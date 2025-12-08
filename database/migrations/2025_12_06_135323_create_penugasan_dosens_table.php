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
        Schema::create('penugasan_dosens', function (Blueprint $table) {
            $table->id();
            $table->string('id_registrasi_dosen');
            $table->string('id_dosen');
            $table->string('id_tahun_ajaran')->nullable();
            $table->string('id_prodi')->nullable();
            $table->string('id_perguruan_tinggi');
            $table->string('nomor_surat_tugas')->nullable();
            $table->date('tanggal_surat_tugas')->nullable();
            $table->date('mulai_surat_tugas')->nullable();
            $table->timestamp('sync_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penugasan_dosens');
    }
};
