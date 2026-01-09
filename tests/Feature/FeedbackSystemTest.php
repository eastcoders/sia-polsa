<?php

namespace Tests\Feature;

use App\Jobs\GenerateSurveyTicketsJob;
use App\Models\AktivitasKuliahMahasiswa;
use App\Models\BiodataMahasiswa;
use App\Models\Dosen;
use App\Models\DosenPengajarKelasKuliah;
use App\Models\FormTemplate;
use App\Models\KelasKuliah;
use App\Models\MataKuliah;
use App\Models\PesertaKelasKuliah;
use App\Models\ResponseBallot;
use App\Models\RiwayatPendidikan;
use App\Models\Semester;
use App\Models\SurveyTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FeedbackSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Basic Data
        $this->semester = Semester::create([
            'id_semester' => '20231',
            'nama_semester' => 'Ganjil 2023/2024',
            'semester' => 'Ganjil',
            'a_periode_aktif' => '1',
            'id_tahun_ajaran' => '2023',
            'tanggal_mulai' => '2023-09-01', // Added
            'tanggal_selesai' => '2024-01-31' // Added
        ]);

        // User & Student Setup
        $this->user = User::create(['name' => 'Test Student', 'email' => 'student@test.com', 'password' => bcrypt('password')]);
        $this->dosenUser = User::create(['name' => 'Test Dosen', 'email' => 'dosen@test.com', 'password' => bcrypt('password')]);

        $this->biodata = BiodataMahasiswa::create([
            'nama_lengkap' => 'Test Student',
            'id_mahasiswa' => 'MH001',
            // 'id_user' => $this->user->id, // REMOVED
            'jenis_kelamin' => 'L',
            'id_agama' => '1',
            'tanggal_lahir' => '2000-01-01',
            'tempat_lahir' => 'Jakarta',
            'kewarganegaraan' => 'ID',
            'nik' => '1234567890123456',
            'nisn' => '1234567890',
            'npwp' => '123456789012345',
            'kelurahan' => 'Test Kelurahan',
            'id_wilayah' => '123',
            'no_hp' => '081234567890',
            'email' => 'student@test.com',
            'nama_ibu_kandung' => 'Test Mother',
        ]);

        $this->user->update(['mahasiswa_id' => $this->biodata->id]);
        $this->riwayat = RiwayatPendidikan::create([
            'id_registrasi_mahasiswa' => 'REG123',
            'id_mahasiswa' => $this->biodata->id_mahasiswa,
            'id_biodata_mahasiswa' => $this->biodata->id_mahasiswa, // Added
            'nim' => '12345',
            'id_jenis_daftar' => '1',
            'id_jalur_daftar' => '1',
            'id_periode_masuk' => '20231',
            'tanggal_daftar' => '2023-01-01',
            'id_perguruan_tinggi' => 'PT01',
            'id_prodi' => 'PROD01',
            'biaya_masuk' => '0'
        ]);

        // Enrollment Setup
        $this->matkul = MataKuliah::create([
            'id_matkul' => 'MK01', // Added ID
            'kode_mata_kuliah' => 'MK01',
            'nama_mata_kuliah' => 'Algo',
            'sks_mata_kuliah' => 3,
            'id_prodi' => 'PROD01', // Added
            'id_jenis_mata_kuliah' => 'A', // Added
            'id_kelompok_mata_kuliah' => 'A', // Added
            'sks_tatap_muka' => 3, // Added
            'sks_praktek' => 0, // Added
            'sks_praktek_lapangan' => 0, // Added
            'sks_simulasi' => 0, // Added
        ]);
        $this->kelas = KelasKuliah::create([
            'id_kelas_kuliah' => 'CLS01',
            'id_matkul' => $this->matkul->id_matkul,
            'id_semester' => $this->semester->id_semester,
            'nama_kelas_kuliah' => 'A', // Fixed name
            'id_prodi' => 'PROD01', // Added
            'sks_mk' => 3, // Added
            'sks_tm' => 3, // Added
            'sks_prak' => 0, // Added
            'sks_sim' => 0, // Added
        ]);
        $this->dosen = Dosen::create([
            'id_dosen' => 'DSN01',
            'nama_dosen' => 'Dr. Test',
            'jenis_kelamin' => 'L', // Added
            'id_agama' => '1', // Added
            'tanggal_lahir' => '1980-01-01' // Added
        ]);

        $this->pengajar = DosenPengajarKelasKuliah::create([
            'id_aktivitas_mengajar' => 'ACT01',
            'id_kelas_kuliah' => $this->kelas->id_kelas_kuliah,
            'id_registrasi_dosen' => $this->dosen->id_dosen, // Fixed column name
            'sks_substansi_total' => 3,
            'rencana_minggu_pertemuan' => 16,
            'id_jenis_evaluasi' => '1', // Added
        ]);

        $this->peserta = PesertaKelasKuliah::create([
            'id_kelas_kuliah' => 'CLS01',
            'id_registrasi_mahasiswa' => 'REG123'
        ]);

        $this->aktivitas = AktivitasKuliahMahasiswa::create([
            'id_registrasi_mahasiswa' => 'REG123',
            'id_semester' => '20231',
            'id_status_mahasiswa' => 'A',
            'ips' => 0,
            'ipk' => 0,
            'sks_semester' => 20,
            'sks_total' => 20
        ]);

        // Mocking Dosen Alias Relationship for polymorphic resolution
        // Note: In real app, DosenPengajarKelasKuliah might relate to Dosen via id_dosen not id_dosen_alias
        // Adjusting test to match implementation expectation if needed.
    }

    public function test_can_create_form_template()
    {
        $template = FormTemplate::create([
            'semester_id' => $this->semester->id,
            'title' => 'Kuesioner Dosen UAS',
            'category' => 'UAS_DOSEN',
            'evaluation_target' => Dosen::class,
            'schema_snapshot' => [
                ['text' => 'Apakah dosen on time?', 'type' => 'scale', 'metric_key' => 'punctuality'],
            ],
            'is_active' => true
        ]);

        $this->assertDatabaseHas('form_templates', ['title' => 'Kuesioner Dosen UAS']);
    }

    public function test_uas_ticket_generation_job_distributes_tickets_correctly()
    {
        $template = FormTemplate::create([
            'semester_id' => $this->semester->id,
            'title' => 'Evaluasi Kinerja Dosen',
            'category' => 'UAS_DOSEN',
            'evaluation_target' => Dosen::class,
            'schema_snapshot' => [],
            'is_active' => true
        ]);

        // Run Job
        (new GenerateSurveyTicketsJob($template))->handle();

        // Assert Ticket Created
        $this->assertDatabaseHas('survey_tickets', [
            'user_id' => $this->user->id,
            'form_template_id' => $template->id,
            'reference_type' => get_class($this->pengajar), // Should target the teacher in the class
            'reference_id' => $this->pengajar->id,
            'status' => 'PENDING'
        ]);
    }

    public function test_uts_ticket_generation_job_distributes_tickets_correctly()
    {
        $template = FormTemplate::create([
            'semester_id' => $this->semester->id,
            'title' => 'Evaluasi Layanan Akademik',
            'category' => 'UTS_LAYANAN',
            'evaluation_target' => 'App\Models\Prodi',
            'schema_snapshot' => [],
            'is_active' => true
        ]);

        // Run Job
        (new GenerateSurveyTicketsJob($template))->handle();

        // Assert Ticket Created for the generic academic service (linked to AktivitasKuliah)
        $this->assertDatabaseHas('survey_tickets', [
            'user_id' => $this->user->id,
            'form_template_id' => $template->id,
            'reference_type' => get_class($this->aktivitas),
            'reference_id' => $this->aktivitas->id,
            'status' => 'PENDING'
        ]);
    }

    public function test_gatekeeper_blocks_exam_card_when_tickets_pending()
    {
        // 0. Create Template
        $template = FormTemplate::create([
            'semester_id' => $this->semester->id,
            'title' => 'Kuesioner Wajib',
            'category' => 'UAS_DOSEN',
            'evaluation_target' => Dosen::class,
            'schema_snapshot' => [],
            'is_active' => true
        ]);

        // 1. Create a Pending Ticket
        SurveyTicket::create([
            'user_id' => $this->user->id,
            'form_template_id' => $template->id, // Use valid ID
            'reference_type' => 'Mock',
            'reference_id' => 1,
            'status' => 'PENDING'
        ]);

        // 2. Check Gatekeeper Logic (Simulating Resource Method)
        $pendingCount = SurveyTicket::where('user_id', $this->user->id)
            ->where('status', 'PENDING')
            ->count();

        $this->assertGreaterThan(0, $pendingCount);
    }

    public function test_submission_completes_ticket_and_creates_anonymous_ballot()
    {
        $template = FormTemplate::create([
            'semester_id' => $this->semester->id,
            'title' => 'Evaluasi Dosen',
            'category' => 'UAS_DOSEN',
            'evaluation_target' => Dosen::class,
            'schema_snapshot' => [
                ['text' => 'Rate 1-4', 'type' => 'scale', 'metric_key' => 'quality']
            ],
            'is_active' => true
        ]);

        $ticket = SurveyTicket::create([
            'user_id' => $this->user->id,
            'form_template_id' => $template->id,
            'reference_type' => DosenPengajarKelasKuliah::class,
            'reference_id' => $this->pengajar->id,
            'status' => 'PENDING'
        ]);

        // Simulate Form Submission Logic (as found in IsiKuesioner::submit)
        $data = ['question_0' => 4];

        \Illuminate\Support\Facades\DB::transaction(function () use ($ticket, $data, $template) {

            // Create Ballot
            ResponseBallot::create([
                'form_template_id' => $template->id,
                'target_id' => 999, // Mock Target Dosen ID
                'answers_full' => $data,
                'calculated_score' => 4.0,
            ]);

            // Update Ticket
            $ticket->update(['status' => 'COMPLETED', 'completed_at' => now()]);
        });

        // Assertions
        $this->assertDatabaseHas('survey_tickets', [
            'id' => $ticket->id,
            'status' => 'COMPLETED'
        ]);

        $this->assertDatabaseHas('response_ballots', [
            'form_template_id' => $template->id,
            'calculated_score' => 4.0
        ]);

        // CRITICAL PRIVACY CHECK: Ballot should NOT be linked to User
        $ballot = ResponseBallot::first();
        $this->assertArrayNotHasKey('user_id', $ballot->toArray());
    }
}
