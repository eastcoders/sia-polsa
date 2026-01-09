<?php

namespace App\Filament\Mahasiswa\Widgets;

use Filament\Widgets\Widget;

class GatekeeperAlertWidget extends Widget
{
    protected string $view = 'filament.mahasiswa.widgets.gatekeeper-alert-widget';

    protected static ?int $sort = -3; // Top priority
    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'pendingCount' => \App\Models\SurveyTicket::where('user_id', auth()->id())
                ->where('status', 'PENDING')
                ->count(),
        ];
    }

    public static function canView(): bool
    {
        return \App\Models\SurveyTicket::where('user_id', auth()->id())
            ->where('status', 'PENDING')
            ->exists();
    }
}
