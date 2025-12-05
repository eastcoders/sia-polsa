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
        Schema::create('dosens', function (Blueprint $table) {
            $table->id();
            $table->string('id_dosen');
            $table->string('nama_dosen');
            $table->string('nidn')->nullable();
            $table->string('nip')->nullable();
            $table->string('jenis_kelamin');
            $table->string('id_agama');
            $table->string('tanggal_lahir');
            $table->string('id_status_aktif')->nullable();
            $table->string('nama_status_aktif')->nullable();
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosens');
    }
};
