<?php

namespace App\Filament\Resources\Dosens\Pages;

use App\Jobs\SyncDosenJob;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use App\Jobs\DispatchSyncDosen;
use Filament\Actions\CreateAction;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Dosens\DosenResource;

class ManageDosens extends ManageRecords
{
    protected static string $resource = DosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Data')
                ->icon(Heroicon::Plus)
                ->closeModalByClickingAway(false)
                ->modalSubmitActionLabel('Simpan Data')
                ->mutateDataUsing(function (array $data): array {
                    $data['id_dosen'] = Str::uuid()->toString();
                    $data['nama_status_aktif'] = 'Aktif';
                    $data['id_status_aktif'] = '1';

                    return $data;
                })
                ->createAnother(false),
            Action::make('sync')
                ->label('Clone Data')
                ->button()
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('info')
                ->requiresConfirmation()
                ->action(function () {
                    DispatchSyncDosen::dispatch();
                    Notification::make()
                        ->title('Sinkronisasi Dimulai')
                        ->body('Proses sinkronisasi Dosen sedang berjalan di belakang layar.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
