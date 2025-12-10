<?php

namespace App\Filament\Pages;

use UnitEnum;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class NilaiPerkuliahan extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Nilai Perkuliahan';

    protected static string|UnitEnum|null $navigationGroup = 'Perkulihan';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.nilai-perkuliahan';
}
