<div class="space-y-4">
    {{-- Payment Status Card --}}
    <div class="p-4 bg-warning-50 dark:bg-warning-950/50 rounded-lg border border-warning-200 dark:border-warning-800">
        <div class="flex items-start gap-3">
            <x-heroicon-o-clock class="w-6 h-6 text-warning-600 dark:text-warning-400 flex-shrink-0 mt-0.5" />
            <div>
                <h4 class="font-semibold text-warning-700 dark:text-warning-300">Menunggu Verifikasi</h4>
                <p class="text-sm text-warning-600 dark:text-warning-400 mt-1">
                    Pembayaran Anda sedang dalam proses verifikasi oleh bagian keuangan.
                    Proses ini biasanya memakan waktu 1-2 hari kerja.
                </p>
            </div>
        </div>
    </div>

    {{-- Payment Details --}}
    @if($payment)
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Detail Pembayaran</h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Nomor Pembayaran</span>
                    <p class="font-mono font-semibold text-sm">{{ $payment->payment_number }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Metode Pembayaran</span>
                    <p class="font-semibold">{{ $payment->payment_method->label() }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Tanggal Pengajuan</span>
                    <p class="font-semibold">{{ $payment->created_at->format('d F Y H:i') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Jumlah</span>
                    <p class="font-semibold">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
                </div>
            </div>

            @if($payment->notes)
                <div class="mt-3 pt-3 border-t dark:border-gray-700">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Catatan</span>
                    <p class="text-sm">{{ $payment->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Proof Preview --}}
        @if($payment->proof_file_path)
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Bukti Pembayaran</h4>
                <div class="border dark:border-gray-700 rounded-lg overflow-hidden">
                    @php
                        $extension = pathinfo($payment->proof_file_path, PATHINFO_EXTENSION);
                    @endphp
                    
                    @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <img 
                            src="{{ Storage::url($payment->proof_file_path) }}" 
                            alt="Bukti Pembayaran" 
                            class="w-full max-h-64 object-contain bg-gray-100 dark:bg-gray-900"
                        />
                    @elseif(strtolower($extension) === 'pdf')
                        <div class="p-4 text-center bg-gray-100 dark:bg-gray-900">
                            <x-heroicon-o-document class="w-12 h-12 mx-auto text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500">File PDF diunggah</p>
                            <div class="mt-3">
                                <a 
                                    href="{{ Storage::url($payment->proof_file_path) }}" 
                                    target="_blank"
                                    class="inline-flex items-center justify-center gap-1 font-medium text-primary-600 hover:text-primary-500 text-sm"
                                >
                                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                    Lihat File
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-4 text-center bg-gray-100 dark:bg-gray-900">
                            <x-heroicon-o-paper-clip class="w-12 h-12 mx-auto text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500">File diunggah</p>
                            <div class="mt-3">
                                <a 
                                    href="{{ Storage::url($payment->proof_file_path) }}" 
                                    target="_blank"
                                    class="inline-flex items-center justify-center gap-1 font-medium text-primary-600 hover:text-primary-500 text-sm"
                                >
                                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                    Download File
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif

    {{-- Info Box --}}
    <div class="p-3 bg-blue-50 dark:bg-blue-950/50 rounded-lg border border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-2">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
            <p class="text-sm text-blue-700 dark:text-blue-300">
                Jika pembayaran belum diverifikasi dalam 3 hari kerja, silakan hubungi bagian keuangan.
            </p>
        </div>
    </div>
</div>
