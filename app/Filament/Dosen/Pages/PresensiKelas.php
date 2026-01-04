<?php

namespace App\Filament\Dosen\Pages;

use App\Models\KelasKuliah;
use App\Models\PertemuanKelas;
use App\Models\PesertaKelasKuliah;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Livewire\WithPagination;

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

    public array $tugasData = [];      // State untuk nilai tugas

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

        if (! $this->record_id) {
            abort(404, 'Kelas tidak ditemukan.');
        }

        // Initialize attendance state for all participants
        $this->initializeData();

        // Set default form state
        $this->form->fill();
    }

    /**
     * Fetch all participants and initialize attendance & task state
     */
    protected function initializeData()
    {
        $allPeserta = PesertaKelasKuliah::where('id_kelas_kuliah', $this->record_id)->get();

        foreach ($allPeserta as $mhs) {
            // Use registration ID as array key
            $key = $mhs->id_registrasi_mahasiswa;

            // Set default value if key is missing
            if (! isset($this->attendanceData[$key])) {
                $this->attendanceData[$key] = [
                    'status' => 'hadir', // Default hadir agar memudahkan dosen
                    'keterangan' => null,
                ];
            }

            if (! isset($this->tugasData[$key])) {
                $this->tugasData[$key] = [
                    'nilai' => 0,
                    'feedback' => null,
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
                            ->disabled(fn () => $this->selectedPertemuanId),
                        DatePicker::make('tanggal')
                            ->label('Tanggal Pertemuan')
                            ->default(now())
                            ->required()
                            ->disabled(fn () => $this->selectedPertemuanId),
                        Select::make('metode_pembelajaran')
                            ->options([
                                'luring' => 'Luring (Offline)',
                                'daring' => 'Daring (Online)',
                                'hybrid' => 'Hybrid',
                            ])
                            ->default('luring')
                            ->required()
                            ->disabled(fn () => $this->selectedPertemuanId),
                        Select::make('status_pertemuan')
                            ->options([
                                'terjadwal' => 'Terjadwal',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                            ])
                            ->default('selesai')
                            ->required()
                            ->disabled(fn () => $this->selectedPertemuanId),
                        Textarea::make('materi')
                            ->label('Materi Pembahasan')
                            ->rows(2)
                            ->columnSpanFull()
                            ->disabled(fn () => $this->selectedPertemuanId),
                    ])->columns(2),

                Section::make('Penugasan (Logbook)')
                    ->description('Centang jika ada tugas pada pertemuan ini.')
                    ->schema([
                        Toggle::make('ada_tugas')
                            ->label('Ada Tugas pada pertemuan ini?')
                            ->live()
                            ->default(false),

                        Group::make([
                            TextInput::make('judul_tugas')
                                ->label('Judul Tugas')
                                ->required()
                                ->placeholder('Contoh: Laporan Praktikum Modul 1'),
                            Textarea::make('deskripsi_tugas')
                                ->label('Deskripsi Tugas')
                                ->rows(2),
                        ])
                            ->visible(fn (Get $get) => $get('ada_tugas'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
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
                $formData = $pertemuan->toArray();

                // Check if assignment exists
                $tugas = \App\Models\TugasPertemuan::where('id_pertemuan_kelas', $pertemuan->id)->first();
                if ($tugas) {
                    $formData['ada_tugas'] = true;
                    $formData['judul_tugas'] = $tugas->judul_tugas;
                    $formData['deskripsi_tugas'] = $tugas->deskripsi;
                } else {
                    $formData['ada_tugas'] = false;
                }

                $this->form->fill($formData);

                // Retrieve existing attendance records
                $presensiExisting = $pertemuan->presensiMahasiswas;
                foreach ($presensiExisting as $p) {
                    if (isset($this->attendanceData[$p->id_registrasi_mahasiswa])) {
                        $this->attendanceData[$p->id_registrasi_mahasiswa] = [
                            'status' => $p->status_kehadiran,
                            'keterangan' => $p->keterangan,
                        ];
                    }
                }

                // Retrieve existing grades
                if ($tugas) {
                    $nilaiExisting = \App\Models\NilaiTugas::where('id_tugas_pertemuan', $tugas->id)->get();
                    foreach ($nilaiExisting as $n) {
                        if (isset($this->tugasData[$n->id_registrasi_mahasiswa])) {
                            $this->tugasData[$n->id_registrasi_mahasiswa] = [
                                'nilai' => $n->nilai,
                                'feedback' => $n->feedback,
                            ];
                        }
                    }
                }
            }
        } else {
            $this->form->fill();
            $this->initializeData();
        }
    }

    public function save()
    {
        $dataPertemuan = $this->form->getState();

        if ($this->selectedPertemuanId) {
            $pertemuan = PertemuanKelas::find($this->selectedPertemuanId);
            if (! $pertemuan) {
                \Filament\Notifications\Notification::make()->title('Error')->body('Data tidak ditemukan.')->danger()->send();

                return;
            }
        } else {
            $pertemuan = PertemuanKelas::create([
                'id_kelas_kuliah' => $this->record_id,
                'pertemuan_ke' => $dataPertemuan['pertemuan_ke'],
                'tanggal' => $dataPertemuan['tanggal'],
                'materi' => $dataPertemuan['materi'] ?? '-',
                'metode_pembelajaran' => $dataPertemuan['metode_pembelajaran'],
                'status_pertemuan' => $dataPertemuan['status_pertemuan'],
            ]);
            $this->selectedPertemuanId = $pertemuan->id;
        }

        // 1. Save Attendance
        foreach ($this->attendanceData as $idRegMhs => $data) {
            $pertemuan->presensiMahasiswas()->updateOrCreate(
                ['id_pertemuan_kelas' => $pertemuan->id, 'id_registrasi_mahasiswa' => $idRegMhs],
                ['status_kehadiran' => $data['status'], 'keterangan' => $data['keterangan'] ?? null]
            );
        }

        // 2. Save Assignment (if checked)
        if ($dataPertemuan['ada_tugas'] ?? false) {
            $tugas = \App\Models\TugasPertemuan::updateOrCreate(
                ['id_pertemuan_kelas' => $pertemuan->id],
                [
                    'judul_tugas' => $dataPertemuan['judul_tugas'],
                    'deskripsi' => $dataPertemuan['deskripsi_tugas'] ?? null,
                    'is_active' => true,
                ]
            );

            // Save Grades
            foreach ($this->tugasData as $idRegMhs => $data) {
                \App\Models\NilaiTugas::updateOrCreate(
                    ['id_tugas_pertemuan' => $tugas->id, 'id_registrasi_mahasiswa' => $idRegMhs],
                    ['nilai' => $data['nilai'] ?? 0, 'feedback' => $data['feedback'] ?? null]
                );
            }
        } else {
            // Optional: If unchecked, should we delete existing task?
            // For safety, let's keep it but maybe mark inactive? Or just leave it.
            // Current decision: Do nothing to avoid accidental data loss.
        }

        \Filament\Notifications\Notification::make()->title('Data Berhasil Disimpan')->success()->send();
    }

    public function getViewData(): array
    {
        // Retrieve class data
        $kelas = KelasKuliah::with(['matkul', 'prodi', 'semester'])->where('id_kelas_kuliah', $this->record_id)->first();

        if (! $kelas) {
            abort(404, 'Kelas tidak ditemukan.');
        }

        // Retrieve meeting history
        $historyPertemuan = PertemuanKelas::where('id_kelas_kuliah', $this->record_id)
            ->orderBy('pertemuan_ke', 'desc')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->id => "Pertemuan Ke-{$item->pertemuan_ke} ({$item->tanggal->format('d M Y')})"]);

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
