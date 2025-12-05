<?php

namespace App\Filament\Resources\Kurikulums\Pages;

use App\Filament\Resources\Kurikulums\KurikulumResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Str;

class ManageKurikulums extends ManageRecords
{
    protected static string $resource = KurikulumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->closeModalByClickingAway(false)
                ->mutateDataUsing(function (array $data) {
                    $data['id_kurikulum'] = Str::uuid()->toString();

                    return $data;
                }),
        ];
    }
}
