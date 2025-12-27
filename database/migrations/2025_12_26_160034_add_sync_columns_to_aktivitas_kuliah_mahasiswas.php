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
        Schema::table('aktivitas_kuliah_mahasiswas', function (Blueprint $table) {
            $table->dateTime('sync_at')->nullable();
            $table->string('sync_status')->nullable();
            $table->text('sync_message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas_kuliah_mahasiswas', function (Blueprint $table) {
            $table->dropColumn(['sync_at', 'sync_status', 'sync_message']);
        });
    }
};
