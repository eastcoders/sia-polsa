<?php

namespace App\Filament\Dosen\Pages;

use App\Models\KelasKuliah;
use App\Models\KomponenBobotKelas;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class AturBobotNilai extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.dosen.pages.atur-bobot-nilai';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Atur Bobot Penilaian';

    public $record_id;

    public ?array $bobotData = [];

    public function mount()
    {
        $this->record_id = request()->query('record');

        // dd(
        //     $kelas = KelasKuliah::with('semester')->where('id_kelas_kuliah', $this->record_id)->first()
        // );

        // dd($this->record_id);

        if (! $this->record_id) {
            abort(404, 'Kelas tidak ditemukan.');
        }

        // Initialize or Load Data
        $this->loadData();
    }

    protected function loadData()
    {
        // Check if data exists in DB
        $existing = KomponenBobotKelas::where('id_kelas_kuliah', $this->record_id)
            ->pluck('bobot', 'nama_komponen')
            ->toArray();

        // Default Presets as requested by User
        $defaults = [
            'Tugas' => 25,
            'UTS' => 25,
            'UAS' => 25,
            'Presensi' => 15,
            'Keaktifan' => 5,
            'Etika' => 5,
        ];

        // Merge defaults with existing (existing overrides default)
        // If existing is empty, it will be full defaults.
        // If partial, we fill missing keys with defaults.
        $this->bobotData = array_merge($defaults, $existing);

        $this->form->fill($this->bobotData);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Konfigurasi Bobot Penilaian')
                    ->description('Total bobot harus tepat 100%.')
                    ->schema([
                        TextInput::make('Tugas')
                            ->label('Tugas (Rata-rata)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('UTS')
                            ->label('Ujian Tengah Semester (UTS)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('UAS')
                            ->label('Ujian Akhir Semester (UAS)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('Presensi')
                            ->label('Presensi (Kehadiran)')
                            ->helperText('Dihitung otomatis bedasarkan jumlah kehadiran.')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('Keaktifan')
                            ->label('Keaktifan Kelas')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('Etika')
                            ->label('Sikap & Etika')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                    ])->columns(2),
            ])
            ->statePath('bobotData');
    }

    public function save()
    {
        $data = $this->form->getState();

        // VALIDATION: Total 100%
        $total = array_sum($data);

        if ($total != 100) {
            Notification::make()
                ->title('Validasi Gagal')
                ->body("Total bobot saat ini $total%. Total harus tepat 100%.")
                ->danger()
                ->send();

            return;
        }

        // VALIDATION: Active Semester (Gatekeeper)
        $kelas = KelasKuliah::with('semester')->where('id_kelas_kuliah', $this->record_id)->first();
        if ($kelas->semester->a_periode_aktif != '1') {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Semester sudah tidak aktif. Perubahan bobot tidak diizinkan.')
                ->danger()
                ->send();

            return;
        }

        // Save Logic
        DB::transaction(function () use ($data) {
            foreach ($data as $komponen => $bobot) {
                KomponenBobotKelas::updateOrCreate(
                    [
                        'id_kelas_kuliah' => $this->record_id,
                        'nama_komponen' => $komponen,
                    ],
                    [
                        'bobot' => $bobot,
                    ]
                );
            }
        });

        Notification::make()
            ->title('Berhasil Disimpan')
            ->body('Konfigurasi bobot penilaian berhasil diperbarui.')
            ->success()
            ->send();
    }
}
