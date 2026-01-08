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
        if (!Schema::hasColumn('riwayat_pendidikans', 'pilihan_waktu')) {
            Schema::table('riwayat_pendidikans', function (Blueprint $table) {
                $table->enum('pilihan_waktu', ['pagi', 'sore'])->nullable()->after('nim')->comment('Global override for schedule preference');
            });
        }

        if (!Schema::hasColumn('peserta_kelas_kuliahs', 'pilihan_waktu')) {
            Schema::table('peserta_kelas_kuliahs', function (Blueprint $table) {
                $table->enum('pilihan_waktu', ['pagi', 'sore'])->nullable()->after('id_registrasi_mahasiswa')->comment('Specific override for this class enrollment');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->dropColumn('pilihan_waktu');
        });

        Schema::table('peserta_kelas_kuliahs', function (Blueprint $table) {
            $table->dropColumn('pilihan_waktu');
        });
    }
};
