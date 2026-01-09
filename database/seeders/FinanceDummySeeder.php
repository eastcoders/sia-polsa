<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Finance\FeeComponent;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\FinancialInvoice;
use App\Models\Finance\FinancialInvoiceItem;
use App\Models\Finance\FinancialPayment;
use App\Models\Prodi;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\DB;

class FinanceDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Create Fee Components
            $components = [
                ['name' => 'SPP Tetap', 'type' => 'RECURRING'],
                ['name' => 'SPP SKS', 'type' => 'RECURRING'],
                ['name' => 'Uang Kemahasiswaan', 'type' => 'ONE_TIME'],
                ['name' => 'Biaya Praktikum', 'type' => 'RECURRING'],
            ];

            foreach ($components as $compData) {
                FeeComponent::firstOrCreate($compData);
            }

            // 2. Create Fee Structures (Matrix)
            // Assuming we have some Prodis, pick first one or create dummy
            $prodi = Prodi::first();
            if (!$prodi) {
                // Should hopefully exist, otherwise skip specific structure
                $this->command->info('No Prodi found, skipping specific Prodi Fee Structures.');
            }

            $sppTetap = FeeComponent::where('name', 'SPP Tetap')->first();
            $sppSks = FeeComponent::where('name', 'SPP SKS')->first();

            if ($prodi) {
                // Structure for Pagi
                FeeStructure::create([
                    'angkatan' => 2024,
                    'prodi_id' => $prodi->id,
                    'waktu_kuliah_enum' => 'pagi',
                    'fee_component_id' => $sppTetap->id,
                    'amount' => 1500000,
                ]);

                // Structure for Sore (Usually more expensive)
                FeeStructure::create([
                    'angkatan' => 2024,
                    'prodi_id' => $prodi->id,
                    'waktu_kuliah_enum' => 'sore',
                    'fee_component_id' => $sppTetap->id,
                    'amount' => 1750000,
                ]);
            }

            // 3. Create Dummy Invoices & Assignments if Students exist
            $student = RiwayatPendidikan::first();
            if ($student) {
                // Use id_registrasi_mahasiswa if available, fallback to id (usually UUID in this system based on patterns)
                $studentId = $student->id_registrasi_mahasiswa ?? $student->id;

                // Create an UNPAID Invoice
                $invoice = FinancialInvoice::create([
                    'invoice_number' => 'INV/' . date('Y/m/') . '0001',
                    'id_registrasi_mahasiswa' => $studentId,
                    'period_date' => now()->startOfMonth(),
                    'due_date' => now()->addDays(7),
                    'total_amount' => 1750000, // Will be updated by items
                    'status' => 'UNPAID',
                    'generated_at' => now(),
                ]);

                FinancialInvoiceItem::create([
                    'financial_invoice_id' => $invoice->id,
                    'component_name' => 'SPP Tetap Januari 2025',
                    'amount' => 1750000,
                ]);

                // Create a PAID Invoice (Historical)
                $oldInvoice = FinancialInvoice::create([
                    'invoice_number' => 'INV/' . date('Y/m', strtotime('-1 month')) . '/0001',
                    'id_registrasi_mahasiswa' => $student->id,
                    'period_date' => now()->subMonth()->startOfMonth(),
                    'due_date' => now()->subMonth()->addDays(7),
                    'total_amount' => 1750000,
                    'status' => 'PAID',
                    'paid_at' => now()->subMonth()->addDays(5),
                    'generated_at' => now()->subMonth(),
                ]);

                FinancialInvoiceItem::create([
                    'financial_invoice_id' => $oldInvoice->id,
                    'component_name' => 'SPP Tetap Desember 2024',
                    'amount' => 1750000,
                ]);

                // 4. Create Dummy Payment for the PAID invoice
                $payment = FinancialPayment::create([
                    'payment_number' => 'PAY/' . date('Y/m') . '/0001',
                    'payment_method' => 'MANUAL_TRANSFER',
                    'status' => 'VERIFIED',
                    'verified_at' => now()->subMonth()->addDays(5),
                    'verified_by' => 1, // Assuming admin user ID 1 exists
                ]);

                // Attach payment to invoice
                $oldInvoice->payments()->attach($payment->id, ['amount_allocated' => 1750000]);
            } else {
                $this->command->info('No RegistrasiMahasiswa found, skipping Invoices/Payments generation.');
            }
        });
    }
}
