<?php

namespace App\Filament\Resources\BiodataMahasiswas\Pages;

use App\Filament\Resources\BiodataMahasiswas\BiodataMahasiswaResource;
use App\Jobs\DispatchSyncMahasiswa;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListBiodataMahasiswas extends ListRecords
{
    protected static string $resource = BiodataMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Data')
                ->icon(Heroicon::Plus),
            Action::make('sync')
                ->label('Clone Data')
                ->button()
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('info')
                ->requiresConfirmation()
                ->action(function () {
                    DispatchSyncMahasiswa::dispatch();
                    Notification::make()
                        ->title('Sinkronisasi Dimulai')
                        ->body('Proses sinkronisasi Dosen sedang berjalan di belakang layar.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function paginateTableQuery(Builder $query): \Illuminate\Contracts\Pagination\CursorPaginator
    {
        return $query->cursorPaginate($this->getTableRecordsPerPage());
    }
}
