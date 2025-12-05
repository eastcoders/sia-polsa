<?php

namespace App\Livewire;

use App\Jobs\SyncAgamaJob;
use App\Models\Agama;
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

class AgamaTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Agama::query())
            ->heading('Agama')
            ->description('Data Agama')
            ->columns([
                TextColumn::make('id')
                    ->rowIndex()
                    ->label('#'),
                TextColumn::make('nama_agama')
                    ->label('Nama Agama'),
                TextColumn::make('id_agama')
                    ->label('ID Agama'),
            ])
            ->paginated(5)
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Agama')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncAgamaJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Agama sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.agama-table');
    }
}
