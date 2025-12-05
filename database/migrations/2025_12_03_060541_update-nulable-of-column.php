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
        Schema::table('profile_p_t_s', function (Blueprint $table) {
            $table->string('id_perguruan_tinggi')->nullable()->change();
            $table->string('kode_perguruan_tinggi')->nullable()->change();
            $table->string('nama_perguruan_tinggi')->nullable()->change();
            $table->string('telepon')->nullable()->change();
            $table->string('faximile')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('website')->nullable()->change();
            $table->string('jalan')->nullable()->change();
            $table->string('dusun')->nullable()->change();
            $table->string('rt_rw')->nullable()->change();
            $table->string('kelurahan')->nullable()->change();
            $table->string('kode_pos')->nullable()->change();
            $table->string('id_wilayah')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_p_t_s', function (Blueprint $table) {
            $table->string('id_perguruan_tinggi')->change();
            $table->string('kode_perguruan_tinggi')->change();
            $table->string('nama_perguruan_tinggi')->change();
            $table->string('telepon')->change();
            $table->string('faximile')->change();
            $table->string('email')->change();
            $table->string('website')->change();
            $table->string('jalan')->change();
            $table->string('dusun')->change();
            $table->string('rt_rw')->change();
            $table->string('kelurahan')->change();
            $table->string('kode_pos')->change();
            $table->string('id_wilayah')->change();
        });
    }
};
