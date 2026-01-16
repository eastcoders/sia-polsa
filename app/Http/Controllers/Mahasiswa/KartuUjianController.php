<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KartuUjianController extends Controller
{
    public function print(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            abort(403, 'Data mahasiswa tidak ditemukan.');
        }

        $riwayat = $mahasiswa->riwayatPendidikan->first();

        if (!$riwayat) {
            abort(403, 'Riwayat pendidikan tidak ditemukan.');
        }

        // Get active semester info
        $activeSemester = Semester::where('a_periode_aktif', '1')
            ->orderBy('id_semester', 'desc')
            ->first();

        // Get jenis ujian from request (default to UAS)
        $jenisUjian = $request->get('jenis', 'UAS');

        // Get all published jadwal ujian for this student
        $jadwalUjians = JadwalUjian::query()
            ->whereHas('kelasKuliah.pesertaKelas.riwayatPendidikan', function ($q) use ($mahasiswa) {
                $q->where('id_mahasiswa', $mahasiswa->id_mahasiswa);
            })
            ->whereHas('kelasKuliah', function ($q) use ($activeSemester) {
                $q->where('id_semester', $activeSemester?->id_semester);
            })
            ->where('jenis_ujian', $jenisUjian)
            ->published()
            ->with(['kelasKuliah.matkul', 'ruangKelas'])
            ->orderBy('tanggal_ujian')
            ->orderBy('jam_mulai')
            ->get();

        return view('mahasiswa.kartu-ujian-print', compact(
            'mahasiswa',
            'riwayat',
            'jadwalUjians',
            'activeSemester',
            'jenisUjian'
        ));
    }
}
