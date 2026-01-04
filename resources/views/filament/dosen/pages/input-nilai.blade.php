<x-filament-panels::page>
    {{-- Header Info --}}
    <div class="mb-4 p-4 bg-white rounded-lg shadow border border-gray-100">
        <h2 class="text-lg font-bold text-gray-800">{{ $kelas->matkul->nama_mata_kuliah ?? '-' }}</h2>
        <p class="text-gray-600 text-sm">{{ $kelas->nama_kelas_kuliah }} - {{ $kelas->prodi->nama_program_studi }}</p>
    </div>

    {{-- The Table --}}
    {{ $this->table }}
</x-filament-panels::page>
