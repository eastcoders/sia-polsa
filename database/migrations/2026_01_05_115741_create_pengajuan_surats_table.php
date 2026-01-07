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
        Schema::create('pengajuan_surats', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi ke Biodata Mahasiswa (Local PK)
            $table->foreignId('biodata_mahasiswa_id')->constrained('biodata_mahasiswas')->cascadeOnDelete();

            $table->string('jenis_surat'); // 'Keterangan Aktif', 'Pengantar Magang', dll
            $table->string('nomor_surat')->nullable(); // Generated saat approved
            $table->text('keterangan'); // Alasan / Detail surat

            $table->string('status')->default('draft'); // draft, pending, approved, rejected
            $table->text('file_url')->nullable(); // Path PDF final

            // Audit Trail Approval Surat
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reject_reason')->nullable(); // Alasan penolakan

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_surats');
    }
};
