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
        $tables = [
            'biodata_mahasiswas',
            'riwayat_pendidikans',
            'mata_kuliahs',
            'kurikulums',
            'matkul_kurikulums',
            'dosen_pengajar_kelas_kuliahs',
            'peserta_kelas_kuliahs',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'biodata_mahasiswas',
            'riwayat_pendidikans',
            'mata_kuliahs',
            'kurikulums',
            'matkul_kurikulums',
            'dosen_pengajar_kelas_kuliahs',
            'peserta_kelas_kuliahs',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
