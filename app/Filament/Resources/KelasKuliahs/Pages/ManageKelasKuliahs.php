<?php

namespace App\Filament\Resources\KelasKuliahs\Pages;

use App\Filament\Resources\KelasKuliahs\KelasKuliahResource;
use App\Jobs\DispatchSyncKelasKuliah;
use App\Models\MataKuliah;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Str;

class ManageKelasKuliahs extends ManageRecords
{
    protected static string $resource = KelasKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {

                    $data['id_kelas_kuliah'] = Str::uuid()->toString();

                    $idMatkul = MataKuliah::where('id_matkul', $data['id_matkul'])->first();

                    $data['sks_mk'] = $idMatkul->sks_mata_kuliah;
                    $data['sks_tm'] = $idMatkul->sks_tatap_muka;
                    $data['sks_prak'] = $idMatkul->sks_praktek;
                    $data['sks_sim'] = $idMatkul->sks_praktek_lapangan;

                    return $data;
                }),
            Action::make('sync_from_feeder')
                ->label('Clone Data')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('info')
                ->requiresConfirmation()
                ->action(function () {
                    DispatchSyncKelasKuliah::dispatch();
                    \Filament\Notifications\Notification::make()
                        ->title('Sync dijadwalkan')
                        ->success()
                        ->send();
                }),
        ];
    }
}
