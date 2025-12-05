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
        Schema::create('bidang_minats', function (Blueprint $table) {
            $table->id();
            $table->string('id_bidang_minat');
            $table->string('nm_bidang_minat');
            $table->string('id_prodi');
            $table->string('nama_program_studi');
            $table->string('smt_dimulai');
            $table->string('tamat_sk_bidang_minat');
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bidang_minats');
    }
};
