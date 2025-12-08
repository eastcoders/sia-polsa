<?php

namespace App\Filament\Resources\KelasKuliahs\Pages;

use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Resources\KelasKuliahs\KelasKuliahResource;

class AddDosenPengajarPage extends EditRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = KelasKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form'),
            $this->getCancelFormAction(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function tableDosen(Table $table): Table
    {
        return $table;
    }
}
