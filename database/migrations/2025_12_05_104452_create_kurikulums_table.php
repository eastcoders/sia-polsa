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
        Schema::create('kurikulums', function (Blueprint $table) {
            $table->id();
            $table->string('id_kurikulum');
            $table->string('nama_kurikulum');
            $table->string('id_prodi');
            $table->string('id_semester');
            $table->string('jumlah_sks_lulus');
            $table->string('jumlah_sks_wajib');
            $table->string('jumlah_sks_pilihan');
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurikulums');
    }
};
