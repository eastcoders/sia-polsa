<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;

class NilaiPerkuliahan extends Page
{
    protected static ?string $recordTitleAttribute = 'Nilai Perkuliahan';

    protected static string|UnitEnum|null $navigationGroup = 'Perkulihan';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.nilai-perkuliahan';
}
