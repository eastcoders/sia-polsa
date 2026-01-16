<?php

declare(strict_types=1);

namespace App\Console\Commands\Academic;

use App\Actions\Academic\CalculateStudentGpaAction;
use App\Models\AktivitasKuliahMahasiswa;
use App\Models\RiwayatPendidikan;
use Illuminate\Console\Command;

class RecalculateAkmCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'academic:recalculate-akm 
                            {--student= : Specific student ID (id_registrasi_mahasiswa)}
                            {--all : Recalculate for all students with grades}
                            {--dirty : Only recalculate students with pending updates}
                            {--dry-run : Run without actually saving changes}';

    /**
     * The console command description.
     */
    protected $description = 'Recalculate AKM (IPS, IPK, SKS) for students. Use --all for nightly batch processing.';

    /**
     * Execute the console command.
     */
    public function handle(CalculateStudentGpaAction $action): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No records will be updated');
            $this->newLine();
        }

        // Option 1: Single student
        if ($studentId = $this->option('student')) {
            return $this->processSingleStudent($action, $studentId, $dryRun);
        }

        // Option 2: All students with grades
        if ($this->option('all')) {
            return $this->processAllStudents($action, $dryRun);
        }

        // Option 3: Dirty students only (default for cron)
        if ($this->option('dirty')) {
            return $this->processDirtyStudents($action, $dryRun);
        }

        $this->error('Please specify --student=<id>, --all, or --dirty');
        return self::FAILURE;
    }

    /**
     * Process a single student.
     */
    private function processSingleStudent(CalculateStudentGpaAction $action, string $studentId, bool $dryRun): int
    {
        $this->info("📊 Calculating GPA for student: {$studentId}");

        $result = $action->execute($studentId, $dryRun);

        if ($result['success']) {
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Student ID', $result['id_registrasi_mahasiswa']],
                    ['NIM', $result['nim'] ?? 'N/A'],
                    ['Semesters Processed', $result['semesters_processed']],
                    ['Total SKS', $result['total_sks']],
                    ['Final IPK', number_format($result['final_ipk'], 2)],
                ]
            );
            $this->info('✅ Calculation completed successfully!');
            return self::SUCCESS;
        }

        $this->error("❌ Calculation failed: {$result['error']}");
        return self::FAILURE;
    }

    /**
     * Process all students with grades.
     */
    private function processAllStudents(CalculateStudentGpaAction $action, bool $dryRun): int
    {
        $this->info('📊 Fetching all students with grades...');

        // Get unique student IDs that have nilai records
        $studentIds = RiwayatPendidikan::query()
            ->whereHas('aktivitasKuliahMahasiswa')
            ->orWhereExists(function ($query) {
                $query->selectRaw(1)
                    ->from('nilai_kelas_perkuliahans')
                    ->whereColumn('nilai_kelas_perkuliahans.id_registrasi_mahasiswa', 'riwayat_pendidikans.id_registrasi_mahasiswa');
            })
            ->pluck('id_registrasi_mahasiswa')
            ->toArray();

        $this->info("Found {" . count($studentIds) . "} students to process");

        return $this->executeBatch($action, $studentIds, $dryRun);
    }

    /**
     * Process only "dirty" students (those with recent grade changes).
     * For now, this processes students with AKM records - can be enhanced with dirty flag later.
     */
    private function processDirtyStudents(CalculateStudentGpaAction $action, bool $dryRun): int
    {
        $this->info('📊 Fetching students with pending AKM updates...');

        // Get students with AKM records (can be enhanced with dirty flag column)
        $studentIds = AktivitasKuliahMahasiswa::query()
            ->distinct()
            ->pluck('id_registrasi_mahasiswa')
            ->toArray();

        $this->info("Found " . count($studentIds) . " students to process");

        return $this->executeBatch($action, $studentIds, $dryRun);
    }

    /**
     * Execute batch calculation with progress bar.
     */
    private function executeBatch(CalculateStudentGpaAction $action, array $studentIds, bool $dryRun): int
    {
        if (empty($studentIds)) {
            $this->warn('No students found to process.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($studentIds));
        $bar->start();

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($studentIds as $studentId) {
            $result = $action->execute($studentId, $dryRun);

            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
                $errors[] = [
                    'id' => $studentId,
                    'error' => $result['error'] ?? 'Unknown',
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary table
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Processed', count($studentIds)],
                ['Success', $successCount],
                ['Failed', $failedCount],
            ]
        );

        // Show errors if any
        if (!empty($errors)) {
            $this->newLine();
            $this->error('❌ Errors encountered:');
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->line("  - {$error['id']}: {$error['error']}");
            }
            if (count($errors) > 10) {
                $this->line("  ... and " . (count($errors) - 10) . " more errors");
            }
        }

        if ($failedCount > 0) {
            $this->warn('⚠️  Completed with some errors. Check logs for details.');
            return self::FAILURE;
        }

        $this->info('✅ All calculations completed successfully!');
        return self::SUCCESS;
    }
}
