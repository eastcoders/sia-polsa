<?php

namespace App\Filament\Resources\KelasKuliahs\Pages;

use App\Filament\Resources\KelasKuliahs\KelasKuliahResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

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
