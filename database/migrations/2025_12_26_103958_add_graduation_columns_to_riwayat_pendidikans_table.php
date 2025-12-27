<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->string('id_jenis_keluar')->nullable()->after('biaya_masuk');
            $table->date('tanggal_keluar')->nullable()->after('id_jenis_keluar');
            $table->string('keterangan_keluar')->nullable()->after('tanggal_keluar');
            $table->string('no_seri_ijazah')->nullable()->after('keterangan_keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->dropColumn([
                'id_jenis_keluar',
                'tanggal_keluar',
                'keterangan_keluar',
                'no_seri_ijazah',
            ]);
        });
    }
};
