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
        Schema::create('pertemuan_kelas', function (Blueprint $table) {
            $table->id();
            $table->string('id_kelas_kuliah'); // FK to kelas_kuliahs
            $table->string('id_jadwal_perkuliahan')->nullable(); // Optional reference
            $table->date('tanggal');
            $table->integer('pertemuan_ke');
            $table->text('materi')->nullable();
            $table->enum('metode_pembelajaran', ['luring', 'daring', 'hybrid'])->default('luring');
            $table->enum('status_pertemuan', ['terjadwal', 'selesai', 'dibatalkan'])->default('terjadwal');
            $table->timestamps();

            // Indexing for performance
            $table->index(['id_kelas_kuliah', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertemuan_kelas');
    }
};
