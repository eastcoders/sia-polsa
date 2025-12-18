<?php

namespace App\Filament\Resources\BiodataMahasiswas\Pages;

use App\Filament\Resources\BiodataMahasiswas\BiodataMahasiswaResource;
use BackedEnum;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Support\Icons\Heroicon;
use App\Models\PesertaKelasKuliah;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class KrsMahasiswa extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = BiodataMahasiswaResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected string $view = 'filament.resources.biodata-mahasiswas.pages.krs-mahasiswa';

    public function getTitle(): string
    {
        return 'KRS Mahasiswa';
    }

    public static function getNavigationLabel(): string
    {
        return 'KRS Mahasiswa';
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
    public function table(Table $table): Table
    {
        return $table
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('semester')
                    ->relationship('kelasKuliah.semester', 'nama_semester')
                    ->label('Semester')
                    ->default(fn() => session('active_semester_id') ?? \App\Models\Semester::where('a_periode_aktif', 1)->value('id_semester'))
                    ->preload()
                    ->searchable(),
            ])
            ->query(
                PesertaKelasKuliah::query()
                    ->where('id_registrasi_mahasiswa', $this->record->riwayatPendidikan?->id_registrasi_mahasiswa)
            )
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('kelasKuliah.matkul.kode_mata_kuliah')
                    ->label('Kode MK')
                    ->searchable(),
                TextColumn::make('kelasKuliah.matkul.nama_mata_kuliah')
                    ->label('Nama Mata Kuliah')
                    ->searchable(),
                TextColumn::make('kelasKuliah.nama_kelas_kuliah')
                    ->label('Nama Kelas')
                    ->searchable(),
                TextColumn::make('kelasKuliah.sks_mk')
                    ->label('SKS'),
                TextColumn::make('kelasKuliah.semester.nama_semester')
                    ->label('Semester'),
            ])

            ->headerActions([
                $this->tambahKelasAction(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function tambahKelasAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('tambah_kelas')
            ->label('Tambah Kelas')
            ->modalHeading('Pilih Kelas Kuliah')
            ->modalWidth('7xl')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->closeModalByClickingAway(false)
            ->schema([
                \Filament\Schemas\Components\Livewire::make(\App\Livewire\Perkuliahan\ListKelasKuliah::class, [
                    'id_registrasi_mahasiswa' => $this->record->riwayatPendidikan?->id_registrasi_mahasiswa,
                ]),
            ]);
    }
}
