<x-filament-panels::page>
    {{-- Filter Form --}}
    <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100 w-full">
        {{ $this->form }}
    </div>

    {{-- Container Grid: Mengatur layout menjadi grid yang responsif (1 kolom di mobile, 2 di tablet, 3 di desktop) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">

        @forelse($kelasAjar as $data)
            {{-- Mengambil objek kelasKuliah untuk memudahkan akses data --}}
            @php
                $kelas = $data->kelasKuliah;
                // Mengambil jadwal pertama (asumsi satu kelas punya jadwal utama)
                $jadwal = $kelas->jadwalPerkuliahan->first(); 
            @endphp

            {{-- Card Utama --}}
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100 flex flex-col h-full">
                
                {{-- Card Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white relative">
                    {{-- Nama Mata Kuliah --}}
                    <h3 class="text-xl font-bold leading-tight mb-1">
                        {{ $kelas->matkul->nama_mata_kuliah ?? 'Mata Kuliah Tidak Ditemukan' }}
                    </h3>
                    {{-- Nama Kelas --}}
                    <p class="text-blue-100 text-sm font-medium">
                        {{ $kelas->nama_kelas_kuliah }}
                    </p>
                    
                    {{-- Badge SKS --}}
                    <div class="absolute top-4 right-4 bg-white/20 backdrop-blur-sm px-2 py-1 rounded text-xs font-semibold">
                        {{ $kelas->sks_mk }} SKS
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-6 flex-grow space-y-4">
                    
                    {{-- Program Studi --}}
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <x-heroicon-o-academic-cap class="w-5 h-5 text-gray-400" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Program Studi</p>
                            <p class="text-sm text-gray-800 font-medium">
                                {{ $kelas->prodi->nama_program_studi ?? '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- Semester --}}
                    <div class="flex items-start space-x-3">
                         <div class="flex-shrink-0 mt-1">
                            <x-heroicon-o-calendar class="w-5 h-5 text-gray-400" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Semester</p>
                            <p class="text-sm text-gray-800 font-medium">
                                {{ $kelas->semester->nama_semester ?? '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- Jadwal & Ruang --}}
                    <div class="flex items-start space-x-3">
                         <div class="flex-shrink-0 mt-1">
                             <x-heroicon-o-clock class="w-5 h-5 text-gray-400" />
                         </div>
                        <div class="w-full">
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Jadwal & Ruang</p>
                            
                            @if($kelas->jadwalPerkuliahan && $kelas->jadwalPerkuliahan->count() > 0)
                                <div class="space-y-3">
                                    @foreach($kelas->jadwalPerkuliahan as $jadwal)
                                        <div class="bg-gray-50 p-2 rounded-md border border-gray-100">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-xs font-bold px-2 py-0.5 rounded
                                                    {{ strtolower($jadwal->kelas_pagi_sore) == 'sore' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                                                    {{ strtoupper($jadwal->kelas_pagi_sore ?? 'REGULER') }}
                                                </span>
                                                <span class="text-xs text-gray-500 capitalize font-medium">
                                                    {{ $jadwal->hari ?? '-' }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-800 font-medium">
                                                Pukul: 
                                                {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                            </p>
                                            <p class="text-xs text-gray-600 truncate" title="{{ $jadwal->ruangKelas->nama_ruang_kelas ?? '-' }}">
                                                <x-heroicon-o-map-pin class="w-3 h-3 inline mr-0.5 text-gray-400"/>
                                                {{ 'Ruang: '. $jadwal->ruangKelas->nama_ruang_kelas ?? 'Belum ditentukan' }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic">Jadwal belum diatur</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex items-center text-gray-600" title="Jumlah Peserta">
                        <x-heroicon-o-users class="w-5 h-5 mr-2 text-indigo-500" />
                        <span class="text-sm font-semibold">{{ $data->peserta_kelas_count ?? 0 }}</span>
                        <span class="text-xs ml-1 text-gray-500">Mahasiswa</span>
                    </div>

                    {{-- Tombol Detail / Aksi --}}
                    <a href="{{ \App\Filament\Dosen\Pages\PresensiKelas::getUrl(['record' => $kelas->id_kelas_kuliah]) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium hover:underline focus:outline-none">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>
        
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-12 text-center text-gray-500">
                <x-heroicon-o-clipboard-document-list class="w-16 h-16 text-gray-300 mb-4" />
                <h3 class="text-lg font-medium text-gray-900">Belum ada kelas yang ditemukan.</h3>
                <p class="text-sm">Coba ubah filter semester atau hubungi administrator.</p>
            </div>
        @endforelse

    </div>

    {{-- Pagination Links --}}
    <div class="mt-4">
        {{ $kelasAjar->links() }}
    </div>
</x-filament-panels::page>
