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
        Schema::create('jadwal_perkuliahans', function (Blueprint $table) {
            $table->id();
            $table->string('id_kelas_kuliah');
            $table->string('id_ruang');
            $table->string('id_semester');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('hari');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_perkuliahans');
    }
};
