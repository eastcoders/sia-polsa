<?php

namespace App\Filament\Resources\Finance\ExamDispensations;

use App\Filament\Resources\Finance\ExamDispensations\Pages\CreateExamDispensation;
use App\Filament\Resources\Finance\ExamDispensations\Pages\EditExamDispensation;
use App\Filament\Resources\Finance\ExamDispensations\Pages\ListExamDispensations;
use App\Filament\Resources\Finance\ExamDispensations\Schemas\ExamDispensationForm;
use App\Filament\Resources\Finance\ExamDispensations\Tables\ExamDispensationsTable;
use App\Models\Finance\ExamDispensation;
use BackedEnum, UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExamDispensationResource extends Resource
{
    protected static ?string $model = ExamDispensation::class;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Dispensasi Ujian';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return ExamDispensationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamDispensationsTable::configure($table);
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
            'index' => ListExamDispensations::route('/'),
            'create' => CreateExamDispensation::route('/create'),
            'edit' => EditExamDispensation::route('/{record}/edit'),
        ];
    }
}
