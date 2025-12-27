<?php

namespace App\Filament\Pages;

use App\Jobs\DispatchSyncAllBiodata;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use UnitEnum;

class MasterBiodata extends Page implements HasActions, HasSchemas
{
    use InteractsWithActions, InteractsWithSchemas;

    protected string $view = 'filament.pages.master-biodata';

    protected static ?string $title = 'Data Master Biodata';

    protected static string|UnitEnum|null $navigationGroup = 'Master Record';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Master Biodata';
    }

    public function getActions(): array
    {
        return [
            Action::make('sync_semua')
                ->label('Sync Semua Data')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Sinkronisasi')
                ->modalDescription('Apakah Anda yakin ingin menjalankan sinkronisasi semua data master biodata? Proses ini akan berjalan di background.')
                ->action(function () {
                    DispatchSyncAllBiodata::dispatch();

                    Notification::make()
                        ->title('Sinkronisasi Dimulai')
                        ->body('Proses sinkronisasi semua data master biodata sedang berjalan di belakang layar.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
