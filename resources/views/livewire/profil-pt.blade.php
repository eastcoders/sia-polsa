<div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Profil Perguruan Tinggi</h2>
            <p class="text-sm text-gray-500 mt-1">Informasi dasar institusi pendidikan tinggi</p>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex space-x-2">
            <button wire:click="syncProfile"
                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors duration-200 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 00-15.356-2m15.356 2H15" />
                </svg>
                Sync Profile
            </button>

            <button wire:click="syncAllPt"
                class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-md transition-colors duration-200 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16v2H4V6zm4 8h8v2H8v-2z" />
                </svg>
                All PT
            </button>

            <button wire:click="syncAllProdi"
                class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium rounded-md transition-colors duration-200 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9M5 11V9m2 2a2 2 0 100 4h12a2 2 0 100-4H7z" />
                </svg>
                All Prodi
            </button>
        </div>
    </div>

    <!-- Logo Upload (Opsional) -->
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Logo Perguruan Tinggi (Opsional)</label>
        <div x-data="{ isUploading: false }" @file-upload-started.window="isUploading = true"
            @file-upload-finished.window="isUploading = false"
            class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-blue-400 transition-colors relative overflow-hidden"
            x-bind:class="{ 'opacity-70': isUploading }">

            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo PT" class="mx-auto h-24 w-auto object-contain mb-2">
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l-4-4m-4 4l4-4m4 4l4-4M8 12l4-4m4 4l-4 4" />
                </svg>
                <p class="text-sm text-gray-500">Klik untuk upload logo atau seret file ke sini</p>
            @endif

            <input type="file" wire:model="logo" accept="image/*"
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                x-on:change="$dispatch('file-upload-started')">

            <!-- Loading State -->
            <div x-show="isUploading" class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center">
                <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.644z">
                    </path>
                </svg>
            </div>
        </div>
        @error('logo')
            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
        @enderror
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
</div>
