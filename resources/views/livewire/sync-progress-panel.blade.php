<div wire:poll.3s="poll">
    {{-- Active Batches (Running) --}}
    @if(count($activeBatches) > 0)
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 mb-4 border border-blue-200 dark:border-blue-800">
            <div class="flex items-center gap-2 mb-3">
                <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-500 border-t-transparent"></div>
                <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200">Sinkronisasi Berlangsung</h3>
            </div>

            @foreach($activeBatches as $batch)
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 mb-2 last:mb-0 shadow-sm">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate max-w-xs">
                            {{ $batch->name }}
                        </span>
                        <span class="text-xs text-blue-600 dark:text-blue-400 font-semibold">
                            {{ $batch->progress }}%
                        </span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-2">
                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-500"
                             style="width: {{ $batch->progress }}%"></div>
                    </div>

                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $batch->processed_jobs }} / {{ $batch->total_jobs }} jobs</span>
                        <span>
                            @if($batch->failed_jobs > 0)
                                <span class="text-red-500">{{ $batch->failed_jobs }} gagal</span>
                            @else
                                {{ $batch->pending_jobs }} pending
                            @endif
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Pending Jobs in Queue --}}
    @if($pendingJobsCount > 0)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 mb-4 border border-yellow-200 dark:border-yellow-800">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>{{ $pendingJobsCount }}</strong> job menunggu di queue
                </span>
            </div>
        </div>
    @endif

    {{-- Recent Completed Batches --}}
    @if(count($recentBatches) > 0)
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Riwayat Sync Terbaru</h3>

            @foreach($recentBatches as $batch)
                <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700 last:border-0">
                    <div class="flex items-center gap-2">
                        @if($batch->has_failures)
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-100 dark:bg-red-900/30">
                                <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        @else
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30">
                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        @endif
                        <span class="text-sm text-gray-600 dark:text-gray-400 truncate max-w-xs">
                            {{ $batch->name }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-500">
                        {{ \Carbon\Carbon::parse($batch->finished_at)->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Empty State --}}
    @if(count($activeBatches) === 0 && count($recentBatches) === 0 && $pendingJobsCount === 0)
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 text-center border border-gray-200 dark:border-gray-700">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada proses sinkronisasi saat ini</p>
        </div>
    @endif
</div>
