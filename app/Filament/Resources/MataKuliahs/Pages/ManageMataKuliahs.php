<?php

namespace App\Filament\Resources\MataKuliahs\Pages;

use App\Filament\Resources\MataKuliahs\MataKuliahResource;
use App\Jobs\DispatchSyncMataKuliah;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ManageMataKuliahs extends ManageRecords
{
    protected static string $resource = MataKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Data')
                ->icon(Heroicon::Plus)
                ->closeModalByClickingAway(false)
                ->mutateDataUsing(function (array $data) {
                    $data['id_matkul'] = Str::uuid()->toString();

                    return $data;
                }),
            Action::make('sync_from_feeder')
                ->label('Clone Data')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('info')
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
