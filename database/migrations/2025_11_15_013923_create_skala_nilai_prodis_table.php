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
        Schema::create('skala_nilai_prodis', function (Blueprint $table) {
            $table->id();
            $table->string('id_skala_nilai');
            $table->string('id_prodi');
            $table->string('nilai_huruf');
            $table->string('nilai_indeks')->nullable();
            $table->string('bobot_minimum');
            $table->string('bobot_maksimum');
            $table->date('tanggal_mulai_efektif');
            $table->date('tanggal_akhir_efektif');
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skala_nilai_prodis');
    }
};
