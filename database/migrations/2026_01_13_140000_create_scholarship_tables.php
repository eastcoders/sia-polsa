<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Scholarships Master Table
        if (!Schema::hasTable('scholarships')) {
            Schema::create('scholarships', function (Blueprint $table) {
                $table->id();
                $table->string('name');                                    // "Beasiswa Bidikmisi", "Beasiswa Yayasan A"
                $table->string('code')->unique();                          // "BM-2024", "YA-FULL"
                $table->enum('funding_source', [
                    'GOVERNMENT',      // Pemerintah (KIP, Bidikmisi)
                    'FOUNDATION',      // Yayasan
                    'INSTITUTION',     // Internal Institusi
                    'CORPORATE',       // Perusahaan/Sponsor
                ])->default('INSTITUTION');
                $table->decimal('coverage_percentage', 5, 2)->default(100.00); // 100.00 = Full, 50.00 = Half
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);               // Masih bisa assign ke mahasiswa baru
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_active', 'funding_source'], 'scholarship_filter_index');
            });
        }

        // 2. Student Scholarships Junction Table (with Lifecycle)
        if (!Schema::hasTable('student_scholarships')) {
            Schema::create('student_scholarships', function (Blueprint $table) {
                $table->id();
                $table->uuid('id_registrasi_mahasiswa');                   // FK to riwayat_pendidikans
                $table->foreignId('scholarship_id')
                    ->constrained('scholarships')
                    ->cascadeOnDelete();
                $table->date('valid_from');                                // WAJIB - Tanggal mulai
                $table->date('valid_until')->nullable();                   // NULL = Unlimited/Sampai lulus
                $table->enum('coverage_type', [
                    'FULL_TUITION',           // Cover SPP penuh
                    'PARTIAL_TUITION',        // Cover sebagian SPP
                    'TUITION_AND_LIVING',     // Cover SPP + living cost
                ])->default('FULL_TUITION');
                $table->enum('status', [
                    'ACTIVE',                 // Sedang berjalan
                    'SUSPENDED',              // Ditangguhkan sementara
                    'REVOKED',                // Dicabut permanen
                    'EXPIRED',                // Sudah habis masa berlaku
                    'COMPLETED',              // Selesai dengan baik
                ])->default('ACTIVE');
                $table->text('notes')->nullable();
                $table->foreignId('approved_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                // Indexes for fast lookup
                $table->index('id_registrasi_mahasiswa', 'student_scholarship_student_idx');
                $table->index(['id_registrasi_mahasiswa', 'status', 'valid_from', 'valid_until'], 'active_scholarship_lookup');
            });
        }

        // 3. Add scholarship columns to financial_invoices (if table exists)
        if (Schema::hasTable('financial_invoices')) {
            Schema::table('financial_invoices', function (Blueprint $table) {
                // Check if columns don't exist before adding
                if (!Schema::hasColumn('financial_invoices', 'payment_source')) {
                    $table->enum('payment_source', [
                        'SELF_PAYMENT',       // Mahasiswa bayar sendiri
                        'SCHOLARSHIP',        // Dicover beasiswa
                        'DISPENSATION',       // Ada dispensasi khusus
                    ])->nullable()->after('status');
                }

                if (!Schema::hasColumn('financial_invoices', 'scholarship_coverage_id')) {
                    $table->foreignId('scholarship_coverage_id')
                        ->nullable()
                        ->after('payment_source')
                        ->constrained('student_scholarships')
                        ->nullOnDelete();
                }
            });
        }

        // 4. Modify financial_payments to add SCHOLARSHIP method
        // Note: MySQL doesn't support ALTER ENUM easily, so we handle this via raw SQL
        // Skip on SQLite (testing) - SQLite doesn't have ENUM type, string column accepts any value
        if (Schema::hasTable('financial_payments') && DB::connection()->getDriverName() === 'mysql') {
            // Add SCHOLARSHIP and WAIVER to payment_method enum
            \Illuminate\Support\Facades\DB::statement("
                ALTER TABLE financial_payments 
                MODIFY COLUMN payment_method ENUM('MANUAL_TRANSFER', 'VIRTUAL_ACCOUNT', 'CASH', 'SCHOLARSHIP', 'WAIVER') 
                NOT NULL DEFAULT 'MANUAL_TRANSFER'
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns from financial_invoices
        if (Schema::hasTable('financial_invoices')) {
            Schema::table('financial_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('financial_invoices', 'scholarship_coverage_id')) {
                    $table->dropForeign(['scholarship_coverage_id']);
                    $table->dropColumn('scholarship_coverage_id');
                }
                if (Schema::hasColumn('financial_invoices', 'payment_source')) {
                    $table->dropColumn('payment_source');
                }
            });
        }

        // Revert payment_method enum (optional - handle with care in production)
        // Skip on SQLite (testing)
        if (Schema::hasTable('financial_payments') && DB::connection()->getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE financial_payments 
                MODIFY COLUMN payment_method ENUM('MANUAL_TRANSFER', 'VIRTUAL_ACCOUNT', 'CASH') 
                NOT NULL DEFAULT 'MANUAL_TRANSFER'
            ");
        }

        Schema::dropIfExists('student_scholarships');
        Schema::dropIfExists('scholarships');
    }
};
