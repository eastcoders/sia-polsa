<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\Scholarships;

use App\Filament\Resources\Finance\Scholarships\Pages\CreateScholarship;
use App\Filament\Resources\Finance\Scholarships\Pages\EditScholarship;
use App\Filament\Resources\Finance\Scholarships\Pages\ListScholarships;
use App\Filament\Resources\Finance\Scholarships\Schemas\ScholarshipForm;
use App\Filament\Resources\Finance\Scholarships\Tables\ScholarshipsTable;
use App\Models\Finance\Scholarship;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ScholarshipResource extends Resource
{
    protected static ?string $model = Scholarship::class;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Master Beasiswa';

    protected static ?string $modelLabel = 'Beasiswa';

    protected static ?string $pluralModelLabel = 'Beasiswa';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return ScholarshipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScholarshipsTable::configure($table);
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
            'index' => ListScholarships::route('/'),
            'create' => CreateScholarship::route('/create'),
            'edit' => EditScholarship::route('/{record}/edit'),
        ];
    }
}
