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
        Schema::create('pegawais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nip')->unique(); // Nomor Induk Pegawai
            $table->string('nama_lengkap');
            $table->string('email')->unique()->nullable();
            $table->string('no_hp')->nullable();
            $table->string('alamat')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('jabatan_fungsional')->nullable(); // Staff, Kabag, dll
            $table->string('unit_kerja')->nullable(); // Keuangan, BAUK, dll
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
