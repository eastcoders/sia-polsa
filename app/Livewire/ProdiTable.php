<?php

namespace App\Livewire;

use App\Jobs\SyncProdiJob;
use App\Models\Prodi;
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
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ProdiTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Prodi::query())
            ->heading('Prodi')
            ->description('Data Prodi')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('id_prodi')
                    ->label('ID Prodi'),
                TextColumn::make('nama_program_studi')
                    ->label('Nama Prodi'),
                TextColumn::make('kode_program_studi')
                    ->label('Kode Prodi'),
                TextColumn::make('nama_jenjang_pendidikan')
                    ->label('Jenjang Pendidikan'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Prodi')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncProdiJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Prodi sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.prodi-table');
    }
}
