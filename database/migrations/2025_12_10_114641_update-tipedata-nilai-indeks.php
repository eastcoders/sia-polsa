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
        Schema::table('nilai_kelas_perkuliahans', function (Blueprint $table) {
            $table->decimal('nilai_angka', 5, 2)->nullable()->change();
            $table->decimal('nilai_indeks', 3, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_kelas_perkuliahans', function (Blueprint $table) {
            $table->integer('nilai_angka')->nullable()->change();
            $table->integer('nilai_indeks')->nullable()->change();
        });
    }
};
