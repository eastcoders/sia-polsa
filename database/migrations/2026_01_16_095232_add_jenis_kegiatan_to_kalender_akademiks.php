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
        Schema::table('kalender_akademiks', function (Blueprint $table) {
            // Jenis kegiatan untuk Auto-Switch Logic
            // Values: MINGGU_UTS, MINGGU_UAS, PERKULIAHAN, LIBUR_SEMESTER, etc.
            if (!Schema::hasColumn('kalender_akademiks', 'jenis_kegiatan')) {
                $table->string('jenis_kegiatan')->nullable()->after('keterangan');
            }

            // Index untuk query cepat berdasarkan jenis dan tanggal
            $table->index(['jenis_kegiatan', 'tanggal_mulai', 'tanggal_selesai'], 'kalender_kegiatan_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kalender_akademiks', function (Blueprint $table) {
            $table->dropIndex('kalender_kegiatan_idx');
            $table->dropColumn('jenis_kegiatan');
        });
    }
};
