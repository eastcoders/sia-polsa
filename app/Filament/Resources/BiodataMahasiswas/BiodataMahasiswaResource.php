<?php

namespace App\Filament\Resources\BiodataMahasiswas;

use App\Filament\Resources\BiodataMahasiswas\Pages\CreateBiodataMahasiswa;
use App\Filament\Resources\BiodataMahasiswas\Pages\EditBiodataMahasiswa;
use App\Filament\Resources\BiodataMahasiswas\Pages\HistoriNilaiMahasiswa;
use App\Filament\Resources\BiodataMahasiswas\Pages\HistoriPendidikan;
use App\Filament\Resources\BiodataMahasiswas\Pages\KrsMahasiswa;
use App\Filament\Resources\BiodataMahasiswas\Pages\ListBiodataMahasiswas;
use App\Filament\Resources\BiodataMahasiswas\Pages\ViewBiodataMahasiswa;
use App\Filament\Resources\BiodataMahasiswas\Schemas\BiodataMahasiswaForm;
use App\Filament\Resources\BiodataMahasiswas\Tables\BiodataMahasiswasTable;
use App\Models\BiodataMahasiswa;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BiodataMahasiswaResource extends Resource
{
    protected static ?string $model = BiodataMahasiswa::class;

    protected static ?string $pluralModelLabel = 'Mahasiswa';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BiodataMahasiswaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BiodataMahasiswasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBiodataMahasiswas::route('/'),
            'create' => CreateBiodataMahasiswa::route('/create'),
            'edit' => EditBiodataMahasiswa::route('/{record}/edit'),
            'view' => ViewBiodataMahasiswa::route('/{record}/view'),
            'histori-pendidikan' => HistoriPendidikan::route('/{record}/histori-pendidikan'),
            'krs-mahasiswa' => KrsMahasiswa::route('/{record}/krs-mahasiswa'),
            'histori-nilai-mahasiswa' => HistoriNilaiMahasiswa::route('/{record}/histori-nilai-mahasiswa'),
        ];
    }

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewBiodataMahasiswa::class,
            HistoriPendidikan::class,
            KrsMahasiswa::class,
            HistoriNilaiMahasiswa::class,
        ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'Mahasiswa';
    }
}
