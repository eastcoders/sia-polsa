<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Table;
use App\Models\KelasKuliah;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Illuminate\Contracts\View\View;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Livewire;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use App\Livewire\Perkuliahan\NilaiPerkuliahan\ListPesertaKelas;

class NilaiPerkuliahan extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

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
                    ->formatStateUsing(fn($record) => $record->pesertaKelas->count())
                    ->label('Jumlah Peserta')

                /**
                 * 
                 * 1. Klik Edit untuk mengubah nilai peserta
                 * -> tampil daftar mahasiswa berdasarkan id kelas kuliah
                 * -> tambahkan form untuk mengisi nilai (dropdown)
                 * -> simpan ke database.
                 * 
                 */

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
                        Livewire::make(ListPesertaKelas::class)
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
