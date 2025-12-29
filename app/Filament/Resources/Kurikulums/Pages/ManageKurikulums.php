<?php

namespace App\Filament\Resources\Kurikulums\Pages;

use App\Filament\Resources\Kurikulums\KurikulumResource;
use App\Jobs\DispatchSyncKurikulum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ManageKurikulums extends ManageRecords
{
    protected static string $resource = KurikulumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Data')
                ->icon(Heroicon::Plus)
                ->closeModalByClickingAway(false)
                ->mutateDataUsing(function (array $data) {
                    $data['id_kurikulum'] = Str::uuid()->toString();

                    return $data;
                }),
            Action::make('sync_from_feeder')
                ->label('Clone Data')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('info')
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
