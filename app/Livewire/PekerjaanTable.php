<?php

namespace App\Livewire;

use App\Jobs\SyncPekerjaanJob;
use App\Models\Pekerjaan;
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
use Livewire\Component;

class PekerjaanTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Pekerjaan::query())
            ->heading('Pekerjaan')
            ->description('Data Pekerjaan')
            ->columns([
                TextColumn::make('id')
                    ->rowIndex()
                    ->label('#'),
                TextColumn::make('nama_pekerjaan')
                    ->label('Nama Pekerjaan'),
                TextColumn::make('id_pekerjaan')
                    ->label('ID Pekerjaan'),
            ])
            ->paginated(5)
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Pekerjaan')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncPekerjaanJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Pekerjaan sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.pekerjaan-table');
    }
}
