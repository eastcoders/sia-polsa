<?php

namespace App\Livewire;

use App\Jobs\SyncJenjangPendidikanJob;
use App\Models\JenjangPendidikan;
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

class JenjangPendidikanTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(JenjangPendidikan::query())
            ->heading('Jenjang Pendidikan')
            ->description('Data jenjang pendidikan')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('id_jenjang_didik')
                    ->label('ID'),
                TextColumn::make('nama_jenjang_didik')
                    ->label('Nama Jenjang Pendidikan'),
            ])
            ->paginated(5)
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Jenjang Pendidikan')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncJenjangPendidikanJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Jenjang Pendidikan sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.jenjang-pendidikan-table');
    }
}
