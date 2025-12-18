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
        // 1. Biodata Mahasiswa
        Schema::table('biodata_mahasiswas', function (Blueprint $table) {
            $table->string('id_server')->nullable()->index()->after('id_mahasiswa')
                ->comment('ID unik dari server (UUID) setelah berhasil upload');
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'changed'])->default('pending')->index()
                ->comment('Status sinkronisasi: pending=belum, synced=sudah, failed=gagal, changed=diubah local');
            $table->text('sync_message')->nullable()
                ->comment('Pesan error atau info dari server saat terakhir sync');
        });

        // 2. Riwayat Pendidikan
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->string('id_server')->nullable()->index()->after('id_registrasi_mahasiswa')
                ->comment('ID Registrasi dari server setelah berhasil upload');
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'changed'])->default('pending')->index()
                ->comment('Status sinkronisasi local ke server');
            $table->text('sync_message')->nullable()
                ->comment('Log pesan hasil sync terakhir');
            $table->timestamp('sync_at')->nullable()
                ->comment('Waktu terakhir data berhasil disinkronkan');
        });

        // 3. Mata Kuliah
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->string('id_server')->nullable()->index()->after('id_matkul')
                ->comment('ID MK dari server');
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'changed'])->default('pending')->index()
                ->comment('Status sinkronisasi local ke server');
            $table->text('sync_message')->nullable()
                ->comment('Log pesan hasil sync terakhir');
        });

        // 4. Kurikulum
        Schema::table('kurikulums', function (Blueprint $table) {
            $table->string('id_server')->nullable()->index()->after('id_kurikulum')
                ->comment('ID Kurikulum dari server');
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'changed'])->default('pending')->index()
                ->comment('Status sinkronisasi local ke server');
            $table->text('sync_message')->nullable()
                ->comment('Log pesan hasil sync terakhir');
        });

        // 5. Kelas Kuliah
        Schema::table('kelas_kuliahs', function (Blueprint $table) {
            $table->string('id_server')->nullable()->index()->after('id_kelas_kuliah')
                ->comment('ID Kelas dari server');
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'changed'])->default('pending')->index()
                ->comment('Status sinkronisasi local ke server');
            $table->text('sync_message')->nullable()
                ->comment('Log pesan hasil sync terakhir');
        });

        // 6. Dosen Pengajar Kelas
        Schema::table('dosen_pengajar_kelas_kuliahs', function (Blueprint $table) {
            $table->string('id_server')->nullable()->index()->after('id_aktivitas_mengajar')
                ->comment('ID Aktivitas Mengajar dari server');
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'changed'])->default('pending')->index()
                ->comment('Status sinkronisasi local ke server');
            $table->text('sync_message')->nullable()
                ->comment('Log pesan hasil sync terakhir');
        });

        // 7. Matkul Kurikulum (Pivot - No id_server)
        Schema::table('matkul_kurikulums', function (Blueprint $table) {
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'changed'])->default('pending')->index()
                ->comment('Status sinkronisasi relasi MK-Kurikulum');
            $table->text('sync_message')->nullable()
                ->comment('Log pesan hasil sync terakhir');
            $table->timestamp('sync_at')->nullable()
                ->comment('Waktu terakhir data berhasil disinkronkan');
        });

        // 8. Peserta Kelas Kuliah (Pivot - No id_server)
        Schema::table('peserta_kelas_kuliahs', function (Blueprint $table) {
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'changed'])->default('pending')->index()
                ->comment('Status sinkronisasi relasi Mahasiswa-Kelas (KRS)');
            $table->text('sync_message')->nullable()
                ->comment('Log pesan hasil sync terakhir');
            $table->timestamp('sync_at')->nullable()
                ->comment('Waktu terakhir data berhasil disinkronkan');
        });

        // 9. Nilai Kelas Perkuliahan (Pivot - No id_server)
        Schema::table('nilai_kelas_perkuliahans', function (Blueprint $table) {
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'changed'])->default('pending')->index()
                ->comment('Status sinkronisasi Nilai');
            $table->text('sync_message')->nullable()
                ->comment('Log pesan hasil sync terakhir');
            // sync_at already exists
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tablesWithIdServer = [
            'biodata_mahasiswas',
            'riwayat_pendidikans',
            'mata_kuliahs',
            'kurikulums',
            'kelas_kuliahs',
            'dosen_pengajar_kelas_kuliahs'
        ];

        foreach ($tablesWithIdServer as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['id_server', 'sync_status', 'sync_message']);
                if (Schema::hasColumn($table->getTable(), 'sync_at')) {
                    // Be careful not to drop existing sync_at unless we added it.
                    // But for simplification in down(), we might skip strict checking
                    // or specifically check per table.
                }
            });
        }

        // Manual drop for specific extra columns added
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->dropColumn('sync_at');
        });

        Schema::table('matkul_kurikulums', function (Blueprint $table) {
            $table->dropColumn(['sync_status', 'sync_message', 'sync_at']);
        });

        Schema::table('peserta_kelas_kuliahs', function (Blueprint $table) {
            $table->dropColumn(['sync_status', 'sync_message', 'sync_at']);
        });

        Schema::table('nilai_kelas_perkuliahans', function (Blueprint $table) {
            $table->dropColumn(['sync_status', 'sync_message']);
        });
    }
};
