<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Fee Components (Master Data)
        if (!Schema::hasTable('fee_components')) {
            Schema::create('fee_components', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // SPP Tetap, SPP SKS
                $table->enum('type', ['RECURRING', 'ONE_TIME'])->default('RECURRING');
                $table->timestamps();
            });
        }

        // 2. Fee Structures (The Matrix)
        if (!Schema::hasTable('fee_structures')) {
            Schema::create('fee_structures', function (Blueprint $table) {
                $table->id();
                $table->year('angkatan');
                $table->uuid('prodi_id')->nullable(); // Nullable for 'All Prodi' fees
                $table->enum('waktu_kuliah_enum', ['pagi', 'sore'])->nullable(); // Changed to ENUM to match system
                $table->foreignId('fee_component_id')->constrained('fee_components')->cascadeOnDelete();
                $table->decimal('amount', 14, 2);
                $table->timestamps();

                $table->index(['angkatan', 'prodi_id', 'waktu_kuliah_enum'], 'fee_matrix_index');
            });
        }

        // 3. Student Fee Assignments (Snapshots)
        if (!Schema::hasTable('student_fee_assignments')) {
            Schema::create('student_fee_assignments', function (Blueprint $table) {
                $table->id();
                $table->uuid('id_registrasi_mahasiswa'); // Matches your biodata_mahasiswas FK style used elsewhere
                $table->foreignId('fee_structure_id')->constrained('fee_structures');
                $table->date('valid_from');
                $table->date('valid_to')->nullable(); // Null = Forever
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('id_registrasi_mahasiswa');
            });
        }

        // 4. Financial Invoices (Using UUID for Payment Gateway readiness)
        if (!Schema::hasTable('financial_invoices')) {
            Schema::create('financial_invoices', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('invoice_number')->unique(); // INV/2024/01/001
                $table->uuid('id_registrasi_mahasiswa');
                $table->date('period_date'); // 2024-01-01
                $table->date('due_date');
                $table->decimal('total_amount', 14, 2);
                $table->enum('status', ['UNPAID', 'PAID'])->default('UNPAID');
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('generated_at')->useCurrent();
                $table->timestamps();

                $table->index(['id_registrasi_mahasiswa', 'status', 'due_date'], 'gatekeeper_index');
            });
        }

        // 5. Invoice Items
        if (!Schema::hasTable('financial_invoice_items')) {
            Schema::create('financial_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('financial_invoice_id')->constrained('financial_invoices')->cascadeOnDelete();
                $table->string('component_name'); // Snapshot Name "SPP Tetap"
                $table->decimal('amount', 14, 2);
                $table->timestamps();
            });
        }

        // 6. Payments
        if (!Schema::hasTable('financial_payments')) {
            Schema::create('financial_payments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('payment_number')->unique();
                $table->enum('payment_method', ['MANUAL_TRANSFER', 'VIRTUAL_ACCOUNT', 'CASH', 'SCHOLARSHIP', 'WAIVER']);
                $table->string('proof_file_path')->nullable(); // For manual
                $table->string('proof_file_hash')->nullable()->index(); // Duplicate detection
                $table->enum('status', ['PENDING', 'VERIFIED', 'REJECTED'])->default('PENDING');
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users'); // Admin
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 7. Payment - Invoice Pivot (Direct Settlement)
        if (!Schema::hasTable('financial_payment_invoice')) {
            Schema::create('financial_payment_invoice', function (Blueprint $table) {
                $table->foreignUuid('payment_id')->constrained('financial_payments')->cascadeOnDelete();
                $table->foreignUuid('invoice_id')->constrained('financial_invoices')->cascadeOnDelete();
                $table->decimal('amount_allocated', 14, 2); // How much of this payment went to this invoice
                $table->primary(['payment_id', 'invoice_id']);
            });
        }

        // 8. Exam Dispensations (Exceptions)
        if (!Schema::hasTable('exam_dispensations')) {
            Schema::create('exam_dispensations', function (Blueprint $table) {
                $table->id();
                $table->uuid('id_registrasi_mahasiswa');
                $table->enum('type', ['UTS', 'UAS', 'KRS']);
                $table->date('valid_until');
                $table->text('reason');
                $table->foreignId('approved_by')->constrained('users');
                $table->timestamps();

                $table->index(['id_registrasi_mahasiswa', 'type', 'valid_until'], 'active_dispensation_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_dispensations');
        Schema::dropIfExists('financial_payment_invoice');
        Schema::dropIfExists('financial_payments');
        Schema::dropIfExists('financial_invoice_items');
        Schema::dropIfExists('financial_invoices');
        Schema::dropIfExists('student_fee_assignments');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_components');
        // Schema::dropIfExists('finance_module_tables'); // Original table name
    }
};
