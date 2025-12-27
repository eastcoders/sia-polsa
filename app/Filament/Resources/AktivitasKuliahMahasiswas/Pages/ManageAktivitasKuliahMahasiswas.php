<?php

namespace App\Filament\Resources\AktivitasKuliahMahasiswas\Pages;

use App\Filament\Resources\AktivitasKuliahMahasiswas\AktivitasKuliahMahasiswaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAktivitasKuliahMahasiswas extends ManageRecords
{
    protected static string $resource = AktivitasKuliahMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            \Filament\Actions\Action::make('sync')
                ->label('Sync Data')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(function () {
                    \App\Jobs\DispatchSyncAktivitasKuliahMahasiswa::dispatch();
                    \Filament\Notifications\Notification::make()
                        ->title('Sync Background Job Dispatched')
                        ->success()
                        ->send();
                }),
        ];
    }
}
