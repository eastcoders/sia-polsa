<?php

namespace App\Livewire;

use App\Jobs\SyncPenghasilanJob;
use App\Models\Penghasilan;
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

class PenghasilanTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Penghasilan::query())
            ->heading('Penghasilan')
            ->description('Data Penghasilan')
            ->columns([
                TextColumn::make('id')
                    ->rowIndex()
                    ->label('#'),
                TextColumn::make('nama_penghasilan')
                    ->label('Nama Penghasilan'),
                TextColumn::make('id_penghasilan')
                    ->label('ID Penghasilan'),
            ])
            ->paginated(5)
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Penghasilan')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncPenghasilanJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Penghasilan sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.penghasilan-table');
    }
}
