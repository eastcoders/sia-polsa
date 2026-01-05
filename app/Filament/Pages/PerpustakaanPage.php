<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;

class PerpustakaanPage extends Page
{
    protected ?string $heading = 'Manajemen User Perpustakaan';
    protected static ?string $title = 'Perpustakaan';
    protected string $view = 'filament.pages.perpustakaan-page';
    protected static string|UnitEnum|null $navigationGroup = 'Dosen & Pegawai';
    protected static ?int $navigationSort = 8;
}
