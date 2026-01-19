<div class="space-y-4">
    {{-- Invoice Summary --}}
    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Nomor Invoice</span>
                <p class="font-semibold">{{ $invoice->invoice_number }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Periode</span>
                <p class="font-semibold">{{ $invoice->period_date->format('F Y') }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Jatuh Tempo</span>
                <p class="font-semibold {{ $invoice->isOverdue() && $invoice->status->value === 'UNPAID' ? 'text-danger-600' : '' }}">
                    {{ $invoice->due_date->format('d F Y') }}
                    @if($invoice->isOverdue() && $invoice->status->value === 'UNPAID')
                        <span class="text-xs text-danger-600">(Terlambat)</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                <p>
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                        {{ $invoice->status->value === 'PAID' ? 'bg-success-100 text-success-700' : 'bg-danger-100 text-danger-700' }}">
                        {{ $invoice->status->label() }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    {{-- Invoice Items --}}
    <div>
        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Rincian Biaya</h4>
        <div class="border dark:border-gray-700 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Komponen</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($invoice->items as $item)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $item->component_name }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white">
                                Rp {{ number_format($item->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-2 text-sm text-center text-gray-500">
                                Tidak ada rincian biaya
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 dark:text-white">Total</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Payment Info --}}
    @if($invoice->status->value === 'PAID')
        <div class="p-4 bg-success-50 dark:bg-success-950/50 rounded-lg">
            <div class="flex items-center gap-2">
                <x-heroicon-o-check-circle class="w-5 h-5 text-success-600" />
                <span class="font-medium text-success-700 dark:text-success-300">
                    Dibayar pada {{ $invoice->paid_at?->format('d F Y H:i') ?? '-' }}
                </span>
            </div>
        </div>
    @endif
</div>
