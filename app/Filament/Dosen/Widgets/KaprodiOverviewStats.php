<?php

namespace App\Filament\Dosen\Widgets;

use App\Services\KaprodiMonitoringService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

use Filament\Widgets\Concerns\InteractsWithPageFilters;

class KaprodiOverviewStats extends BaseWidget
{
    use InteractsWithPageFilters;

    // Mengatur agar widget ini muncul di urutan atas
    protected static ?int $sort = 1;

    /**
     * Tentukan siapa saja yang boleh melihat Widget ini.
     * Menggunakan array agar mudah ditambahkan role lain di masa depan.
     */
    public static function canView(): bool
    {
        $user = Auth::user();

        // Daftar Role yang Diizinkan (Extensible)
        $allowedRoles = ['kaprodi'];

        // Cek apakah user punya salah satu role di atas
        // Asumsi menggunakan Spatie Permission
        return $user && $user->hasAnyRole($allowedRoles);
    }

    protected function getStats(): array
    {
        // 1. Ambil Filter dari Dashboard
        // 'semester_id' didapat dari nama field di Dashboard::filtersForm
        $semesterId = $this->filters['semester_id'] ?? null;

        // 2. Panggil Service
        $service = new KaprodiMonitoringService();

        // 3. Hitung Metrik (Query Count) dengan Filter
        $countKelasBermasalah = $service->getKelasBermasalahQuery($semesterId)->count();
        $countLowAttendance = $service->getKelasLowAttendanceQuery(4, $semesterId)->count();
        $countMhsKritis = $service->getMahasiswaKritisQuery($semesterId)->count();

        // 3. Return Stats Cards
        return [
            Stat::make('Kelas Bermasalah (Hazard)', $countKelasBermasalah)
                ->description('Kelas Tanpa Dosen/Jadwal')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($countKelasBermasalah > 0 ? 'danger' : 'success')
                ->url($this->getDrillDownUrl('kelas_bermasalah')), // Placeholder URL logic

            Stat::make('Kelas Low Performance', $countLowAttendance)
                ->description('Realisasi Tatap Muka Rendah')
                ->descriptionIcon('heroicon-m-clock')
                ->color($countLowAttendance > 0 ? 'warning' : 'success'),

            Stat::make('Mahasiswa Kritis', $countMhsKritis)
                ->description('IPK < 2.00')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color($countMhsKritis > 0 ? 'danger' : 'success'),
        ];
    }

    /**
     * Helper untuk generate URL Drill Down
     */
    protected function getDrillDownUrl(string $type): string
    {
        switch ($type) {
            case 'kelas_bermasalah':
                return \App\Filament\Dosen\Resources\MonitoringKelasResource::getUrl('index', [
                    'tableFilters' => [
                        'kelas_bermasalah' => ['isActive' => true],
                    ],
                ]);

            case 'low_attendance':
                return \App\Filament\Dosen\Resources\MonitoringKelasResource::getUrl('index', [
                    'tableFilters' => [
                        'low_attendance' => ['isActive' => true],
                    ],
                ]);

            case 'mahasiswa_kritis':
                return \App\Filament\Dosen\Resources\MonitoringMahasiswaResource::getUrl('index', [
                    'tableFilters' => [
                        'mahasiswa_kritis' => ['isActive' => true],
                    ],
                ]);

            default:
                return '#';
        }
    }
}
