<x-filament-panels::page>
    {{-- Sync Progress Panel --}}
    <div>
        @livewire('wilayah-table')
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            @livewire('agama-table')
        </div>
        <div>
            @livewire('pekerjaan-table')
        </div>
        <div>
            @livewire('penghasilan-table')
        </div>
        <div>
            @livewire('jenjang-pendidikan-table')
        </div>
        <div>
            @livewire('alat-transportasi-table')
        </div>
        <div>
            @livewire('jenis-tinggal-table')
        </div>
    </div>
</x-filament-panels::page>
