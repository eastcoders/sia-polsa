<?php

namespace App\Filament\Dosen\Resources\Finance\ExamDispensations;

use App\Filament\Dosen\Resources\Finance\ExamDispensations\Pages;
use App\Filament\Resources\Finance\ExamDispensations\Schemas\ExamDispensationForm;
use App\Filament\Resources\Finance\ExamDispensations\Tables\ExamDispensationsTable;
use App\Models\Finance\ExamDispensation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ExamDispensationResource extends Resource
{
    protected static ?string $model = ExamDispensation::class;


    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Dispensasi Ujian';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('keuangan') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return ExamDispensationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamDispensationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExamDispensations::route('/'),
            'create' => Pages\CreateExamDispensation::route('/create'),
            'edit' => Pages\EditExamDispensation::route('/{record}/edit'),
        ];
    }
}
