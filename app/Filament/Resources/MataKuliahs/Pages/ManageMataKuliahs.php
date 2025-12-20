<?php

namespace App\Filament\Resources\MataKuliahs\Pages;

use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use App\Jobs\DispatchSyncMataKuliah;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\MataKuliahs\MataKuliahResource;

class ManageMataKuliahs extends ManageRecords
{
    protected static string $resource = MataKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->closeModalByClickingAway(false)
                ->mutateDataUsing(function (array $data) {
                    $data['id_matkul'] = Str::uuid()->toString();

                    return $data;
                }),
            Action::make('sync_from_feeder')
                ->label('Sync Mata Kuliah')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    DispatchSyncMataKuliah::dispatch();
                    \Filament\Notifications\Notification::make()
                        ->title('Sync dijadwalkan')
                        ->success()
                        ->send();
                }),
        ];
    }
}
