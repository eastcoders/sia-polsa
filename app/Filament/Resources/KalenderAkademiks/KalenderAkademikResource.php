<?php

namespace App\Filament\Resources\KalenderAkademiks;

use App\Filament\Resources\KalenderAkademiks\Pages\ManageKalenderAkademiks;
use App\Models\KalenderAkademik;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KalenderAkademikResource extends Resource
{
    protected static ?string $model = KalenderAkademik::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal_mulai')
                    ->required(),
                DatePicker::make('tanggal_selesai')
                    ->required(),
                TextInput::make('keterangan')
                    ->required(),
                Toggle::make('is_libur')
                    ->required(),
                Toggle::make('is_minggu_ujian')
                    ->required(),
                TextInput::make('id_semester'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('tanggal_selesai')
                    ->date()
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->searchable(),
                IconColumn::make('is_libur')
                    ->boolean(),
                IconColumn::make('is_minggu_ujian')
                    ->boolean(),
                TextColumn::make('id_semester')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageKalenderAkademiks::route('/'),
        ];
    }
}
