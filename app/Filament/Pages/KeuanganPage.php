<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;

class KeuanganPage extends Page
{
    protected string $view = 'filament.pages.keuangan-page';
    protected static string|UnitEnum|null $navigationGroup = 'Dosen & Pegawai';
    protected static ?int $navigationSort = 6;
    protected static ?string $title = 'Keuangan';
}
