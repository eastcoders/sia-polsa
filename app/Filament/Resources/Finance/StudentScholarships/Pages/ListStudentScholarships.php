<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\StudentScholarships\Pages;

use App\Filament\Imports\StudentScholarshipImporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Finance\StudentScholarships\StudentScholarshipResource;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListStudentScholarships extends ListRecords
{
    protected static string $resource = StudentScholarshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Separate action for downloading template
            Action::make('downloadImportTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function (): StreamedResponse {
                    return response()->streamDownload(function (): void {
                        $headers = [
                            'nim',
                            'scholarship_code',
                            'valid_from',
                            'valid_until',
                            'coverage_type',
                            'notes',
                        ];

                        $exampleRow = [
                            '2024101001',
                            'KIP-2024',
                            '2024-01-01',
                            '2028-12-31',
                            'FULL_TUITION',
                            'Beasiswa KIP periode 2024',
                        ];

                        echo implode(',', $headers) . "\n";
                        echo implode(',', $exampleRow) . "\n";
                    }, 'template_import_beasiswa.csv');
                }),

            ImportAction::make()
                ->importer(StudentScholarshipImporter::class)
                ->label('Import Beasiswa')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Beasiswa Mahasiswa')
                ->modalDescription(
                    'Upload file CSV berisi data beasiswa mahasiswa. ' .
                    'Gunakan tombol "Download Template" di samping untuk mendapatkan format file yang benar. ' .
                    'Baris dengan NIM tidak valid akan otomatis di-skip dan tercatat dalam laporan error.'
                )
                ->chunkSize(100)
                ->maxRows(5000),

            CreateAction::make(),
        ];
    }
}


