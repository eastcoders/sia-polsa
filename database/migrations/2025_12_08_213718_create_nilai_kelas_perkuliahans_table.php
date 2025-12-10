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
        Schema::create('nilai_kelas_perkuliahans', function (Blueprint $table) {
            $table->id();
            $table->string('id_kelas_kuliah');
            $table->string('id_registrasi_mahasiswa');
            $table->integer('nilai_angka')->nullable();
            $table->integer('nilai_indeks')->nullable();
            $table->string('nilai_huruf')->nullable();
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_kelas_perkuliahans');
    }
};
