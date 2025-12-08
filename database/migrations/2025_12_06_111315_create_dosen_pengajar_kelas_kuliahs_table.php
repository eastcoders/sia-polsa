<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dosen_pengajar_kelas_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->string('id_aktivitas_mengajar');
            $table->string('id_registrasi_dosen');
            $table->string('id_kelas_kuliah');
            $table->string('id_substansi')->nullable();
            $table->string('sks_substansi_total');
            $table->string('rencana_minggu_pertemuan');
            $table->string('realisasi_minggu_pertemuan')->nullable();
            $table->string('id_jenis_evaluasi');
            $table->enum('punya_alias', [0, 1])->default(0);
            $table->string('id_dosen_alias')->nullable();
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_pengajar_kelas_kuliahs');
    }
};
