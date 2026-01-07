<?php

namespace App\Filament\Dosen\Resources\ApprovalSurats\Pages;

use App\Filament\Dosen\Resources\ApprovalSurats\ApprovalSuratResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditApprovalSurat extends EditRecord
{
    protected static string $resource = ApprovalSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
