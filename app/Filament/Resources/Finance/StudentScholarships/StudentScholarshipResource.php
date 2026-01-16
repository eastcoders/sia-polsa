<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\StudentScholarships;

use App\Filament\Resources\Finance\StudentScholarships\Pages\CreateStudentScholarship;
use App\Filament\Resources\Finance\StudentScholarships\Pages\EditStudentScholarship;
use App\Filament\Resources\Finance\StudentScholarships\Pages\ListStudentScholarships;
use App\Filament\Resources\Finance\StudentScholarships\Schemas\StudentScholarshipForm;
use App\Filament\Resources\Finance\StudentScholarships\Tables\StudentScholarshipsTable;
use App\Models\Finance\StudentScholarship;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class StudentScholarshipResource extends Resource
{
    protected static ?string $model = StudentScholarship::class;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Penerima Beasiswa';

    protected static ?string $modelLabel = 'Penerima Beasiswa';

    protected static ?string $pluralModelLabel = 'Penerima Beasiswa';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return StudentScholarshipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentScholarshipsTable::configure($table);
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
            'index' => ListStudentScholarships::route('/'),
            'create' => CreateStudentScholarship::route('/create'),
            'edit' => EditStudentScholarship::route('/{record}/edit'),
        ];
    }
}
