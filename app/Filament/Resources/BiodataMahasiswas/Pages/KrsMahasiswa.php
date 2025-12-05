<?php

namespace App\Filament\Resources\BiodataMahasiswas\Pages;

use App\Filament\Resources\BiodataMahasiswas\BiodataMahasiswaResource;
use BackedEnum;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;

class KrsMahasiswa extends Page
{
    use InteractsWithRecord;

    protected static string $resource = BiodataMahasiswaResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected string $view = 'filament.resources.biodata-mahasiswas.pages.krs-mahasiswa';

    public static function getNavigationLabel(): string
    {
        return 'KRS Mahasiswa';
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
