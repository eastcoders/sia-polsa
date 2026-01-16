<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\Finance\ScholarshipStatus;
use App\Models\Finance\Scholarship;
use App\Models\Finance\StudentScholarship;
use App\Models\RiwayatPendidikan;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StudentScholarshipImporter extends Importer
{
    protected static ?string $model = StudentScholarship::class;

    /**
     * Cache for NIM to id_registrasi_mahasiswa mapping.
     * Pre-loaded to avoid N+1 queries during import.
     *
     * @var Collection<string, string>|null
     */
    protected static ?Collection $nimMap = null;

    /**
     * Cache for scholarship code to ID mapping.
     *
     * @var Collection<string, int>|null
     */
    protected static ?Collection $scholarshipMap = null;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nim')
                ->label('NIM Mahasiswa')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                ])
                ->helperText('NIM harus terdaftar di sistem. Data dengan NIM tidak ditemukan akan di-skip.')
                ->example('2024101001'),

            ImportColumn::make('scholarship_code')
                ->label('Kode Beasiswa')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                ])
                ->helperText('Kode beasiswa dari Master Data Beasiswa (harus aktif).')
                ->example('KIP-2024'),

            ImportColumn::make('valid_from')
                ->label('Tanggal Mulai')
                ->requiredMapping()
                ->rules([
                    'required',
                    'date',
                ])
                ->helperText('Format: YYYY-MM-DD (contoh: 2024-01-01)')
                ->example('2024-01-01'),

            ImportColumn::make('valid_until')
                ->label('Tanggal Berakhir')
                ->rules([
                    'nullable',
                    'date',
                    'after_or_equal:valid_from',
                ])
                ->helperText('Opsional. Kosongkan jika beasiswa berlaku sampai lulus.')
                ->example('2028-12-31'),

            ImportColumn::make('coverage_type')
                ->label('Tipe Coverage')
                ->rules([
                    'nullable',
                    'string',
                    'in:FULL_TUITION,PARTIAL_TUITION,TUITION_AND_LIVING',
                ])
                ->helperText('Pilihan: FULL_TUITION, PARTIAL_TUITION, atau TUITION_AND_LIVING. Default: FULL_TUITION')
                ->example('FULL_TUITION'),

            ImportColumn::make('notes')
                ->label('Catatan')
                ->rules([
                    'nullable',
                    'string',
                    'max:1000',
                ])
                ->helperText('Opsional. Catatan tambahan untuk data beasiswa.')
                ->example('Beasiswa periode genap 2024'),
        ];
    }

    /**
     * Pre-load all NIMs and Scholarships before import starts.
     * This prevents N+1 queries during row processing.
     */
    public static function beforeImport(Import $import): void
    {
        // Pre-load all NIMs to memory: ['nim' => 'id_registrasi_mahasiswa']
        static::$nimMap = RiwayatPendidikan::query()
            ->whereNotNull('nim')
            ->pluck('id_registrasi_mahasiswa', 'nim');

        // Pre-load all scholarship codes: ['code' => 'id']
        static::$scholarshipMap = Scholarship::query()
            ->where('is_active', true)
            ->pluck('id', 'code');
    }

    /**
     * Resolve the record from the imported row data.
     *
     * This method handles:
     * 1. NIM validation (skip if not found)
     * 2. Scholarship code validation (skip if not found)
     * 3. Duplicate detection (skip if already exists)
     *
     * @throws RowImportFailedException
     */
    public function resolveRecord(): ?StudentScholarship
    {
        $nim = $this->data['nim'] ?? null;
        $scholarshipCode = $this->data['scholarship_code'] ?? null;
        $validFrom = $this->data['valid_from'] ?? null;

        // Validate NIM exists - throw exception to record in failed rows CSV
        if (!$nim || !static::$nimMap?->has($nim)) {
            throw new RowImportFailedException("NIM '{$nim}' tidak ditemukan dalam database.");
        }

        // Validate scholarship code exists
        if (!$scholarshipCode || !static::$scholarshipMap?->has($scholarshipCode)) {
            throw new RowImportFailedException("Kode beasiswa '{$scholarshipCode}' tidak ditemukan atau tidak aktif.");
        }

        $idRegistrasiMahasiswa = static::$nimMap->get($nim);
        $scholarshipId = static::$scholarshipMap->get($scholarshipCode);

        // Parse valid_from for duplicate check
        $validFromDate = Carbon::parse($validFrom)->toDateString();

        // Check for existing record (idempotency) - skip duplicates
        $existingRecord = StudentScholarship::query()
            ->where('id_registrasi_mahasiswa', $idRegistrasiMahasiswa)
            ->where('scholarship_id', $scholarshipId)
            ->where('valid_from', $validFromDate)
            ->first();

        if ($existingRecord) {
            throw new RowImportFailedException(
                "Mahasiswa dengan NIM '{$nim}' sudah memiliki beasiswa ini pada periode yang sama. Data di-skip."
            );
        }

        // Create new record
        return new StudentScholarship([
            'id_registrasi_mahasiswa' => $idRegistrasiMahasiswa,
            'scholarship_id' => $scholarshipId,
        ]);
    }

    /**
     * Fill the record with imported data.
     */
    public function fillRecord(): void
    {
        $this->record->fill([
            'valid_from' => Carbon::parse($this->data['valid_from'])->toDateString(),
            'valid_until' => isset($this->data['valid_until']) && $this->data['valid_until']
                ? Carbon::parse($this->data['valid_until'])->toDateString()
                : null,
            'coverage_type' => $this->data['coverage_type'] ?? 'FULL_TUITION',
            'status' => ScholarshipStatus::ACTIVE,
            'notes' => $this->data['notes'] ?? null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Clean up caches after import completes.
     */
    public static function afterImport(Import $import): void
    {
        static::$nimMap = null;
        static::$scholarshipMap = null;
    }

    /**
     * Get the completed notification body.
     */
    public static function getCompletedNotificationBody(Import $import): string
    {
        $successCount = number_format($import->successful_rows);
        $failedCount = number_format($import->getFailedRowsCount());

        $body = "Import beasiswa mahasiswa selesai.\n";
        $body .= "✅ Berhasil: {$successCount} baris\n";

        if ($import->getFailedRowsCount() > 0) {
            $body .= "❌ Gagal: {$failedCount} baris\n";
            $body .= "Download file CSV untuk melihat detail error.";
        }

        return $body;
    }

    /**
     * Get the job batch name for queue.
     */
    public function getJobBatchName(): string
    {
        return 'import-student-scholarships';
    }
}
