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
        Schema::create('aktivitas_kuliah_mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('id_registrasi_mahasiswa')->index();
            $table->string('id_semester')->index();
            $table->string('id_status_mahasiswa')->default('A'); // A: Aktif, C: Cuti, N: Non-Aktif, dll
            $table->decimal('ips', 4, 2)->nullable();
            $table->decimal('ipk', 4, 2)->nullable();
            $table->integer('sks_semester')->nullable();
            $table->integer('sks_total')->nullable();
            $table->decimal('biaya_kuliah_smt', 15, 2)->nullable();
            $table->uuid('id_server')->nullable()->unique();
            $table->timestamps();

            // Optional: Foreign keys if referencing local tables
            // $table->foreign('id_registrasi_mahasiswa')->references('id_registrasi_mahasiswa')->on('riwayat_pendidikans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas_kuliah_mahasiswas');
    }
};
