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
            ->query(fn () => PesertaKelasKuliah::where('id_kelas_kuliah', $this->record->id_kelas_kuliah))
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
                \Filament\Actions\Action::make('push_all_peserta')
                    ->label('Push Semua ke Server')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Push Semua Peserta Kelas ke Server')
                    ->modalDescription('Apakah kamu yakin ingin mengirim semua data peserta kelas ini ke server?')
                    ->action(function () {
                        $pesertaList = PesertaKelasKuliah::where('id_kelas_kuliah', $this->record->id_kelas_kuliah)->get();
                        foreach ($pesertaList as $peserta) {
                            \App\Jobs\PushPesertaKelasJob::dispatch($peserta);
                        }
                        \Filament\Notifications\Notification::make()
                            ->title('Push dijadwalkan')
                            ->body("Sebanyak {$pesertaList->count()} peserta kelas akan dikirim ke server.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                DeleteAction::make(),
                \Filament\Actions\Action::make('push_peserta_to_server')
                    ->label('Push')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Push Peserta Kelas ke Server')
                    ->modalDescription('Apakah kamu yakin ingin mengirim data peserta kelas ini ke server?')
                    ->action(function (PesertaKelasKuliah $record) {
                        \App\Jobs\PushPesertaKelasJob::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Push dijadwalkan')
                            ->body('Data peserta kelas akan dikirim ke server.')
                            ->success()
                            ->send();
                    }),
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
