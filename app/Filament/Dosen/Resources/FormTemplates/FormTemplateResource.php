<?php

namespace App\Filament\Dosen\Resources\FormTemplates;

use App\Filament\Dosen\Resources\FormTemplates\Pages\CreateFormTemplate;
use App\Filament\Dosen\Resources\FormTemplates\Pages\EditFormTemplate;
use App\Filament\Dosen\Resources\FormTemplates\Pages\ListFormTemplates;
use App\Filament\Dosen\Resources\FormTemplates\Schemas\FormTemplateForm;
use App\Filament\Dosen\Resources\FormTemplates\Tables\FormTemplatesTable;
use App\Models\FormTemplate;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FormTemplateResource extends Resource
{
    protected static ?string $model = FormTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'BPMI Tools';

    public static function canViewAny(): bool
    {
        // Simple check: user must have 'bpmi' role.
        // Ensure your Spatie Permission middleware/logic is active.
        return auth()->user()->hasRole('bpmi');
    }

    public static function form(Schema $schema): Schema
    {
        return FormTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormTemplatesTable::configure($table);
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
            'index' => ListFormTemplates::route('/'),
            'create' => CreateFormTemplate::route('/create'),
            'edit' => EditFormTemplate::route('/{record}/edit'),
        ];
    }
}
