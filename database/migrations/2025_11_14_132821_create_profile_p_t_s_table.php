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
        Schema::create('profile_p_t_s', function (Blueprint $table) {
            $table->id();
            $table->string('id_perguruan_tinggi');
            $table->string('kode_perguruan_tinggi');
            $table->string('nama_perguruan_tinggi');
            $table->string('telepon');
            $table->string('faximile');
            $table->string('email');
            $table->string('website');
            $table->string('jalan');
            $table->string('dusun');
            $table->string('rt_rw');
            $table->string('kelurahan');
            $table->string('kode_pos');
            $table->string('id_wilayah');
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_p_t_s');
    }
};
