<?php

declare(strict_types=1);

namespace App\Listeners\Finance;

use App\Events\Finance\PaymentConfirmed;
use App\Models\AktivitasKuliahMahasiswa;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener: Initialize AKM record when payment is confirmed.
 * 
 * This implements the "Hybrid Approach" from AKM Lifecycle Analysis:
 * - AKM is created at registration/payment time (not at KRS time)
 * - Default status is 'A' (Aktif) with SKS 0
 * - This prevents "Non-Aktif palsu" in PDDikti reporting
 */
class InitializeAkmOnPayment implements ShouldQueue
{
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Handle the event.
     */
    public function handle(PaymentConfirmed $event): void
    {
        $idRegistrasi = $event->idRegistrasiMahasiswa;
        $idSemester = $event->idSemester;

        // Check if AKM already exists for this student & semester
        $existingAkm = AktivitasKuliahMahasiswa::where('id_registrasi_mahasiswa', $idRegistrasi)
            ->where('id_semester', $idSemester)
            ->first();

        if ($existingAkm) {
            Log::info('[AKM INIT] AKM already exists, skipping initialization', [
                'id_registrasi' => $idRegistrasi,
                'semester' => $idSemester,
            ]);
            return;
        }

        // Create new AKM with default values (Hybrid Approach)
        $akm = AktivitasKuliahMahasiswa::create([
            'id_registrasi_mahasiswa' => $idRegistrasi,
            'id_semester' => $idSemester,
            'id_status_mahasiswa' => 'A', // Default: Aktif
            'ips' => 0.00,
            'ipk' => 0.00,
            'sks_semester' => 0,
            'sks_total' => 0,
            'biaya_kuliah_smt' => $event->payment->invoices->sum('total_amount') ?? 0,
        ]);

        Log::info('[AKM INIT] New AKM record created on payment confirmation', [
            'akm_id' => $akm->id,
            'id_registrasi' => $idRegistrasi,
            'semester' => $idSemester,
            'status' => 'A',
        ]);
    }
}
