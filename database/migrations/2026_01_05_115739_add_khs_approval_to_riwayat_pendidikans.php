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
            $table->boolean('khs_is_approved')->default(false);
            $table->timestamp('khs_approved_at')->nullable();
            $table->foreignId('khs_approved_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->dropForeign(['khs_approved_by']);
            $table->dropColumn(['khs_is_approved', 'khs_approved_at', 'khs_approved_by']);
        });
    }
};
