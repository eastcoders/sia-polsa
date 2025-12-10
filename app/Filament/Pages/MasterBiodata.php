<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class MasterBiodata extends Page
{
    protected string $view = 'filament.pages.master-biodata';

    protected static ?string $title = 'Data Master Biodata';

    protected static string|UnitEnum|null $navigationGroup = 'Master Record';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Master Biodata';
    }

    // Table Wilayah
}
