<x-filament-panels::page>

    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Profil Perguruan Tinggi</h2>
                <p class="text-sm text-gray-500 mt-1">Informasi dasar institusi pendidikan tinggi</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-6">
            <!-- Kolom Kiri (Preview Logo - 4 dari 12 kolom) -->
            <div class="md:col-span-4">
                <h3 class="text-lg font-medium mb-4">Preview Logo</h3>

                <div class="border rounded-lg p-4 min-h-64 flex items-center justify-center">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo Preview" class="max-w-full max-h-64 object-contain"
                            onerror="this.classList.add('hidden'); document.getElementById('no-logo-message').classList.remove('hidden');">
                        <div id="no-logo-message" class="hidden">
                            <p class="text-gray-500 italic">Gambar tidak dapat dimuat</p>
                        </div>
                    @else
                        <div class="text-center">
                            <div class="text-gray-400 mb-2">
                                <!-- Placeholder icon atau gambar default -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 italic">Logo belum diupload</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Kolom Kanan (Form Upload - 8 dari 12 kolom) -->
            <div class="md:col-span-8">
                <h3 class="text-lg font-medium mb-4">Upload Logo</h3>

                <div class="space-y-4">
                    {{ $this->form }}

                    <div class="pt-4">
                        <x-filament::button wire:click="updateLogo" color="primary" :disabled="!($data['logo'] ?? null)">
                            Simpan Logo
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Profil -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                <span class="text-sm font-medium text-blue-800 w-24">Kode PT</span>
                <span class="text-sm text-blue-900 font-semibold">: {{ $kode_pt ?? '-' }}</span>
            </div>

            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                <span class="text-sm font-medium text-blue-800 w-24">Nama PT</span>
                <span class="text-sm text-blue-900 font-semibold">: {{ $nama_pt ?? '-' }}</span>
            </div>

            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                <span class="text-sm font-medium text-blue-800 w-24">Telephone</span>
                <span class="text-sm text-blue-900 font-semibold">: {{ $telephone ?? '-' }}</span>
            </div>

            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                <span class="text-sm font-medium text-blue-800 w-24">Faximile</span>
                <span class="text-sm text-blue-900 font-semibold">: {{ $faximile ?? '-' }}</span>
            </div>

            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                <span class="text-sm font-medium text-blue-800 w-24">Email</span>
                <span class="text-sm text-blue-900 font-semibold">: {{ $email ?? '-' }}</span>
            </div>

            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                <span class="text-sm font-medium text-blue-800 w-24">Website</span>
                <span class="text-sm text-blue-900 font-semibold">:
                    @if ($website)
                        <a href="{{ $website }}" target="_blank"
                            class="text-blue-600 hover:text-blue-800 underline">{{ $website }}</a>
                    @else
                        -
                    @endif
                </span>
            </div>
        </div>
        <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg mt-5">
            <span class="text-sm font-medium text-green-800">Total Perguruan Tinggi</span>
            <span class="text-sm text-green-900 font-semibold">: {{ $countAllPt ?? '-' }}</span>
        </div>
        <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg mt-5">
            <span class="text-sm font-medium text-green-800">Total Program Studi</span>
            <span class="text-sm text-green-900 font-semibold">: {{ $countAllProdi ?? '-' }}</span>
        </div>
    </div>


    <div>
        @livewire('prodi-table')
    </div>
    <div>
        @livewire('semester-table')
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            @livewire('jalur-masuk-table')
        </div>
        <div>
            @livewire('jenis-daftar-table')
        </div>
    </div>
    <div>
        @livewire('pembiayaan-table')
    </div>
    <div>
        @livewire('bidang-minat-table')
    </div>
</x-filament-panels::page>
