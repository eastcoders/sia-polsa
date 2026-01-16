<?php

namespace App\Filament\Resources\Finance\FeeStructures\Actions;

use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use App\Models\Finance\FeeStructure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use App\Actions\Finance\GenerateMonthlyTuitionAction;

class GenerateBulkInvoicesAction
{
    public static function make(): Action
    {
        return Action::make('generateBulkInvoices')
            ->label('Generate Tagihan')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->modalHeading('Generate Tagihan Otomatis')
            ->modalDescription('Generate tagihan untuk semua mahasiswa aktif berdasarkan FeeStructure dengan komponen RECURRING.')
            ->modalWidth('lg')
            ->form([
                Section::make('Periode Tagihan')
                    ->description('Pilih bulan dan tahun periode tagihan')
                    ->schema([
                        Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->default(now()->month)
                            ->required(),
                        Select::make('tahun')
                            ->label('Tahun')
                            ->options(function () {
                                $currentYear = now()->year;
                                return collect(range($currentYear - 1, $currentYear + 1))
                                    ->mapWithKeys(fn($year) => [$year => $year])
                                    ->toArray();
                            })
                            ->default(now()->year)
                            ->required(),
                    ])->columns(2),

                Section::make('Opsi')
                    ->schema([
                        Toggle::make('dry_run')
                            ->label('Preview Mode (Dry Run)')
                            ->helperText('Jika aktif, hanya menampilkan preview tanpa membuat tagihan sebenarnya. DISARANKAN untuk diaktifkan terlebih dahulu.')
                            ->default(true),
                    ]),

                Section::make('Informasi')
                    ->schema([
                        Placeholder::make('info')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="text-sm space-y-2">
                                    <p><strong>Proses yang akan dijalankan:</strong></p>
                                    <ol class="list-decimal list-inside space-y-1">
                                        <li>Mengambil semua mahasiswa dengan status <strong>Aktif</strong></li>
                                        <li>Untuk setiap mahasiswa, mencari <strong>FeeStructure</strong> yang sesuai berdasarkan angkatan, prodi, dan waktu kuliah</li>
                                        <li>Hanya komponen dengan tipe <strong>RECURRING</strong> yang akan dimasukkan</li>
                                        <li>Mahasiswa dengan beasiswa aktif akan otomatis mendapat status <strong>LUNAS</strong></li>
                                        <li>Tagihan duplikat (periode yang sama) akan dilewati</li>
                                    </ol>
                                </div>
                            ')),
                    ]),
            ])
            ->action(function (array $data): void {
                $periodDate = Carbon::create($data['tahun'], $data['bulan'], 1);
                $dryRun = $data['dry_run'] ?? true;

                $action = new GenerateMonthlyTuitionAction();
                $result = $action->execute($periodDate, $dryRun);

                if ($dryRun) {
                    // Show preview results (only browser notification, not saved to DB)
                    Notification::make()
                        ->title('Preview Hasil Generate')
                        ->body(self::formatResultMessage($result, true))
                        ->info()
                        ->persistent()
                        ->send();
                } else {
                    // Show actual results and save to database
                    $status = $result['errors_count'] > 0 ? 'warning' : 'success';

                    Notification::make()
                                ->title('Tagihan Berhasil Digenerate')
                                ->body(self::formatResultMessage($result, false))
                        ->$status()
                            ->persistent()
                            ->sendToDatabase(auth()->user())
                            ->send();
                }
            })
            ->requiresConfirmation()
            ->modalSubmitActionLabel('Generate');
    }

    private static function formatResultMessage(array $result, bool $isDryRun): string
    {
        $prefix = $isDryRun ? '(PREVIEW) ' : '';
        $batchInfo = isset($result['batch_id']) && $result['batch_id']
            ? "Batch ID: {$result['batch_id']}\n"
            : '';

        return <<<MSG
            {$prefix}Periode: {$result['period']}
            {$batchInfo}
            📊 Total Mahasiswa Diproses: {$result['total_students_processed']}
            ✅ Invoice Dibuat: {$result['invoices_created']}
            🎓 Beasiswa (Auto-PAID): {$result['scholarship_invoices']}
            💳 Reguler (UNPAID): {$result['regular_invoices']}
            ⏭️ Duplikat Dilewati: {$result['duplicates_skipped']}
            ⚠️ Tanpa FeeStructure: {$result['no_fee_structure_skipped']}
            ❌ Error: {$result['errors_count']}
            MSG;
    }
}
