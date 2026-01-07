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
        // 1. Drop columns from riwayat_pendidikans
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            if (Schema::hasColumn('riwayat_pendidikans', 'khs_is_approved')) {
                $table->dropForeign(['khs_approved_by']);
                $table->dropColumn(['khs_is_approved', 'khs_approved_at', 'khs_approved_by']);
            }
        });

        // 2. Add columns to aktivitas_kuliah_mahasiswas
        Schema::table('aktivitas_kuliah_mahasiswas', function (Blueprint $table) {
            $table->boolean('khs_is_approved')->default(false)->after('ips');
            $table->timestamp('khs_approved_at')->nullable()->after('khs_is_approved');
            $table->foreignId('khs_approved_by')->nullable()->constrained('users')->nullOnDelete()->after('khs_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop columns from aktivitas_kuliah_mahasiswas
        Schema::table('aktivitas_kuliah_mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['khs_approved_by']);
            $table->dropColumn(['khs_is_approved', 'khs_approved_at', 'khs_approved_by']);
        });

        // 2. Add columns back to riwayat_pendidikans
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->boolean('khs_is_approved')->default(false);
            $table->timestamp('khs_approved_at')->nullable();
            $table->foreignId('khs_approved_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
