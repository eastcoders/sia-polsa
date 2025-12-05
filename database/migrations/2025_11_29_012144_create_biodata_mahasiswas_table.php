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
        Schema::create('biodata_mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('id_mahasiswa')->index();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('id_agama');
            $table->date('tanggal_lahir');
            $table->string('tempat_lahir');

            // Alamat
            $table->string('kewarganegaraan');
            $table->string('nik')->unique();
            $table->string('nisn')->unique();
            $table->string('npwp')->unique();
            $table->string('kelurahan');
            $table->string('dusun')->nullable();
            $table->string('jalan')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('id_wilayah');
            $table->string('telepone')->nullable()->unique();
            $table->string('no_hp')->unique();
            $table->string('email')->unique();
            $table->enum('penerima_kps', ['0', '1'])->default(0);
            $table->string('no_kps')->nullable();
            $table->string('id_jenis_tinggal')->nullable();
            $table->string('id_alat_transportasi')->nullable();

            // Orang Tua
            $table->string('nama_ibu_kandung');
            $table->date('tanggal_lahir_ibu')->nullable();
            $table->string('nik_ibu')->nullable();
            $table->string('nama_ayah')->nullable();
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->string('nik_ayah')->nullable();

            // Pekerjaan Orang Tua
            $table->string('id_pekerjaan_ayah')->nullable();
            $table->string('id_pekerjaan_ibu')->nullable();
            $table->string('id_penghasilan_ayah')->nullable();
            $table->string('id_penghasilan_ibu')->nullable();
            $table->string('id_pendidikan_ayah')->nullable();
            $table->string('id_pendidikan_ibu')->nullable();

            // Wali
            $table->string('nama_wali')->nullable();
            $table->string('nik_wali')->nullable();
            $table->string('id_pekerjaan_wali')->nullable();
            $table->string('id_penghasilan_wali')->nullable();
            $table->string('id_pendidikan_wali')->nullable();

            // Kebutuhan Khusus
            $table->string('id_kebutuhan_khusus_mahasiswa')->default(0)->nullable();
            $table->string('id_kebutuhan_khusus_ibu')->default(0)->nullable();
            $table->string('id_kebutuhan_khusus_ayah')->default(0)->nullable();

            $table->dateTime('sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biodata_mahasiswas');
    }
};
