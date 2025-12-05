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
            DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['id_provinsi']);
        unset($data['id_kabupaten']);

        return $data;
    }
}
