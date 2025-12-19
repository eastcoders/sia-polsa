<?php

namespace App\Livewire;

use App\Livewire\Perkuliahan\NilaiPerkuliahan\ListPesertaKelas;
use App\Models\KelasKuliah;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class NilaiPerkuliahan extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => KelasKuliah::query())
            ->columns([
                TextColumn::make('id')->label('No')->rowIndex(),
                TextColumn::make('semester.nama_semester')->label('Semester'),
                TextColumn::make('matkul.kode_mata_kuliah')->label('Kode MK'),
                TextColumn::make('matkul.nama_mata_kuliah')->label('Nama Mata Kuliah'),
                TextColumn::make('nama_kelas_kuliah')->label('Nama Kelas'),
                TextColumn::make('matkul.sks_mata_kuliah')->label('Bobot MK (sks)'),
                TextColumn::make('pesertaKelas')
                    ->formatStateUsing(fn($record) => $record->pesertaKelas->where('id_kelas_kuliah', $record->id_kelas_kuliah)->count())
                    ->label('Jumlah Peserta'),
                TextColumn::make('sync_status')
                    ->label('Status Sync')
                    ->badge()
                    ->colors([
                        'success' => 'synced',
                        'warning' => ['pending', 'changed'],
                        'danger' => 'failed',
                    ])
                    ->tooltip(fn($record) => $record->sync_message)
                    ->sortable(),
                TextColumn::make('sync_at')
                    ->label('Sync Terakhir')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                EditAction::make('edit')
                    ->closeModalByClickingAway(false)
                    ->modalHeading('Update Nilai Peserta')
                    ->modalWidth('6xl')
                    ->schema([
                        Livewire::make(ListPesertaKelas::class),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.nilai-perkuliahan');
    }
}
