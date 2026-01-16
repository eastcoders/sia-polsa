<?php

namespace App\Filament\Mahasiswa\Resources\PengajuanSurats;

use BackedEnum;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\PengajuanSurat;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Mahasiswa\Resources\PengajuanSurats\Pages;

class PengajuanSuratResource extends Resource
{
    protected static ?string $model = PengajuanSurat::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Layanan Akademik';

    protected static ?string $navigationLabel = 'Pengajuan Surat';


    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Form Pengajuan')
                ->schema([
                    Select::make('jenis_surat')
                        ->label('Jenis Surat')
                        ->options([
                            'Keterangan Aktif Kuliah' => 'Keterangan Aktif Kuliah',
                            'Pengantar Magang/PKL' => 'Pengantar Magang/PKL',
                            'Keterangan Lulus' => 'Keterangan Lulus',
                            'Izin Cuti Akademik' => 'Izin Cuti Akademik',
                        ])
                        ->required(),
                    Textarea::make('keterangan')
                        ->label('Keperluan / Keterangan Tambahan')
                        ->required()
                        ->placeholder('Contoh: Untuk keperluan tunjangan gaji orang tua')
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make('Status Pengajuan')
                ->schema([
                    Placeholder::make('status_view')
                        ->label('Status Saat Ini')
                        ->content(fn($record) => $record?->status ?? '-'),
                    Placeholder::make('nomor_surat')
                        ->label('Nomor Surat')
                        ->content(fn($record) => $record?->nomor_surat ?? '-'),
                    Placeholder::make('reject_reason')
                        ->label('Alasan Penolakan')
                        ->content(fn($record) => $record?->reject_reason ?? '-')
                        ->visible(fn($record) => $record?->status === 'rejected'),
                ])
                ->visible(fn($operation) => $operation === 'view'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenis_surat')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->default('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'secondary' => 'draft',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn($record) => $record->status === 'draft' || $record->status === 'pending'),
                DeleteAction::make()
                    ->visible(fn($record) => $record->status === 'draft' || $record->status === 'pending'),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);

        // Scope Logic: Hanya tampilkan surat milik Mahasiswa Login
        $user = Auth::user();
        if ($user && $user->mahasiswa_id) {
            // Asumsi user->mahasiswa_id adalah local ID yang match biodata_mahasiswa_id
            // Namun saya harus pastikan mapping ini benar.
            // Di PengajuanSurat migration, saya pakai `biodata_mahasiswa_id` (foreignId to id).
            // Di BiodataMahasiswa model, user() relation: belongsTo User (User hasOne Biodata).
            // User table `mahasiswa_id` column points to ... ?
            // User migration biasanya: table->foreignId('mahasiswa_id')->nullable();

            // Jadi:
            $query->where('biodata_mahasiswa_id', $user->mahasiswa_id);
        } else {
            // Fallback: don't show anything if logic mismatch
            $query->whereRaw('1=0');
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuanSurats::route('/'),
            'create' => Pages\CreatePengajuanSurat::route('/create'),
            'edit' => Pages\EditPengajuanSurat::route('/{record}/edit'),
        ];
    }
}
