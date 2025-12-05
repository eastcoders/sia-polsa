<?php

namespace App\Livewire;

use App\Jobs\SyncJenisTinggalJob;
use App\Models\JenisTinggal;
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

class JenisTinggalTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(JenisTinggal::query())
            ->heading('Jenis Tinggal')
            ->description('Data Jenis Tinggal')
            ->columns([
                TextColumn::make('id')
                    ->rowIndex()
                    ->label('#'),
                TextColumn::make('nama_jenis_tinggal')
                    ->label('Nama Jenis Tinggal'),
                TextColumn::make('id_jenis_tinggal')
                    ->label('ID Jenis Tinggal'),
            ])
            ->paginated(5)
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Jenis Tinggal')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncJenisTinggalJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Jenis Tinggal sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.jenis-tinggal-table');
    }
}
