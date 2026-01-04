<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use UnitEnum;

class DosenPembinaPage extends Page
{
    protected string $view = 'filament.pages.dosen-pembina-page';
    protected static string|UnitEnum|null $navigationGroup = 'Dosen & Pegawai';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Pembina Akademik';
}
