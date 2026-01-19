<x-filament-widgets::widget>
    @if($overdueCount > 0)
        <x-filament::section class="bg-danger-50 dark:bg-danger-950/50 border-danger-200 dark:border-danger-800">
            <div class="flex items-center gap-4">
                <x-heroicon-o-exclamation-triangle class="w-10 h-10 text-danger-600 dark:text-danger-400" />
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-danger-700 dark:text-danger-300">Tagihan Terlambat!</h2>
                    <p class="mt-1 text-danger-600 dark:text-danger-400">
                        Anda memiliki <span class="font-bold text-xl">{{ $overdueCount }}</span> tagihan yang belum dibayar 
                        senilai <span class="font-bold">Rp {{ number_format($overdueAmount, 0, ',', '.') }}</span>.
                        <br>
                        <span class="text-sm">Segera lakukan pembayaran untuk menghindari sanksi akademik.</span>
                    </p>
                    <div class="mt-3">
                        <x-filament::button
                            color="danger"
                            tag="a"
                            href="/mahasiswa/tagihan-saya"
                        >
                            Bayar Sekarang
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @elseif($nearDueCount > 0)
        <x-filament::section class="bg-warning-50 dark:bg-warning-950/50 border-warning-200 dark:border-warning-800">
            <div class="flex items-center gap-4">
                <x-heroicon-o-clock class="w-10 h-10 text-warning-600 dark:text-warning-400" />
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-warning-700 dark:text-warning-300">Tagihan Hampir Jatuh Tempo</h2>
                    <p class="mt-1 text-warning-600 dark:text-warning-400">
                        Anda memiliki <span class="font-bold">{{ $nearDueCount }}</span> tagihan yang akan jatuh tempo dalam 7 hari ke depan
                        senilai <span class="font-bold">Rp {{ number_format($nearDueAmount ?? 0, 0, ',', '.') }}</span>.
                    </p>
                    <div class="mt-3">
                        <x-filament::button
                            color="warning"
                            tag="a"
                            href="/mahasiswa/tagihan-saya"
                        >
                            Lihat Tagihan
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-widgets::widget>
