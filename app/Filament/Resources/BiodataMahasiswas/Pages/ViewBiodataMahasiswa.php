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
            $kecamatan = Wilayah::where('id_wilayah', $data['id_wilayah'])->value('id_induk_wilayah');
            $kabupaten = Wilayah::where('id_wilayah', $kecamatan)->first();
            $provinsi = Wilayah::where('id_wilayah', $kabupaten->id_induk_wilayah)->first();

            $data['id_kabupaten'] = $kabupaten->id_wilayah;
            $data['id_provinsi'] = $provinsi->id_wilayah;
        }

        return $data;
    }
}
