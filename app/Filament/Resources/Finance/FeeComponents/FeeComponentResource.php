<?php

namespace App\Filament\Resources\Finance\FeeComponents;

use App\Filament\Resources\Finance\FeeComponents\Pages\CreateFeeComponent;
use App\Filament\Resources\Finance\FeeComponents\Pages\EditFeeComponent;
use App\Filament\Resources\Finance\FeeComponents\Pages\ListFeeComponents;
use App\Filament\Resources\Finance\FeeComponents\Schemas\FeeComponentForm;
use App\Filament\Resources\Finance\FeeComponents\Tables\FeeComponentsTable;
use App\Models\Finance\FeeComponent;
use BackedEnum, UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeeComponentResource extends Resource
{
    protected static ?string $model = FeeComponent::class;


    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Komponen Biaya';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FeeComponentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeeComponentsTable::configure($table);
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
            'index' => ListFeeComponents::route('/'),
        ];
    }
}
