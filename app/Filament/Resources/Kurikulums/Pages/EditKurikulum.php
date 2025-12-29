<?php

namespace App\Filament\Resources\Kurikulums\Pages;

use App\Filament\Resources\Kurikulums\KurikulumResource;
use App\Models\MataKuliah;
use App\Models\MatkulKurikulum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EditKurikulum extends EditRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = KurikulumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form'),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['sync_status'] = 'changed';

        return $data;
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => MatkulKurikulum::with('matkul')->where('id_kurikulum', $this->record->id_kurikulum))
            ->heading('Data Matakuliah Kurikulum')
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('matkul.nama_mata_kuliah')
                    ->label('Mata Kuliah'),
                TextColumn::make('matkul.kode_mata_kuliah')
                    ->label('Kode Mata Kuliah'),
                TextColumn::make('semester')
                    ->label('Semester'),
                TextColumn::make('apakah_wajib')
                    ->label('Wajib?')
                    ->formatStateUsing(fn ($state) => $state ? 'Wajib' : 'Pilihan'),
            ])
            ->headerActions([
                Action::make('add_matkul')
                    ->label('Input Mata Kuliah')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                Select::make('id_matkul')
                                    ->label('Mata Kuliah')
                                    ->options(function () {
                                        $existingMatkulIds = MatkulKurikulum::where('id_kurikulum', $this->record->id_kurikulum)
                                            ->pluck('id_matkul')
                                            ->toArray();

                                        return MataKuliah::whereNotIn('id_matkul', $existingMatkulIds)
                                            ->selectRaw("CONCAT(kode_mata_kuliah, ' - ', nama_mata_kuliah, ' - ', sks_mata_kuliah, ' SKS') as label, id_matkul")
                                            ->pluck('label', 'id_matkul');
                                    })
                                    ->searchable()
                                    ->multiple()
                                    ->required(),
                                Select::make('semester')
                                    ->label('Semester')
                                    ->options([
                                        '1' => 'Semester 1',
                                        '2' => 'Semester 2',
                                        '3' => 'Semester 3',
                                        '4' => 'Semester 4',
                                        '5' => 'Semester 5',
                                        '6' => 'Semester 6',
                                        '7' => 'Semester 7',
                                        '8' => 'Semester 8',
                                    ])
                                    ->native(false)
                                    ->required(),
                                Checkbox::make('apakah_wajib')
                                    ->label('Wajib?')
                                    ->mutateDehydratedStateUsing(fn ($state) => $state ? 1 : 0),
                            ]),

                    ])
                    ->modalHeading('Tambah Matakuliah')
                    ->modalSubmitActionLabel('Simpan')
                    ->action(function (array $data) {
                        $idKurikulum = $this->record->id_kurikulum;
                        $jumlahSksLulus = $this->record->jumlah_sks_lulus;

                        $selectedMataKuliah = MataKuliah::whereIn('id_matkul', $data['id_matkul'])->get();

                        $totalSks = $selectedMataKuliah->sum('sks_mata_kuliah');

                        if ($totalSks > $jumlahSksLulus) {
                            Notification::make()
                                ->title('Gagal')
                                ->danger()
                                ->body("Total SKS yang dipilih ({$totalSks}) melebihi jumlah SKS lulus ({$jumlahSksLulus}).")
                                ->send();

                            return;
                        }

                        // Simpan data baru
                        foreach ($selectedMataKuliah as $mataKuliah) {
                            MatkulKurikulum::create([
                                'id_kurikulum' => $idKurikulum,
                                'id_matkul' => $mataKuliah->id_matkul,
                                'semester' => $data['semester'],
                                'apakah_wajib' => (string) $data['apakah_wajib'],
                            ]);
                        }

                        Notification::make()
                            ->title('Berhasil')
                            ->success()
                            ->body("{$selectedMataKuliah->count()} mata kuliah berhasil ditambahkan ke kurikulum.")
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('delete')
                    ->requiresConfirmation()
                    ->iconButton()
                    ->color('danger')
                    ->icon('heroicon-m-trash')
                    ->action(function (MatkulKurikulum $record) {
                        $record->kurikulum->update([
                            'sync_status' => 'changed',
                        ]);

                        $record->update([
                            'sync_status' => 'changed',
                        ]);

                        $record->delete();
                        Notification::make()
                            ->title('Berhasil Menghapus Mata Kuliah dari Kurikulum')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->label('Hapus yang Dipilih')
                    ->action(function (Collection $records) {
                        $count = $records->count();
                        $records->each->delete(); // Hapus satu per satu

                        Notification::make()
                            ->title("Berhasil menghapus {$count} mata kuliah")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->color('danger')
                    ->modalHeading('Hapus Mata Kuliah')
                    ->modalDescription('Apakah Anda yakin ingin menghapus mata kuliah yang dipilih?')
                    ->modalSubmitActionLabel('Hapus'),
            ]);
    }
}
