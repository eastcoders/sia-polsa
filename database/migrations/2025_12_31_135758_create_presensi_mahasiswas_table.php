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
        Schema::create('presensi_mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pertemuan_kelas')->constrained('pertemuan_kelas')->cascadeOnDelete();
            $table->string('id_registrasi_mahasiswa'); // FK to registration/student
            $table->enum('status_kehadiran', ['hadir', 'sakit', 'izin', 'alpha'])->default('alpha');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Prevent duplicate attendance for same student in same meeting
            $table->unique(['id_pertemuan_kelas', 'id_registrasi_mahasiswa'], 'unique_presensi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_mahasiswas');
    }
};
