<x-filament-panels::page>
    {{-- Header Informasi Kelas --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex justify-between items-start">
        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ $kelas->matkul->nama_mata_kuliah ?? '-' }}</h2>
            <p class="text-sm text-gray-500">{{ $kelas->nama_kelas_kuliah }} • {{ $kelas->prodi->nama_program_studi ?? '-' }}</p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                {{ $kelas->semester->nama_semester ?? '-' }}
            </span>
            
            {{-- Dropdown History Pertemuan --}}
            <div class="w-64">
                <select 
                    wire:model.live="selectedPertemuanId" 
                    wire:change="loadPertemuan"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                >
                    <option value="">Pertemuan Baru</option>
                    @foreach($historyPertemuan as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Form Data Pertemuan --}}
    <div>
        {{ $this->form }}
    </div>

    {{-- Tabel List Peserta --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-700">Daftar Hadir Mahasiswa</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3">NIM</th>
                        <th scope="col" class="px-6 py-3">Nama Mahasiswa</th>
                        <th scope="col" class="px-6 py-3 w-48 pt-4">Status Kehadiran</th>
                        <th scope="col" class="px-6 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peserta as $index => $mhs)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">
                                {{ $loop->iteration + ($peserta->currentPage() - 1) * $peserta->perPage() }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $mhs->riwayatPendidikan->nim ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $mhs->riwayatPendidikan->mahasiswa->nama_lengkap ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                {{-- Dropdown Status Kehadiran (Livewire Bound) --}}
                                <select 
                                    wire:model="attendanceData.{{ $mhs->id_registrasi_mahasiswa }}.status"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                >
                                    <option value="hadir">Hadir</option>
                                    <option value="sakit">Sakit</option>
                                    <option value="izin">Izin</option>
                                    <option value="alpha">Alpha</option>
                                </select>
                            </td>
                            <td class="px-6 py-4">
                                {{-- Input Keterangan (Livewire Bound) --}}
                                <input 
                                    type="text" 
                                    wire:model="attendanceData.{{ $mhs->id_registrasi_mahasiswa }}.keterangan"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                                    placeholder="Catatan..."
                                >
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 italic">
                                Tidak ada data peserta kelas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-4 border-t border-gray-100">
            {{ $peserta->links() }}
        </div>
    </div>
    
    {{-- Tombol Simpan --}}
    <div class="flex justify-end mt-4">
        <x-filament::button wire:click="save" color="success">
            Simpan Presensi
        </x-filament::button>
    </div>

</x-filament-panels::page>
