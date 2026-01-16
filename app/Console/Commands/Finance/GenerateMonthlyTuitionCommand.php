<?php

declare(strict_types=1);

namespace App\Console\Commands\Finance;

use App\Actions\Finance\GenerateMonthlyTuitionAction;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateMonthlyTuitionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:generate-monthly-tuition 
                            {--month= : The month to generate for (format: YYYY-MM, defaults to current month)}
                            {--dry-run : Run without actually creating records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly tuition invoices for all active students (runs on 1st of each month)';

    /**
     * Execute the console command.
     */
    public function handle(GenerateMonthlyTuitionAction $action): int
    {
        $this->info('🚀 Starting Monthly Tuition Generation...');
        $this->newLine();

        // Parse month option
        $monthOption = $this->option('month');
        $periodDate = $monthOption
            ? Carbon::createFromFormat('Y-m', $monthOption)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No records will be created');
            $this->newLine();
        }

        $this->info("📅 Generating invoices for period: {$periodDate->format('F Y')}");
        $this->newLine();

        // Execute the action
        $result = $action->execute($periodDate, $dryRun);

        // Display results
        $this->table(
            ['Metric', 'Value'],
            [
                ['Period', $result['period']],
                ['Students Processed', $result['total_students_processed']],
                ['Invoices Created', $result['invoices_created']],
                ['Scholarship Invoices', $result['scholarship_invoices']],
                ['Regular Invoices', $result['regular_invoices']],
                ['Duplicates Skipped', $result['duplicates_skipped']],
                ['Errors', $result['errors_count']],
            ]
        );

        // Show errors if any
        if (!empty($result['errors'])) {
            $this->newLine();
            $this->error('❌ Errors encountered:');
            foreach ($result['errors'] as $error) {
                $this->line("  - Student {$error['student_id']}: {$error['error']}");
            }
        }

        $this->newLine();

        if ($result['errors_count'] > 0) {
            $this->error('⚠️  Completed with errors. Please review the log.');
            return self::FAILURE;
        }

        $this->info('✅ Monthly tuition generation completed successfully!');
        return self::SUCCESS;
    }
}
