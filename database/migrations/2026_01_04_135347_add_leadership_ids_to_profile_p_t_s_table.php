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
        Schema::table('profile_p_t_s', function (Blueprint $table) {
            $table->foreignId('direktur_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->foreignId('wadir1_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->foreignId('wadir2_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->foreignId('wadir3_id')->nullable()->constrained('dosens')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_p_t_s', function (Blueprint $table) {
            $table->dropForeign(['direktur_id']);
            $table->dropForeign(['wadir1_id']);
            $table->dropForeign(['wadir2_id']);
            $table->dropForeign(['wadir3_id']);
            $table->dropColumn(['direktur_id', 'wadir1_id', 'wadir2_id', 'wadir3_id']);
        });
    }
};
