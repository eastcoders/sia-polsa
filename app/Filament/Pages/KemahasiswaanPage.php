<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;

class KemahasiswaanPage extends Page
{
    protected string $view = 'filament.pages.kemahasiswaan-page';
    protected static string|UnitEnum|null $navigationGroup = 'Dosen & Pegawai';
    protected static ?int $navigationSort = 5;
    protected static ?string $title = 'Kemahasiswaan';
}
