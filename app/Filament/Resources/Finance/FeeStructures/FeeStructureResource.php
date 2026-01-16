<?php

namespace App\Filament\Resources\Finance\FeeStructures;

use App\Filament\Resources\Finance\FeeStructures\Pages\CreateFeeStructure;
use App\Filament\Resources\Finance\FeeStructures\Pages\EditFeeStructure;
use App\Filament\Resources\Finance\FeeStructures\Pages\ListFeeStructures;
use App\Filament\Resources\Finance\FeeStructures\Schemas\FeeStructureForm;
use App\Filament\Resources\Finance\FeeStructures\Tables\FeeStructuresTable;
use App\Models\Finance\FeeStructure;
use BackedEnum, UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeeStructureResource extends Resource
{
    protected static ?string $model = FeeStructure::class;


    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Struktur Biaya';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return FeeStructureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeeStructuresTable::configure($table);
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
            'index' => ListFeeStructures::route('/'),
            // 'create' => CreateFeeStructure::route('/create'),
            // 'edit' => EditFeeStructure::route('/{record}/edit'),
        ];
    }
}
