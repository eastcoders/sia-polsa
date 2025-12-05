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
        Schema::create('mata_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->string('id_matkul');
            $table->string('nama_mata_kuliah');
            $table->string('kode_mata_kuliah');
            $table->string('id_prodi');
            $table->string('id_jenis_mata_kuliah');
            $table->string('id_kelompok_mata_kuliah');
            $table->string('sks_mata_kuliah');
            $table->string('sks_tatap_muka');
            $table->string('sks_praktek');
            $table->string('sks_praktek_lapangan');
            $table->string('sks_simulasi');
            $table->string('metode_kuliah')->nullable();
            $table->enum('ada_sap', [0, 1])->nullable();
            $table->enum('ada_silabus', [0, 1])->nullable();
            $table->enum('ada_bahas_ajar', [0, 1])->nullable();
            $table->enum('ada_acara_pendek', [0, 1])->nullable();
            $table->enum('ada_diklat', [0, 1])->nullable();
            $table->date('tanggal_mulai_efektif')->nullable();
            $table->date('tanggal_akhir_efektif')->nullable();
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_kuliahs');
    }
};
