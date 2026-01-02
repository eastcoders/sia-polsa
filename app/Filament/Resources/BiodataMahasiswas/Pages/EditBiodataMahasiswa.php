<?php

namespace App\Filament\Resources\BiodataMahasiswas\Pages;

use App\Filament\Resources\BiodataMahasiswas\BiodataMahasiswaResource;
use App\Models\Wilayah;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBiodataMahasiswa extends EditRecord
{
    protected static string $resource = BiodataMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form')
                ->label('Simpan'),
            $this->getCancelFormAction()
                ->formId('form')
                ->label('Batal'),
            // DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['id_wilayah'])) {
            $currentWilayah = Wilayah::where('id_wilayah', $data['id_wilayah'])->first();

            if ($currentWilayah) {
                if ($currentWilayah->id_level_wilayah == 3) { 
                    $kecamatan = $currentWilayah;
                    $kabupaten = Wilayah::where('id_wilayah', trim($kecamatan->id_induk_wilayah))->first();
                    $provinsi = Wilayah::where('id_wilayah', trim($kabupaten->id_induk_wilayah))->first();

                    $data['id_kabupaten'] = trim($kabupaten->id_wilayah ?? '');
                    $data['id_provinsi'] = trim($provinsi->id_wilayah ?? '');
                } elseif ($currentWilayah->id_level_wilayah == 2) {
                    $kabupaten = $currentWilayah;
                    $provinsi = Wilayah::where('id_wilayah', trim($kabupaten->id_induk_wilayah))->first();

                    $data['id_kabupaten'] = trim($kabupaten->id_wilayah);
                    $data['id_provinsi'] = trim($provinsi->id_wilayah ?? '');
                } elseif ($currentWilayah->id_level_wilayah == 1) {
                    $data['id_provinsi'] = trim($currentWilayah->id_wilayah);
                    $data['id_kabupaten'] = null;
                }
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['id_provinsi']);
        unset($data['id_kabupaten']);

        $data['sync_status'] = 'changed';

        return $data;
    }
}
