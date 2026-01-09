<?php

namespace App\Filament\Mahasiswa\Resources\KartuUjians;

use BackedEnum;
use Filament\Tables\Table;
use App\Models\JadwalUjian;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Mahasiswa\Resources\KartuUjians\Pages\ManageKartuUjians;

class KartuUjianResource extends Resource
{
    protected static ?string $model = JadwalUjian::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Kartu Ujian';
    protected static ?string $slug = 'kartu-ujian';

    // Disable creation/edit/delete for students
    public static function canCreate(): bool
    {
        return false;
    }
    public static function canEdit($record): bool
    {
        return false;
    }
    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || !$user->mahasiswa) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->whereHas('kelasKuliah', function (Builder $query) use ($user) {
                $query->whereHas('pesertaKelas', function (Builder $q) use ($user) {
                    $q->whereHas('riwayatPendidikan', function (Builder $rq) use ($user) {
                        $rq->where('id_mahasiswa', $user->mahasiswa->id_mahasiswa);
                    });
                });
            })
            ->published();
    }

    public static function checkGatekeeper(): int
    {
        return \App\Models\SurveyTicket::where('user_id', auth()->id())
            ->where('status', 'PENDING')
            ->count();
    }

    public static function table(Table $table): Table
    {
        $pendingSurveys = self::checkGatekeeper();

        return $table
            ->columns([
                TextColumn::make('kelasKuliah.matkul.kode_mata_kuliah')
                    ->label('Kode')
                    ->sortable(),
                TextColumn::make('kelasKuliah.matkul.nama_resmi')
                    ->label('Mata Kuliah')
                    ->description(fn(JadwalUjian $record) => $record->kelasKuliah->nama_kelas)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jenis_ujian')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'UTS' => 'info',
                        'UAS' => 'warning',
                    }),
                TextColumn::make('mode_ujian')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ONSITE' => 'success',
                        'TAKE_HOME' => 'danger',
                    }),
                TextColumn::make('jadwal_detail')
                    ->label('Jadwal / Deadline')
                    ->state(function (JadwalUjian $record) {
                        if ($record->mode_ujian === 'ONSITE') {
                            return $record->tanggal_ujian?->format('d M Y') . "\n" .
                                ($record->jam_mulai?->format('H:i') . ' - ' . $record->jam_selesai?->format('H:i'));
                        }
                        return $record->deadline_submission?->format('d M Y H:i');
                    })
                    ->description(
                        fn(JadwalUjian $record) => $record->mode_ujian === 'ONSITE'
                        ? 'Ruang: ' . ($record->ruangKelas->nama_ruang ?? '-')
                        : 'Link ada di detail'
                    ),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('cetak')
                    ->label('Cetak Kartu')
                    ->icon('heroicon-o-printer')
                    ->button()
                    ->outlined()
                    ->url('#') // Placeholder route
                    ->openUrlInNewTab()
                    ->visible($pendingSurveys === 0)
                    ->tooltip('Anda dapat mencetak kartu ujian setelah semua kuesioner terisi.'),

                \Filament\Tables\Actions\Action::make('blocked')
                    ->label('Kuesioner Belum Lengkap')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->button()
                    ->url('/mahasiswa/isi-kuesioner')
                    ->visible($pendingSurveys > 0),
            ])
            ->emptyStateHeading('Belum ada jadwal ujian')
            ->emptyStateDescription('Jika ujian sudah dekat, hubungi bagian akademik.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageKartuUjians::route('/'),
        ];
    }
}
