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
        Schema::create('riwayat_pendidikans', function (Blueprint $table) {
            $table->id();
            $table->string('id_biodata_mahasiswa');
            $table->string('id_mahasiswa');
            $table->string('id_registrasi_mahasiswa');
            $table->string('nim')->unique();
            $table->string('id_jenis_daftar');
            $table->string('id_jalur_daftar');
            $table->string('id_periode_masuk');
            $table->date('tanggal_daftar');
            $table->string('id_perguruan_tinggi');
            $table->string('id_prodi');
            $table->string('id_bidang_minat')->nullable();
            $table->string('sks_diakui')->nullable();
            $table->string('id_perguruan_tinggi_asal')->nullable();
            $table->string('id_prodi_asal')->nullable();
            $table->string('id_pembiayaan')->nullable();
            $table->string('biaya_masuk');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pendidikans');
    }
};
