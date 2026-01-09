<x-filament-widgets::widget>
    <x-filament::section class="bg-danger-50 text-danger-700 border-danger-200">
        <div class="flex items-center gap-4">
            <x-heroicon-o-exclamation-triangle class="w-10 h-10 text-danger-600" />
            <div class="flex-1">
                <h2 class="text-lg font-bold">Kuesioner Wajib Tertunda!</h2>
                <p class="mt-1">
                    Anda memiliki <span class="font-bold text-xl">{{ $pendingCount }}</span> kuesioner yang belum diisi. 
                    Anda tidak dapat mendownload Kartu Ujian sebelum menyelesaikan ini.
                </p>
                <div class="mt-3">
                    <x-filament::button
                        color="danger"
                        tag="a"
                        href="/mahasiswa/isi-kuesioner"
                    >
                        Isi Kuesioner Sekarang
                    </x-filament::button>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
