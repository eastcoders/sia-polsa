<?php

namespace App\Filament\Mahasiswa\Resources\KartuUjians\Pages;

use App\Filament\Mahasiswa\Resources\KartuUjians\KartuUjianResource;
use App\Services\ExamPeriodService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKartuUjians extends ManageRecords
{
    protected static string $resource = KartuUjianResource::class;

    protected function getHeaderActions(): array
    {
        $pendingSurveys = KartuUjianResource::checkGatekeeper();
        $activeExamType = ExamPeriodService::getRelevantExamType();

        return [
            Action::make('cetak_kartu_uts')
                ->label('Cetak Kartu UTS')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn() => route('mahasiswa.kartu-ujian.print', ['jenis' => 'UTS']))
                ->openUrlInNewTab()
                ->visible($pendingSurveys === 0 && $activeExamType === 'UTS'),

            Action::make('cetak_kartu_uas')
                ->label('Cetak Kartu UAS')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->url(fn() => route('mahasiswa.kartu-ujian.print', ['jenis' => 'UAS']))
                ->openUrlInNewTab()
                ->visible($pendingSurveys === 0 && $activeExamType === 'UAS'),

            CreateAction::make()
                ->hidden(),
        ];
    }
}
