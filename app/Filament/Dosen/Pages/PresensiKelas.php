<?php

namespace App\Filament\Dosen\Pages;

use BackedEnum;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\KelasKuliah;
use Filament\Schemas\Schema;
use Livewire\WithPagination;
use App\Models\PertemuanKelas;
use App\Models\PesertaKelasKuliah;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use App\Filament\Dosen\Pages\DaftarKelas;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;

class PresensiKelas extends Page implements HasForms
{
    use InteractsWithForms, WithPagination;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected string $view = 'filament.dosen.pages.presensi-kelas';

    protected static bool $shouldRegisterNavigation = false; // Sembunyikan dari sidebar

    protected static ?string $title = 'Presensi Mahasiswa';

    public $record_id;
    public ?array $pertemuanData = []; // Data form pertemuan
    public array $attendanceData = []; // State untuk data presensi semua mahasiswa
    public ?string $selectedPertemuanId = null; // ID Pertemuan yang sedang dipilih

    /**
     * Determine which navigation item should be active when this page is viewed.
     */
    public function getActiveNavigationItem(): ?string
    {
        return DaftarKelas::getNavigationLabel();
    }

    public function mount()
    {
        // Ambil ID kelas dari query parameter ?record=...
        $this->record_id = request()->query('record');

        if (!$this->record_id) {
            abort(404, 'Kelas tidak ditemukan.');
        }

        // Initialize attendance state for all participants
        $this->initializeAttendanceData();

        // Set default form state
        $this->form->fill();
    }

    /**
     * Fetch all participants and initialize attendance state
     */
    protected function initializeAttendanceData()
    {
        $allPeserta = PesertaKelasKuliah::where('id_kelas_kuliah', $this->record_id)->get();

        foreach ($allPeserta as $mhs) {
            // Use registration ID as array key
            $key = $mhs->id_registrasi_mahasiswa;

            // Set default value if key is missing
            if (!isset($this->attendanceData[$key])) {
                $this->attendanceData[$key] = [
                    'status' => 'hadir', // Default hadir agar memudahkan dosen
                    'keterangan' => null,
                ];
            }
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Pertemuan')
                    ->description('Masukkan data pertemuan kelas hari ini.')
                    ->schema([
                        TextInput::make('pertemuan_ke')
                            ->label('Pertemuan Ke-')
                            ->numeric()
                            ->required()
                            ->disabled(fn() => $this->selectedPertemuanId !== null),
                        DatePicker::make('tanggal')
                            ->label('Tanggal Pertemuan')
                            ->default(now())
                            ->required()
                            ->disabled(fn() => $this->selectedPertemuanId !== null),
                        Select::make('metode_pembelajaran')
                            ->options([
                                'luring' => 'Luring (Offline)',
                                'daring' => 'Daring (Online)',
                                'hybrid' => 'Hybrid',
                            ])
                            ->default('luring')
                            ->required()
                            ->disabled(fn() => $this->selectedPertemuanId !== null),
                        Select::make('status_pertemuan')
                            ->options([
                                'terjadwal' => 'Terjadwal',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                            ])
                            ->default('selesai')
                            ->required()
                            ->disabled(fn() => $this->selectedPertemuanId !== null),
                        Textarea::make('materi')
                            ->label('Materi Pembahasan')
                            ->rows(2)
                            ->columnSpanFull()
                            ->disabled(fn() => $this->selectedPertemuanId !== null),
                    ])->columns(2),
            ])
            ->statePath('pertemuanData');
    }

    /**
     * Handle meeting selection change
     */
    public function loadPertemuan()
    {
        if ($this->selectedPertemuanId) {
            // Find meeting record
            $pertemuan = PertemuanKelas::find($this->selectedPertemuanId);

            if ($pertemuan) {
                // Populate form with meeting data
                $this->form->fill($pertemuan->toArray());

                // Retrieve existing attendance records
                $presensiExisting = $pertemuan->presensiMahasiswas;

                // Update attendance state
                foreach ($presensiExisting as $p) {
                    if (isset($this->attendanceData[$p->id_registrasi_mahasiswa])) {
                        $this->attendanceData[$p->id_registrasi_mahasiswa] = [
                            'status' => $p->status_kehadiran,
                            'keterangan' => $p->keterangan,
                        ];
                    }
                }
            }
        } else {
            // Reset form and state for new entry
            $this->form->fill();
            $this->initializeAttendanceData(); // Initialize default attendance values
        }
    }

    public function save()
    {
        // Retrieve form state
        // Handle state retrieval where disabled fields are omitted
        $dataPertemuan = $this->form->getState();

        if ($this->selectedPertemuanId) {
            // Retrieve existing meeting record
            // Existing header fields are read-only
            $pertemuan = PertemuanKelas::find($this->selectedPertemuanId);

            if (!$pertemuan) {
                \Filament\Notifications\Notification::make()
                    ->title('Error')
                    ->body('Data pertemuan tidak ditemukan.')
                    ->danger()
                    ->send();
                return;
            }
        } else {
            // Create new meeting record
            // Header fields are present in data
            $pertemuan = PertemuanKelas::create([
                'id_kelas_kuliah' => $this->record_id,
                'pertemuan_ke' => $dataPertemuan['pertemuan_ke'],
                'tanggal' => $dataPertemuan['tanggal'],
                'materi' => $dataPertemuan['materi'],
                'metode_pembelajaran' => $dataPertemuan['metode_pembelajaran'],
                'status_pertemuan' => $dataPertemuan['status_pertemuan'],
            ]);

            // Update selected ID
            $this->selectedPertemuanId = $pertemuan->id;
        }

        // Save attendance details
        // Iterate over attendance data
        foreach ($this->attendanceData as $idRegMhs => $data) {
            // Update or create student attendance record
            $pertemuan->presensiMahasiswas()->updateOrCreate(
                [
                    'id_pertemuan_kelas' => $pertemuan->id,
                    'id_registrasi_mahasiswa' => $idRegMhs,
                ],
                [
                    'status_kehadiran' => $data['status'],
                    'keterangan' => $data['keterangan'] ?? null,
                ]
            );
        }

        // Send success notification
        \Filament\Notifications\Notification::make()
            ->title('Presensi Berhasil Disimpan')
            ->success()
            ->send();
    }

    public function getViewData(): array
    {
        // Retrieve class data
        $kelas = KelasKuliah::with(['matkul', 'prodi', 'semester'])->where('id_kelas_kuliah', $this->record_id)->first();

        if (!$kelas) {
            abort(404, 'Kelas tidak ditemukan.');
        }

        // Retrieve meeting history
        $historyPertemuan = PertemuanKelas::where('id_kelas_kuliah', $this->record_id)
            ->orderBy('pertemuan_ke', 'desc')
            ->get()
            ->mapWithKeys(fn($item) => [$item->id => "Pertemuan Ke-{$item->pertemuan_ke} ({$item->tanggal->format('d M Y')})"]);

        // Retrieve paginated participants
        $peserta = PesertaKelasKuliah::with('riwayatPendidikan.mahasiswa')
            ->where('id_kelas_kuliah', $this->record_id)
            ->paginate(20);

        return [
            'kelas' => $kelas,
            'peserta' => $peserta,
            'historyPertemuan' => $historyPertemuan,
        ];
    }
}
