<?php

namespace App\Livewire\Dosen;

use App\Jobs\SyncPenugasanDosenJob;
use App\Models\PenugasanDosen;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
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
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class RegistrasiDosen extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?Model $record = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => PenugasanDosen::orderBy('id_tahun_ajaran', 'desc')->where('id_dosen', $this->record->id_dosen))
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('id_tahun_ajaran')
                    ->label('Tahun Ajaran'),
                TextColumn::make('prodi.programStudiLengkap'),
                TextColumn::make('nomor_surat_tugas'),
                TextColumn::make('tanggal_surat_tugas'),
                TextColumn::make('mulai_surat_tugas'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('sync_penugasan_dosen')
                    ->label('Sinkronisasi Penugasan Dosen')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        $filter = ['filter' => "id_dosen = '{$this->record->id_dosen}'"];
                        // dd($filter);
                        dispatch(new SyncPenugasanDosenJob($filter));

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Agama sedang berjalan di belakang layar.')
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
        return view('livewire.dosen.registrasi-dosen');
    }
}
