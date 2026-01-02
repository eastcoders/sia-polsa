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
        Schema::table('biodata_mahasiswas', function (Blueprint $table) {
            // Index for name search
            $table->index('nama_lengkap', 'idx_bmahasiswa_nama_lengkap');
        });

        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            // Index for NIM search (critical for join and search)
            $table->index('nim', 'idx_rpendidikan_nim');

            // Index for Foreign Keys used in filters/joins
            $table->index('id_prodi', 'idx_rpendidikan_id_prodi');
            $table->index('id_periode_masuk', 'idx_rpendidikan_id_periode_masuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biodata_mahasiswas', function (Blueprint $table) {
            $table->dropIndex('idx_bmahasiswa_nama_lengkap');
        });

        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->dropIndex('idx_rpendidikan_nim');
            $table->dropIndex('idx_rpendidikan_id_prodi');
            $table->dropIndex('idx_rpendidikan_id_periode_masuk');
        });
    }
};
