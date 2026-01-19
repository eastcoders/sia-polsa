<?php

namespace App\Filament\Pegawai\Resources\Finance\FeeComponents;

use App\Filament\Pegawai\Resources\Finance\FeeComponents\Pages;
use App\Filament\Resources\Finance\FeeComponents\Schemas\FeeComponentForm;
use App\Filament\Resources\Finance\FeeComponents\Tables\FeeComponentsTable;
use App\Models\Finance\FeeComponent;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FeeComponentResource extends Resource
{
    protected static ?string $model = FeeComponent::class;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Komponen Biaya';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('keuangan') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return FeeComponentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeeComponentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeeComponents::route('/'),
            'create' => Pages\CreateFeeComponent::route('/create'),
            'edit' => Pages\EditFeeComponent::route('/{record}/edit'),
        ];
    }
}
