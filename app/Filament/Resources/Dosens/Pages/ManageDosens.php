<?php

namespace App\Filament\Resources\Dosens\Pages;

use App\Filament\Resources\Dosens\DosenResource;
use App\Jobs\SyncDosenJob;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Str;

class ManageDosens extends ManageRecords
{
    protected static string $resource = DosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->closeModalByClickingAway(false)
                ->mutateDataUsing(function (array $data): array {
                    $data['id_dosen'] = Str::uuid()->toString();
                    $data['nama_status_aktif'] = 'Aktif';
                    $data['id_status_aktif'] = '1';

                    return $data;
                })
                ->createAnother(false),
            Action::make('sync')
                ->label('Sinkronisasi Dosen')
                ->button()
                ->color('info')
                ->action(function () {
                    // Jalankan job
                    SyncDosenJob::dispatch();

                    Notification::make()
                        ->title('Sinkronisasi Dimulai')
                        ->body('Proses sinkronisasi Dosen sedang berjalan di belakang layar.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
