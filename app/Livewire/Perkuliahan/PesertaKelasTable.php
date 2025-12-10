<?php

namespace App\Livewire\Perkuliahan;

use App\Livewire\Perkuliahan\PesertaKelasKuliah\ListMahasiswa;
use App\Models\PesertaKelasKuliah;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class PesertaKelasTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?Model $record = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => PesertaKelasKuliah::query())
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('riwayatPendidikan.nim')
                    ->label('NIM')
                    ->searchable(),
                TextColumn::make('riwayatPendidikan.mahasiswa.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable(),
                TextColumn::make('riwayatPendidikan.mahasiswa.jenis_kelamin')
                    ->label('Jenis Kelamin'),
                TextColumn::make('riwayatPendidikan.prodi.programStudiLengkap')
                    ->label('Program Studi'),
                TextColumn::make('riwayatPendidikan.periodeDaftar.id_tahun_ajaran')
                    ->label('Angkatan'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                $this->inputKolektifPeserta(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.perkuliahan.peserta-kelas-table');
    }

    public function tambahPesertaKelas()
    {
        return Action::make('add_peserta_kelas')
            ->label('Tambah Peserta Kelas');
    }

    public function inputKolektifPeserta()
    {
        return Action::make('input_kolektif_peserta')
            ->label('Input Kolektif')
            ->modalHeading('Tambah Peserta Kelas Secara Kolektif')
            ->closeModalByClickingAway(false)
            ->schema([
                Livewire::make(ListMahasiswa::class, ['id_kelas_kuliah' => $this->record->id_kelas_kuliah]),
            ])
            ->modalSubmitAction(false)
            ->modalCancelAction(false);
    }
}
