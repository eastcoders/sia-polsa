<?php

namespace App\Livewire;

use App\Jobs\SyncWilayahJob;
use App\Models\Wilayah;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class WilayahTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Wilayah::query())
            ->heading('Wilayah')
            ->description('Data Wilayah')
            ->columns([
                TextColumn::make('id')
                    ->rowIndex()
                    ->label('#'),
                TextColumn::make('nama_wilayah')
                    ->label('Nama Wilayah'),
                TextColumn::make('id_wilayah')
                    ->label('ID Wilayah'),
                TextColumn::make('id_level_wilayah')
                    ->label('Level Wilayah'),
            ])
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Wilayah')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncWilayahJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi wilayah sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.wilayah-table');
    }
}
