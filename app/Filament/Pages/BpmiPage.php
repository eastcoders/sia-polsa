<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;

class BpmiPage extends Page
{
    protected ?string $heading = 'Manajemen User BPMI';
    protected static ?string $title = 'BPMI';
    protected string $view = 'filament.pages.bpmi-page';
    protected static string|UnitEnum|null $navigationGroup = 'Dosen & Pegawai';
    protected static ?int $navigationSort = 7;
}
