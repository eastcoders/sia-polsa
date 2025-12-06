<?php

namespace App\Filament\Resources\KelasKuliahs;

use App\Filament\Resources\KelasKuliahs\Pages\ManageKelasKuliahs;
use App\Models\KelasKuliah;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KelasKuliahResource extends Resource
{
    protected static ?string $model = KelasKuliah::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id_kelas_kuliah')
                    ->required(),
                TextInput::make('id_prodi')
                    ->required(),
                TextInput::make('id_semester')
                    ->required(),
                TextInput::make('nama_kelas_kuliah')
                    ->required(),
                TextInput::make('smt_mk')
                    ->required(),
                TextInput::make('smt_tm')
                    ->required(),
                TextInput::make('smt_prak')
                    ->required(),
                TextInput::make('smt_sim')
                    ->required(),
                TextInput::make('bahasan'),
                TextInput::make('a_selenggara_pditt'),
                TextInput::make('apa_untuk_pditt'),
                TextInput::make('kapasitas'),
                DatePicker::make('tanggal_mulai_efektif'),
                DatePicker::make('tanggal_akhir_efektif'),
                TextInput::make('id_mou'),
                TextInput::make('id_matkul')
                    ->required(),
                Select::make('lingkup')
                    ->options([1 => '1', '2', '3']),
                Select::make('mode')
                    ->options(['O' => 'O', 'F' => 'F', 'M' => 'M']),
                DateTimePicker::make('sync_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_kelas_kuliah')
                    ->searchable(),
                TextColumn::make('id_prodi')
                    ->searchable(),
                TextColumn::make('id_semester')
                    ->searchable(),
                TextColumn::make('nama_kelas_kuliah')
                    ->searchable(),
                TextColumn::make('smt_mk')
                    ->searchable(),
                TextColumn::make('smt_tm')
                    ->searchable(),
                TextColumn::make('smt_prak')
                    ->searchable(),
                TextColumn::make('smt_sim')
                    ->searchable(),
                TextColumn::make('bahasan')
                    ->searchable(),
                TextColumn::make('a_selenggara_pditt')
                    ->searchable(),
                TextColumn::make('apa_untuk_pditt')
                    ->searchable(),
                TextColumn::make('kapasitas')
                    ->searchable(),
                TextColumn::make('tanggal_mulai_efektif')
                    ->date()
                    ->sortable(),
                TextColumn::make('tanggal_akhir_efektif')
                    ->date()
                    ->sortable(),
                TextColumn::make('id_mou')
                    ->searchable(),
                TextColumn::make('id_matkul')
                    ->searchable(),
                TextColumn::make('lingkup')
                    ->badge(),
                TextColumn::make('mode')
                    ->badge(),
                TextColumn::make('sync_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageKelasKuliahs::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
