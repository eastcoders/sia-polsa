<?php

namespace App\Filament\Dosen\Resources\ApprovalSurats;

use BackedEnum;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Models\PengajuanSurat;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Dosen\Resources\ApprovalSurats\Pages;

class ApprovalSuratResource extends Resource
{
    protected static ?string $model = PengajuanSurat::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Validasi Akademik';

    protected static ?string $navigationLabel = 'Approval Surat';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('kaprodi') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Textarea::make('keterangan')
                ->disabled(),
            Forms\Components\Textarea::make('reject_reason')
                ->label('Alasan Penolakan')
                ->visible(fn($record) => $record?->status === 'rejected')
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mahasiswa.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->date()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PengajuanSurat $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                            'nomor_surat' => 'NO/' . date('Y') . '/' . rand(100, 999), // Placeholder generator logic
                        ]);
                        Notification::make()->title('Surat Disetujui')->success()->send();
                    })
                    ->visible(fn($record) => $record->status === 'pending'),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (PengajuanSurat $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'reject_reason' => $data['reason'],
                        ]);
                        Notification::make()->title('Surat Ditolak')->danger()->send();
                    })
                    ->visible(fn($record) => $record->status === 'pending'),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);

        // Scope Logic: Hanya surat dari mahasiswa di Prodi Kaprodi
        $user = auth()->user();
        if ($user && $user->hasRole('kaprodi')) {
            $prodiIds = $user->dosen?->memimpinProdi?->pluck('id_prodi')->toArray() ?? [];
            if (!empty($prodiIds)) {
                // Gunakan scopeForKaprodi yang sudah dibuat di Model
                $query->forKaprodi($prodiIds);
            }
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovalSurats::route('/'),
        ];
    }
}
