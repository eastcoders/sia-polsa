<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\MataKuliah;
use App\Models\BiodataMahasiswa;
use App\Models\JadwalUjian;
use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use App\Models\RiwayatPendidikan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Filament\Resources\JadwalUjians\JadwalUjianResource;
use App\Filament\Mahasiswa\Resources\KartuUjians\KartuUjianResource;

class JadwalUjianTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createMataKuliah()
    {
        return MataKuliah::create([
            'id_matkul' => Str::uuid()->toString(),
            'nama_mata_kuliah' => $this->faker->sentence(3),
            'kode_mata_kuliah' => 'MK-' . rand(100, 999),
            'id_prodi' => 'PRODI-001',
            'id_jenis_mata_kuliah' => 'A',
            'id_kelompok_mata_kuliah' => 'A',
            'sks_mata_kuliah' => '3',
            'sks_tatap_muka' => '3',
            'sks_praktek' => '0',
            'sks_praktek_lapangan' => '0',
            'sks_simulasi' => '0',
        ]);
    }

    private function createKelasKuliah($matkul, $namaKelas)
    {
        return KelasKuliah::create([
            'id_kelas_kuliah' => Str::uuid()->toString(),
            'id_matkul' => $matkul->id_matkul,
            'id_prodi' => 'PRODI-001',
            'id_semester' => '20251',
            'nama_kelas_kuliah' => $namaKelas,
            'sks_mk' => '3',
            'sks_tm' => '3',
            'sks_prak' => '0',
            'sks_sim' => '0',
            'kapasitas' => '30',
        ]);
    }

    private function createMahasiswa()
    {
        return BiodataMahasiswa::create([
            'id_mahasiswa' => Str::uuid()->toString(),
            'nama_lengkap' => $this->faker->name,
            'jenis_kelamin' => 'L',
            'id_agama' => '1',
            'tanggal_lahir' => '2000-01-01',
            'tempat_lahir' => 'Jakarta',
            'kewarganegaraan' => 'ID',
            'nik' => $this->faker->unique()->numerify('################'),
            'nisn' => $this->faker->unique()->numerify('##########'),
            'npwp' => $this->faker->unique()->numerify('###############'),
            'kelurahan' => 'Test Kelurahan',
            'id_wilayah' => '001',
            'no_hp' => $this->faker->unique()->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'nama_ibu_kandung' => $this->faker->name('female'),
        ]);
    }

    /** @test */
    public function admin_can_create_jadwal_ujian_onsite()
    {
        $this->actingAs($user = User::factory()->create());

        $matkul = $this->createMataKuliah();
        $kelas = $this->createKelasKuliah($matkul, 'A');

        $data = [
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'jenis_ujian' => 'UTS',
            'mode_ujian' => 'ONSITE',
            'tanggal_ujian' => '2026-01-20',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'is_published' => true,
        ];

        $jadwal = JadwalUjian::create($data);

        $this->assertDatabaseHas('jadwal_ujians', [
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'jenis_ujian' => 'UTS',
            'mode_ujian' => 'ONSITE',
            'tanggal_ujian' => '2026-01-20 00:00:00'
        ]);

        $this->assertTrue($jadwal->is_published);
        $this->assertEquals('ONSITE', $jadwal->mode_ujian);
    }

    /** @test */
    public function admin_can_create_jadwal_ujian_take_home()
    {
        $this->actingAs($user = User::factory()->create());

        $matkul = $this->createMataKuliah();
        $kelas = $this->createKelasKuliah($matkul, 'B');

        $data = [
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'jenis_ujian' => 'UAS',
            'mode_ujian' => 'TAKE_HOME',
            'deadline_submission' => '2026-02-01 23:59:00',
            'submission_link' => 'https://classroom.google.com/test',
            'is_published' => true,
        ];

        JadwalUjian::create($data);

        $this->assertDatabaseHas('jadwal_ujians', [
            'mode_ujian' => 'TAKE_HOME',
            'deadline_submission' => '2026-02-01 23:59:00'
        ]);
    }

    /** @test */
    public function student_can_only_view_their_own_exams()
    {
        // 1. Setup Student
        $mahasiswa = $this->createMahasiswa();

        $user = User::factory()->create([
            'name' => $mahasiswa->nama_lengkap,
            'mahasiswa_id' => $mahasiswa->id
        ]);

        // 2. Setup Data
        $matkul = $this->createMataKuliah();
        $kelas = $this->createKelasKuliah($matkul, 'C');

        // Enroll Student
        $idRegistrasi = Str::uuid()->toString();

        $riwayat = RiwayatPendidikan::create([
            'id_mahasiswa' => $mahasiswa->id_mahasiswa,
            'id_biodata_mahasiswa' => $mahasiswa->id_mahasiswa,
            'id_registrasi_mahasiswa' => $idRegistrasi,
            'nim' => '12345678',
            'id_jenis_daftar' => '1',
            'id_jalur_daftar' => '1',
            'id_periode_masuk' => '20241',
            'tanggal_daftar' => now(),
            'id_perguruan_tinggi' => 'PT-001',
            'id_prodi' => 'PRODI-001',
            'biaya_masuk' => '0',
        ]);

        PesertaKelasKuliah::create([
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'id_registrasi_mahasiswa' => $idRegistrasi,
        ]);

        // Create Exam
        $exam = JadwalUjian::create([
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'jenis_ujian' => 'UAS',
            'mode_ujian' => 'ONSITE',
            'tanggal_ujian' => '2026-02-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'is_published' => true,
        ]);

        // 3. Act & Assert
        $this->actingAs($user);

        $query = KartuUjianResource::getEloquentQuery();

        $this->assertTrue($query->where('id', $exam->id)->exists(), 'Student should see their own exam');

        // Create another exam not enrolled
        $otherClass = $this->createKelasKuliah($matkul, 'D');
        $otherExam = JadwalUjian::create([
            'id_kelas_kuliah' => $otherClass->id_kelas_kuliah,
            'jenis_ujian' => 'UAS',
            'mode_ujian' => 'ONSITE',
            'tanggal_ujian' => '2026-02-11',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'is_published' => true
        ]);

        $this->assertFalse($query->where('id', $otherExam->id)->exists(), 'Student should NOT see exams they are not enrolled in');
    }
}
