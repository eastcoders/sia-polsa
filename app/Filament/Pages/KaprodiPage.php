<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;

class KaprodiPage extends Page
{
    protected string $view = 'filament.pages.kaprodi-page';

    protected static string|UnitEnum|null $navigationGroup = 'Dosen & Pegawai';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Kaprodi';
}
