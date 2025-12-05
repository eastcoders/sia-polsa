<?php

namespace App\Livewire;

use App\Jobs\SyncAlatTransportasiJob;
use App\Models\AlatTransportasi;
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

class AlatTransportasiTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(AlatTransportasi::query())
            ->heading('Alat Transportasi')
            ->description('Data Alat Transportasi')
            ->columns([
                TextColumn::make('id')
                    ->rowIndex()
                    ->label('#'),
                TextColumn::make('nama_alat_transportasi')
                    ->label('Nama Alat Transportasi'),
                TextColumn::make('id_alat_transportasi')
                    ->label('ID Alat Transportasi'),
            ])
            ->paginated(5)
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Alat Transportasi')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncAlatTransportasiJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Alat Transportasi sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.alat-transportasi-table');
    }
}
