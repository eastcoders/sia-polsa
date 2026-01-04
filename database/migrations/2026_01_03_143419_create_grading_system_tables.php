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
        // 1. Konfigurasi Bobot per Kelas
        Schema::create('komponen_bobot_kelas', function (Blueprint $table) {
            $table->id();
            $table->string('id_kelas_kuliah'); // UUID from PDDikti
            $table->string('nama_komponen'); // Tugas, UTS, UAS, Presensi, dll.
            $table->double('bobot'); // 25.0, 30.5
            $table->timestamps();

            // Index for faster lookups
            $table->index('id_kelas_kuliah');
            $table->unique(['id_kelas_kuliah', 'nama_komponen'], 'unique_bobot_komponen');
        });

        // 2. Definisi Tugas pada Pertemuan
        Schema::create('tugas_pertemuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pertemuan_kelas')->constrained('pertemuan_kelas', 'id')->cascadeOnDelete();
            $table->string('judul_tugas');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Nilai Tugas (Formatif)
        Schema::create('nilai_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tugas_pertemuan')->constrained('tugas_pertemuan', 'id')->cascadeOnDelete();
            // id_registrasi_mahasiswa usually string UUID from PDDikti
            $table->string('id_registrasi_mahasiswa');
            $table->decimal('nilai', 5, 2)->default(0); // 0.00 - 100.00
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->index('id_registrasi_mahasiswa');
            $table->unique(['id_tugas_pertemuan', 'id_registrasi_mahasiswa'], 'unique_nilai_tugas_mhs');
        });

        // 4. Nilai Evaluasi Akhir (Sumatif)
        Schema::create('nilai_evaluasi_akhir', function (Blueprint $table) {
            $table->id();
            $table->string('id_kelas_kuliah'); // UUID
            $table->string('id_registrasi_mahasiswa');
            $table->string('jenis_nilai'); // UTS, UAS, Sikap, dll.
            $table->decimal('nilai', 5, 2)->default(0);
            $table->timestamps();

            $table->index('id_kelas_kuliah');
            $table->index('id_registrasi_mahasiswa');
            $table->unique(['id_kelas_kuliah', 'id_registrasi_mahasiswa', 'jenis_nilai'], 'unique_nilai_akhir_mhs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_evaluasi_akhir');
        Schema::dropIfExists('nilai_tugas');
        Schema::dropIfExists('tugas_pertemuan');
        Schema::dropIfExists('komponen_bobot_kelas');
    }
};
