<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SkalaNilai;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Jobs\SyncSkalaNilaiJob;
use Illuminate\Contracts\View\View;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class SkalaNilaiTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => SkalaNilai::query())
            ->heading('Skala Nilai')
            ->description('Data Skala Nilai Prodi')
            ->columns([
                TextColumn::make('prodi.nama_program_studi'),
                TextColumn::make('nilai_huruf'),
                TextColumn::make('nilai_indeks'),
                TextColumn::make('bobot_nilai_min'),
                TextColumn::make('bobot_nilai_maks'),
                TextColumn::make('tanggal_mulai_efektif')
                    ->date()
                    ->sortable(),
                TextColumn::make('tanggal_akhir_efektif')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Skala Nilai')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncSkalaNilaiJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Skala Nilai sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.skala-nilai-table');
    }
}
