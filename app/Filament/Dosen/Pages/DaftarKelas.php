<?php

namespace App\Filament\Dosen\Pages;

use BackedEnum, UnitEnum;
use App\Models\Dosen;
use Filament\Forms\Get;
use App\Models\Semester;
use Filament\Forms\Form;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Livewire\WithPagination;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class DaftarKelas extends Page implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    // Menentukan view yang akan digunakan oleh halaman ini
    protected string $view = 'filament.dosen.pages.daftar-kelas';

    // Ikon navigasi di sidebar
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|UnitEnum|null $navigationGroup = 'Perkuliahan';


    // Label navigasi
    protected static ?string $navigationLabel = 'Daftar Kelas';

    // Judul halaman
    protected static ?string $title = 'Daftar Kelas Ajar';

    // Filter Semester
    public $semester_id;

    // Method ini dipanggil saat halaman diinisialisasi
    public function mount()
    {
        // Set default semester ke semester aktif
        $this->semester_id = Semester::where('a_periode_aktif', '1')->orderBy('id_semester', 'desc')->value('id_semester');
    }

    // Definisi Form Filter
    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('semester_id')
                    ->label('Filter Semester')
                    ->columnSpanFull()
                    ->options(function () {
                        return Semester::where('a_periode_aktif', '1')
                            ->orderBy('id_semester', 'desc')
                            ->pluck('nama_semester', 'id_semester')
                            ->toArray() ?? [];
                    })
                    ->default(fn() => Semester::where('a_periode_aktif', '1')->orderBy('id_semester', 'desc')->value('id_semester'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function () {
                        $this->resetPage();
                    }),
            ]);
    }

    // Mendapatkan data kelas dengan pagination
    public function getViewData(): array
    {
        $user = Filament::auth()->user();
        $kelasAjar = [];

        if ($user && $user->dosen) {
            $query = $user->dosen->penugasanDosen->dosenPengajarKelasKuliahs()
                ->with([
                    'kelasKuliah.matkul',
                    'kelasKuliah.prodi',
                    'kelasKuliah.semester',
                    'kelasKuliah.jadwalPerkuliahan.ruangKelas'
                ])
                ->withCount('pesertaKelas');

            // Apply Filter Semester if selected
            if ($this->semester_id) {
                // Filter berdasarkan semester dari relasi kelasKuliah
                $query->whereHas('kelasKuliah', function ($q) {
                    $q->where('id_semester', $this->semester_id);
                });
            }

            // Gunakan simplePaginate atau paginate (9 item per halaman utk layout grid 3 kolom)
            $kelasAjar = $query->paginate(9);
        }

        return [
            'kelasAjar' => $kelasAjar,
        ];
    }
}
