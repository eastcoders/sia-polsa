<?php

namespace Tests\Feature;

use App\Models\KalenderAkademik;
use App\Models\KelasKuliah;
use App\Models\PertemuanKelas;
use App\Services\ClassScheduleValidationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassScheduleValidationTest extends TestCase
{
    use RefreshDatabase;


    public function test_validate_schedule_date_detects_holidays()
    {
        // Setup: Create a holiday
        $holidayDate = Carbon::now()->addDays(5)->startOfDay();
        KalenderAkademik::create([
            'tanggal_mulai' => $holidayDate,
            'tanggal_selesai' => $holidayDate,
            'keterangan' => 'Libur Nasional Test',
            'is_libur' => true,
        ]);

        $kelas = KelasKuliah::create([ // Minimal required fields for KelasKuliah
            'id_kelas_kuliah' => 'CLS001',
            'id_prodi' => 'TI',
            'id_semester' => '20231',
            'nama_kelas_kuliah' => 'Pemrograman Web',
            'sks_mk' => 3,
            'sks_tm' => 3,
            'sks_prak' => 0,
            'sks_sim' => 0,
            'id_matkul' => 'MK001',
            'tanggal_mulai_efektif' => Carbon::now()->startOfDay(),
            'tanggal_akhir_efektif' => Carbon::now()->addMonths(4)->endOfDay(),
        ]);

        $service = new ClassScheduleValidationService();
        $result = $service->validateScheduleDate($holidayDate->toDateString(), $kelas->id);

        $this->assertEquals('warning', $result['status']);
        $this->assertStringContainsString('Libur Nasional Test', $result['message']);
    }

    public function test_validate_schedule_date_warns_outside_effective_range()
    {
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addMonths(4)->endOfDay();

        $kelas = KelasKuliah::create([
            'id_kelas_kuliah' => 'CLS002',
            'id_prodi' => 'TI',
            'id_semester' => '20231',
            'nama_kelas_kuliah' => 'Algoritma',
            'sks_mk' => 3,
            'sks_tm' => 3,
            'sks_prak' => 0,
            'sks_sim' => 0,
            'id_matkul' => 'MK002',
            'tanggal_mulai_efektif' => $startDate,
            'tanggal_akhir_efektif' => $endDate,
        ]);

        $service = new ClassScheduleValidationService();

        // Test Date Before Start
        $resultBefore = $service->validateScheduleDate($startDate->copy()->subDay()->toDateString(), $kelas->id);
        $this->assertEquals('warning', $resultBefore['status']);
        $this->assertStringContainsString('sebelum Tanggal Mulai', $resultBefore['message']);

        // Test Date After End
        $resultAfter = $service->validateScheduleDate($endDate->copy()->addDay()->toDateString(), $kelas->id);
        $this->assertEquals('warning', $resultAfter['status']);
        $this->assertStringContainsString('melewati Tanggal Akhir', $resultAfter['message']);
    }

    public function test_valid_schedule_date_returns_valid_status()
    {
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addMonths(4)->endOfDay();

        $kelas = KelasKuliah::create([
            'id_kelas_kuliah' => 'CLS003',
            'id_prodi' => 'TI',
            'id_semester' => '20231',
            'nama_kelas_kuliah' => 'Basis Data',
            'sks_mk' => 3,
            'sks_tm' => 3,
            'sks_prak' => 0,
            'sks_sim' => 0,
            'id_matkul' => 'MK003',
            'tanggal_mulai_efektif' => $startDate,
            'tanggal_akhir_efektif' => $endDate,
        ]);

        $service = new ClassScheduleValidationService();
        $result = $service->validateScheduleDate($startDate->copy()->addDays(10)->toDateString(), $kelas->id);

        $this->assertEquals('valid', $result['status']);
    }

    public function test_can_administer_exam_blocks_uts_if_meetings_low()
    {
        $kelas = KelasKuliah::create([
            'id_kelas_kuliah' => 'CLS004',
            'id_prodi' => 'TI',
            'id_semester' => '20231',
            'nama_kelas_kuliah' => 'Jaringan Komputer',
            'sks_mk' => 3,
            'sks_tm' => 3,
            'sks_prak' => 0,
            'sks_sim' => 0,
            'id_matkul' => 'MK004',
        ]);

        // Create only 4 finished meetings
        for ($i = 1; $i <= 4; $i++) {
            PertemuanKelas::create([
                'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
                'tanggal' => Carbon::now()->subDays(10 - $i),
                'pertemuan_ke' => $i,
                'status_pertemuan' => 'selesai',
            ]);
        }

        $service = new ClassScheduleValidationService();
        $result = $service->canAdministerExam($kelas->id, 'UTS');

        $this->assertFalse($result['can_proceed']);
        $this->assertStringContainsString('belum terpenuhi', $result['message']);
    }

    public function test_can_administer_exam_allows_uts_if_meetings_sufficient()
    {
        $kelas = KelasKuliah::create([
            'id_kelas_kuliah' => 'CLS005',
            'id_prodi' => 'TI',
            'id_semester' => '20231',
            'nama_kelas_kuliah' => 'Keamanan Siber',
            'sks_mk' => 3,
            'sks_tm' => 3,
            'sks_prak' => 0,
            'sks_sim' => 0,
            'id_matkul' => 'MK005',
        ]);

        // Create 7 finished meetings
        for ($i = 1; $i <= 7; $i++) {
            PertemuanKelas::create([
                'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
                'tanggal' => Carbon::now()->subDays(10 - $i),
                'pertemuan_ke' => $i,
                'status_pertemuan' => 'selesai',
            ]);
        }

        $service = new ClassScheduleValidationService();
        $result = $service->canAdministerExam($kelas->id, 'UTS');

        $this->assertTrue($result['can_proceed']);
    }
}
