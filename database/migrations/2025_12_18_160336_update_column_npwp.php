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

            $table->dropUnique(['nisn']);
            $table->dropUnique(['no_hp']);

            $table->string('npwp')->nullable()->change();
            $table->string('nisn')->nullable()->change();
            $table->string('nik')->nullable()->change();
            $table->string('telepone')->nullable()->change();
            $table->string('no_hp')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('id_mahasiswa')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biodata_mahasiswas', function (Blueprint $table) {
            $table->string('npwp')->nullable()->change();
        });
    }
};
