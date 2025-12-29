<?php

namespace App\Filament\Resources\RuangKelas;

use App\Filament\Resources\RuangKelas\Pages\ManageRuangKelas;
use App\Models\RuangKelas;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use UnitEnum;

class RuangKelasResource extends Resource
{
    protected static ?string $model = RuangKelas::class;

    protected static string|UnitEnum|null $navigationGroup = 'Perlengkapan';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_ruang_kelas')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex()
                    ->searchable(),
                TextColumn::make('nama_ruang_kelas')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth(Width::Medium)
                    ->iconButton()
                    ->tooltip('Edit Data'),
                ViewAction::make()
                    ->slideOver()
                    ->modalWidth(Width::Medium)
                    ->iconButton()
                    ->tooltip('View Data'),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete Data'),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRuangKelas::route('/'),
        ];
    }
}
