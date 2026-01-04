<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;
class DirWadirPage extends Page
{
    protected string $view = 'filament.pages.dir-wadir-page';

    protected static string|UnitEnum|null $navigationGroup = 'Dosen & Pegawai';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Direktur & Wadir';

}
