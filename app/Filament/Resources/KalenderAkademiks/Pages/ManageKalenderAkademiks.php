<?php

namespace App\Filament\Resources\KalenderAkademiks\Pages;

use App\Filament\Resources\KalenderAkademiks\KalenderAkademikResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKalenderAkademiks extends ManageRecords
{
    protected static string $resource = KalenderAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('importHolidays')
                ->label('Import Hari Libur')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    \Filament\Forms\Components\TextInput::make('year')
                        ->label('Tahun')
                        ->numeric()
                        ->default(now()->year)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $year = $data['year'];
                    $response = \Illuminate\Support\Facades\Http::withoutVerifying()->get("https://api-harilibur.vercel.app/api?year={$year}");

                    if ($response->successful()) {
                        $holidays = $response->json();
                        $count = 0;

                        foreach ($holidays as $holiday) {
                            if (isset($holiday['is_national_holiday']) && $holiday['is_national_holiday']) {
                                \App\Models\KalenderAkademik::updateOrCreate(
                                    [
                                        'tanggal_mulai' => $holiday['holiday_date'],
                                    ],
                                    [
                                        'tanggal_selesai' => $holiday['holiday_date'],
                                        'keterangan' => $holiday['holiday_name'],
                                        'is_libur' => true,
                                    ]
                                );
                                $count++;
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Import Berhasil')
                            ->body("Berhasil mengimport {$count} hari libur nasional tahun {$year}.")
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Import Gagal')
                            ->body('Gagal mengambil data dari API.')
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
