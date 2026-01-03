@php
    // Ambil ID Registrasi Mahasiswa dari peserta kelas yang terkait dengan user saat ini
    $peserta = $record->pesertaKelas->first();
    $idRegistrasi = $peserta?->id_registrasi_mahasiswa;
    
    // Sort pertemuan dari yang terbaru atau terlama, opsional. Default biasanya urut pertemuan_ke.
    $pertemuans = $record->pertemuanKelas->sortBy('pertemuan_ke');
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Pertemuan</th>
                <th scope="col" class="px-6 py-3">Tanggal</th>
                <th scope="col" class="px-6 py-3">Materi</th>
                <th scope="col" class="px-6 py-3">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pertemuans as $pertemuan)
                @php
                    // Cari data presensi untuk mahasiswa ini pada pertemuan tersebut
                    // Kita asumsikan presensiMahasiswas sudah di-load dengan filter atau kita filter manual di sini
                    // Mengingat di view ini kita mungkin tidak bisa control eager load dengan mudah tanpa logic di Resource,
                    // kita filter manual collection-nya.
                    $presensi = $pertemuan->presensiMahasiswas->firstWhere('id_registrasi_mahasiswa', $idRegistrasi);
                    
                    $status = $presensi?->status_kehadiran;
                    $color = match($status) {
                        'hadir' => 'text-success-600 bg-success-50',
                        'izin' => 'text-warning-600 bg-warning-50',
                        'sakit' => 'text-warning-600 bg-warning-50',
                        'alpha' => 'text-danger-600 bg-danger-50',
                        default => 'text-gray-600 bg-gray-50',
                    };
                    
                    $statusLabel = $status ? (in_array($status, ['hadir', 'izin', 'sakit', 'alpha']) ? [
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpha' => 'Alpha'
                    ][$status] : $status) : '-';
                @endphp
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ $pertemuan->pertemuan_ke }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $pertemuan->tanggal ? \Carbon\Carbon::parse($pertemuan->tanggal)->format('d M Y') : '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="line-clamp-2 max-w-xs" title="{{ $pertemuan->materi }}">
                            {{ $pertemuan->materi ?? '-' }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $color }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                        Belum ada data pertemuan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
