<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;

class SarprasPage extends Page
{
    protected ?string $heading = 'Manajemen User Sarpras';
    protected static ?string $title = 'Sarpras';
    protected string $view = 'filament.pages.sarpras-page';
    protected static string|UnitEnum|null $navigationGroup = 'Dosen & Pegawai';
    protected static ?int $navigationSort = 9;
}
