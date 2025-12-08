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
        Schema::create('kelas_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->string('id_kelas_kuliah');
            $table->string('id_prodi');
            $table->string('id_semester');
            $table->string('nama_kelas_kuliah');
            $table->string('sks_mk');
            $table->string('sks_tm');
            $table->string('sks_prak');
            $table->string('sks_sim');
            $table->string('bahasan')->nullable();
            $table->string('a_selenggara_pditt')->nullable();
            $table->string('apa_untuk_pditt')->nullable();
            $table->string('kapasitas')->nullable();
            $table->date('tanggal_mulai_efektif')->nullable();
            $table->date('tanggal_akhir_efektif')->nullable();
            $table->string('id_mou')->nullable();
            $table->string('id_matkul');
            $table->enum('lingkup', ['1', '2', '3'])->nullable();
            $table->enum('mode', ['O', 'F', 'M'])->nullable();
            $table->timestamp('sync_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_kuliahs');
    }
};
