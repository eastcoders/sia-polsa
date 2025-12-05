<?php

namespace App\Filament\Pages;

use App\Jobs\SyncAllProdiJob;
use App\Jobs\SyncAllPtJob;
use App\Jobs\SyncProfilPTJob;
use App\Models\AllProdi;
use App\Models\PerguruanTinggi;
use App\Models\ProfilePT;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use UnitEnum;

class MasterPerkuliahan extends Page implements HasActions, HasSchemas
{
    use InteractsWithActions, InteractsWithSchemas, WithFileUploads;

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    protected string $view = 'filament.pages.master-perkuliahan';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $title = 'Data Master Perkuliahan';

    protected static string|UnitEnum|null $navigationGroup = 'Master Record';

    protected static ?int $navigationSort = 2;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    // Profile PT Properties
    public $kode_pt;

    public $nama_pt;

    public $telephone;

    public $faximile;

    public $email;

    public $website;

    // Logo Properties
    public $logo;

    public $logoUrl;

    public $logoMedia;

    // Count Properties
    public $countAllPt;

    public $countAllProdi;

    // Form Data
    public ?array $data = [];

    /*
    |--------------------------------------------------------------------------
    | Lifecycle Hooks
    |--------------------------------------------------------------------------
    */

    public static function getNavigationLabel(): string
    {
        return 'Master Perkuliahan';
    }

    public function mount()
    {
        $this->initializeCounts();
        $this->loadProfileData();
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function getActions(): array
    {
        return [
            $this->syncProfilAction(),
            $this->syncAllProdiAction(),
            $this->syncAllPtAction(),
        ];
    }

    public function syncProfilAction(): Action
    {
        return $this->createSyncAction(
            'sync',
            'Profile PT',
            'primary',
            'Proses sinkronisasi Profile PT sedang berjalan di belakang layar.',
            SyncProfilPTJob::class
        );
    }

    public function syncAllProdiAction(): Action
    {
        return $this->createSyncAction(
            'sync_all_prodi',
            'All Prodi',
            'info',
            'Proses sinkronisasi All Prodi sedang berjalan di belakang layar.',
            SyncAllProdiJob::class
        );
    }

    public function syncAllPtAction(): Action
    {
        return $this->createSyncAction(
            'sync_all_pt',
            'All PT',
            'success',
            'Proses sinkronisasi All PT sedang berjalan di belakang layar.',
            SyncAllPtJob::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Forms
    |--------------------------------------------------------------------------
    */

    /**
     * Form untuk upload logo perguruan tinggi
     */
    public function form(Schema $schema)
    {
        return $schema->components([
            FileUpload::make('logo')
                ->label('Logo Perguruan Tinggi (Opsional)')
                ->disk('public')
                ->directory('logos')
                ->preserveFilenames()
                ->image()
                ->maxSize(2048)
                ->imageEditor()
                ->required(false)
                ->afterStateUpdated(function (FileUpload $component, ?string $state) {
                    $this->handleLogoUpload($state);
                })
                ->hint('Format: JPG, PNG, maksimal 2MB'),
        ])->columns(1);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Inisialisasi data jumlah PT dan Prodi
     */
    protected function initializeCounts(): void
    {
        $this->countAllPt = PerguruanTinggi::count() ?: '0';
        $this->countAllProdi = AllProdi::count() ?: '0';
    }

    /**
     * Memuat data profil perguruan tinggi
     */
    protected function loadProfileData(): void
    {
        $profil = ProfilePT::first();

        if (! $profil) {
            $this->setDefaultProfileValues();

            return;
        }

        $data = [
            'kode_pt' => $profil->kode_perguruan_tinggi,
            'nama_pt' => $profil->nama_perguruan_tinggi,
            'telephone' => $profil->telepon,
            'faximile' => $profil->faximile,
            'email' => $profil->email,
            'website' => $profil->website,
            // 'logoUrl' => null,
        ];

        foreach ($data as $property => $value) {
            $this->$property = $value;
        }

        $this->loadLogo($profil);
    }

    /**
     * Mengatur nilai default untuk profil
     */
    protected function setDefaultProfileValues(): void
    {
        $defaults = [
            'kode_pt' => '-',
            'nama_pt' => 'Belum diisi/sync',
            'telephone' => '-',
            'faximile' => '-',
            'email' => '-',
            'website' => '-',
            'logoUrl' => null,
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Memuat logo dari media library
     */
    protected function loadLogo(ProfilePT $profil): void
    {
        if ($media = $profil->getFirstMedia('logo')) {
            $this->logoMedia = $media;
            $this->logoUrl = $media->getUrl();
        }
    }

    /**
     * Membuat action untuk sinkronisasi
     */
    protected function createSyncAction(
        string $name,
        string $label,
        string $color,
        string $message,
        string $jobClass
    ): Action {
        return Action::make($name)
            ->label($label)
            ->button()
            ->color($color)
            ->requiresConfirmation()
            ->action(function () use ($jobClass, $message) {
                $jobClass::dispatch();

                Notification::make()
                    ->title('Sinkronisasi Dimulai')
                    ->body($message)
                    ->success()
                    ->send();
            });
    }

    /**
     * Menangani proses upload logo
     */
    protected function handleLogoUpload(?string $filePath): void
    {
        $profil = ProfilePT::firstOrCreate([]);

        if (! $filePath) {
            $profil->clearMediaCollection('logo');
            $this->updateLogoPreview($profil);

            return;
        }

        // Pastikan file ada di storage
        if (! Storage::disk('public')->exists($filePath)) {
            return;
        }

        // Hapus logo lama
        $profil->clearMediaCollection('logo');

        // Simpan logo baru
        $profil
            ->addMediaFromDisk($filePath, 'public')
            ->preservingOriginal()
            ->toMediaCollection('logo');

        $this->updateLogoPreview($profil);
    }

    /**
     * Memperbarui preview logo
     */
    protected function updateLogoPreview(ProfilePT $profil): void
    {
        $this->logoMedia = $profil->getFirstMedia('logo');
        $this->logoUrl = $this->logoMedia ? $this->logoMedia->getUrl() : null;
    }

    /**
     * Memperbarui logo perguruan tinggi
     */
    public function updateLogo(): void
    {
        // Inisialisasi form jika belum ada
        if (! isset($this->form)) {
            $this->form($this->makeForm());
        }

        // Validasi dan simpan data
        $this->validate();
        $profil = ProfilePT::firstOrCreate([]);

        if (isset($this->data['logo'])) {
            $this->handleLogoUpload($this->data['logo']);
        }

        Notification::make()
            ->title('Logo berhasil diperbarui')
            ->success()
            ->send();

        $state = $this->form->getState();

        // Since you're using statePath('data'), the logo should be in $state['logo']
        $path = $state['logo'] ?? null;

        if (! $path) {
            Notification::make()
                ->title('Gagal Mengupdate Logo')
                ->body('Tidak ada file logo yang diupload.')
                ->danger()
                ->send();

            return;
        }

        // Make sure to handle the case where $path might be an array (multiple files)
        // But since you're using single file upload, it should be a string
        if (is_array($path)) {
            $path = $path[0] ?? null;
        }

        if (! $path) {
            Notification::make()
                ->title('Gagal Mengupdate Logo')
                ->body('File logo tidak valid.')
                ->danger()
                ->send();

            return;
        }

        // Pastikan file-nya memang ada di disk public
        if (! Storage::disk('public')->exists($path)) {
            Notification::make()
                ->title('Gagal Mengupdate Logo')
                ->body('File upload tidak ditemukan di storage (public/'.$path.').')
                ->danger()
                ->send();

            return;
        }

        $profil = ProfilePT::firstOrCreate([]);

        // Kalau mau cuma 1 logo saja:
        $profil->clearMediaCollection('logo');

        // Ambil dari DISK 'public', path relatif 'logos/xxx.jpg'
        $profil
            ->addMediaFromDisk($path, 'public')
            ->preservingOriginal()
            ->toMediaCollection('logo');

        Notification::make()
            ->title('Logo Berhasil Diperbarui')
            ->success()
            ->send();
    }
}
