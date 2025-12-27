<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportDatabase extends Command
{
    /**
     * Nama dan signature command
     *
     * @var string
     */
    protected $signature = 'db:import 
                            {file? : Nama file SQL (tanpa ekstensi)}
                            {--path= : Path lengkap ke file SQL}
                            {--force : Skip konfirmasi}
                            {--backup : Backup database sebelum import}';

    /**
     * Deskripsi command
     *
     * @var string
     */
    protected $description = 'Import database dari file SQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Tentukan path file
        $filePath = $this->getFilePath();

        if (!$filePath) {
            $this->error('File SQL tidak ditemukan!');
            return Command::FAILURE;
        }

        // Konfirmasi
        if (!$this->option('force')) {
            $this->info("File: " . basename($filePath));
            $this->info("Size: " . $this->formatBytes(filesize($filePath)));

            if (!$this->confirm('Apakah Anda yakin ingin mengimport database? Data lama mungkin akan terganti.')) {
                $this->info('Import dibatalkan.');
                return Command::SUCCESS;
            }
        }

        // Backup database jika diinginkan
        if ($this->option('backup')) {
            $this->backupDatabase();
        }

        // Proses import
        try {
            $this->importDatabase($filePath);
            $this->info('✅ Database berhasil diimport!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Gagal mengimport database: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Tentukan path file SQL
     */
    private function getFilePath()
    {
        // Jika menggunakan --path option
        if ($this->option('path')) {
            $path = $this->option('path');
            return File::exists($path) ? $path : null;
        }

        // Jika menggunakan argument file
        $fileName = $this->argument('file');

        // Lokasi default untuk file SQL
        $locations = [
            database_path('sql/'),
            storage_path('app/backups/'),
            storage_path('app/'),
            base_path('database/'),
        ];

        // Cari file dengan ekstensi .sql
        foreach ($locations as $location) {
            // Dengan nama spesifik
            if ($fileName) {
                $fullPath = $location . $fileName . '.sql';
                if (File::exists($fullPath)) {
                    return $fullPath;
                }
            }

            // Atau file .sql pertama di directory
            $files = File::glob($location . '*.sql');
            if (!empty($files)) {
                return $files[0];
            }
        }

        return null;
    }

    /**
     * Import database dari file SQL menggunakan native mysql client
     */
    private function importDatabase($filePath)
    {
        $this->info('⏳ Memulai import database...');

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', '3306');

        // Build command argument for password (handle empty password case)
        // Note: Using -pPASSWORD directly to avoid interactive prompt
        $passwordArg = !empty($password) ? "-p\"{$password}\"" : "";

        // Construct command
        // mysql -u user -pPassword dbname < file.sql
        $command = "mysql -h {$host} -P {$port} -u {$username} {$passwordArg} {$database} < \"{$filePath}\"";

        // Execute command
        $output = [];
        $returnVar = 0;
        
        // Redirect stderr to stdout (2>&1) to capture error messages
        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            $errorMsg = implode("\n", $output);
            // Log full error details
            File::append(storage_path('logs/import-errors.log'), 
                "[" . now() . "] Import Error:\n" . $errorMsg . "\n" . str_repeat("-", 50) . "\n"
            );
            
            throw new \Exception("Command mysql failed. Check logs/import-errors.log for details.\nLast Output: " . substr($errorMsg, 0, 200));
        }

        // Optional: Show output if not empty, mostly warnings
        if (!empty($output)) {
             // Filter out insecure warning if present
            $filteredOutput = array_filter($output, function($line) {
                return !str_contains($line, 'Using a password on the command line interface can be insecure');
            });
            if (!empty($filteredOutput)) {
                $this->line(implode("\n", $filteredOutput));
            }
        }
    }

    /**
     * Backup database sebelum import
     */
    private function backupDatabase()
    {
        $this->info('📦 Membuat backup database...');

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = storage_path('app/backups/backup_' . $timestamp . '.sql');

        // Pastikan directory backup ada
        File::ensureDirectoryExists(dirname($backupPath));

        // Backup menggunakan mysqldump (untuk MySQL)
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', '3306');

        $passwordArg = !empty($password) ? "-p\"{$password}\"" : "";

        $command = "mysqldump -h {$host} -P {$port} -u {$username} {$passwordArg} {$database} > \"{$backupPath}\"";

        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar === 0) {
            $this->info('✅ Backup berhasil: ' . basename($backupPath));
        } else {
            $this->warn('⚠️ Gagal membuat backup menggunakan mysqldump');
            if (!empty($output)) {
                $this->line(implode("\n", $output));
            }
        }
    }

    /**
     * Format bytes ke ukuran yang mudah dibaca
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}