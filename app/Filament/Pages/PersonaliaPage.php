<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;

class PersonaliaPage extends Page
{
    protected ?string $heading = 'Manajemen User Personalia';
    protected static ?string $title = 'Personalia';
    protected string $view = 'filament.pages.personalia-page';
    protected static string|UnitEnum|null $navigationGroup = 'Dosen & Pegawai';
    protected static ?int $navigationSort = 10;
}
