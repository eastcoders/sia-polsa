<?php

namespace App\Filament\Resources\Kurikulums\Pages;

use App\Jobs\DispatchSyncKurikulum;
use App\Jobs\SyncKurikulumJob;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Kurikulums\KurikulumResource;

class ManageKurikulums extends ManageRecords
{
    protected static string $resource = KurikulumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->closeModalByClickingAway(false)
                ->mutateDataUsing(function (array $data) {
                    $data['id_kurikulum'] = Str::uuid()->toString();

                    return $data;
                }),
            Action::make('sync_from_feeder')
                ->label('Sync Kurikulum')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    DispatchSyncKurikulum::dispatch();
                    \Filament\Notifications\Notification::make()
                        ->title('Sync dijadwalkan')
                        ->success()
                        ->send();
                }),
        ];
    }
}
