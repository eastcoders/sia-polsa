<?php

namespace App\Filament\Resources\Dosens\Pages;

use App\Filament\Resources\Dosens\DosenResource;
use App\Jobs\SyncDosenJob;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListDosens extends ListRecords
{
    protected static string $resource = DosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('sync')
                ->label('Sinkronisasi Dosen')
                ->button()
                ->color('primary')
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
