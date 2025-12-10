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
        Schema::create('skala_nilais', function (Blueprint $table) {
            $table->id();
            $table->string('id_prodi');
            $table->string('id_bobot_nilai');
            $table->string('nilai_huruf');
            $table->string('nilai_indeks');
            $table->string('bobot_nilai_min');
            $table->string('bobot_nilai_maks');
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
        Schema::dropIfExists('skala_nilais');
    }
};
