<?php

namespace App\Livewire;

use App\Jobs\SyncJenisPendaftaranJob;
use App\Models\JenisPendaftaran;
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

class JenisDaftarTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => JenisPendaftaran::query())
            ->heading('Jenis Pendaftaran')
            ->description('Data Jenis Pendaftaran')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('id_jenis_daftar'),
                TextColumn::make('nama_jenis_daftar'),
                TextColumn::make('sync_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Jenis Pendaftaran')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncJenisPendaftaranJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Jenis Pendaftaran sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.jenis-daftar-table');
    }
}
