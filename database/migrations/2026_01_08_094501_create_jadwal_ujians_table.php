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
        Schema::create('jadwal_ujians', function (Blueprint $table) {
            $table->id();
            $table->string('id_kelas_kuliah')->index();
            $table->enum('jenis_ujian', ['UTS', 'UAS']);
            $table->enum('mode_ujian', ['ONSITE', 'TAKE_HOME']);

            // On-Site Fields
            $table->date('tanggal_ujian')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->foreignId('id_ruang')->nullable()->constrained('ruang_kelas', 'id')->nullOnDelete();

            // Take-Home Fields
            $table->dateTime('deadline_submission')->nullable();
            $table->text('submission_link')->nullable(); // Optional info for students

            // Meta
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            // Indexes for frequent queries
            $table->index(['id_kelas_kuliah', 'jenis_ujian']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujians');
    }
};
