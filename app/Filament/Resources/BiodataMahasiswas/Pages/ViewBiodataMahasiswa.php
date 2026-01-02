<?php

namespace App\Filament\Resources\BiodataMahasiswas\Pages;

use App\Filament\Resources\BiodataMahasiswas\BiodataMahasiswaResource;
use App\Models\Wilayah;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBiodataMahasiswa extends ViewRecord
{
    protected static string $resource = BiodataMahasiswaResource::class;

    public static function getNavigationLabel(): string
    {
        return 'Detail Mahasiswa';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
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
}
