<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class ProfilPt extends Component
{
    use WithFileUploads;

    public $kode_pt = '065008';

    public $nama_pt = 'Politeknik Sawunggalih Aji';

    public $telephone = '0275-642466,3140444';

    public $faximile = '0275-642467';

    public $email = 'info@polsa.ac.id';

    public $website = 'https://www.polsa.ac.id';

    public $logo; // Untuk upload file

    public $logoUrl; // Untuk preview

    protected $rules = [
        'logo' => 'nullable|image|max:1024', // max 1MB
    ];

    public function syncProfile()
    {
        // Logika sync profile dari SISTER atau API
        session()->flash('message', 'Sync profil berhasil!');
        $this->emit('notify', 'Sync profil berhasil!');
    }

    public function syncAllPt()
    {
        // Logika sync semua perguruan tinggi
        session()->flash('message', 'Sync semua PT berhasil!');
        $this->emit('notify', 'Sync semua PT berhasil!');
    }

    public function syncAllProdi()
    {
        // Logika sync semua program studi
        session()->flash('message', 'Sync semua prodi berhasil!');
        $this->emit('notify', 'Sync semua prodi berhasil!');
    }

    public function updatedLogo()
    {
        $this->validateOnly('logo');

        if ($this->logo) {
            // Simpan logo ke storage atau database
            $this->logoUrl = $this->logo->temporaryUrl();
            // Jika ingin simpan permanen:
            // $path = $this->logo->store('logos', 'public');
            // $this->logoUrl = Storage::url($path);
        }
    }

    public function render()
    {
        return view('livewire.profil-pt');
    }
}
