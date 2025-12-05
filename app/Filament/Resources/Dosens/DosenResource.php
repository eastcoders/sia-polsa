<?php

namespace App\Filament\Resources\Dosens;

use App\Filament\Resources\Dosens\Pages\ManageDosens;
use App\Models\Agama;
use App\Models\Dosen;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DosenResource extends Resource
{
    protected static ?string $model = Dosen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Dosen';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_dosen')
                    ->label('Nama Dosen')
                    ->required(),
                TextInput::make('nidn')
                    ->label('NIDN'),
                TextInput::make('nip')
                    ->label('NIP'),
                Select::make('jenis_kelamin')
                    ->options([
                        'L' => 'Laki Laki',
                        'P' => 'Perempuan',
                    ])
                    ->required(),
                Select::make('id_agama')
                    ->label('Agama')
                    ->options(fn () => Agama::orderBy('id_agama')->pluck('nama_agama', 'id_agama'))
                    ->required(),
                DatePicker::make('tanggal_lahir')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Dosen')
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('nama_dosen')
                    ->label('Nama Dosen')
                    ->searchable(),
                TextColumn::make('nidn')
                    ->label('NIDN')
                    ->searchable(),
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable(),
                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn ($state) => $state == 'L' ? 'Laki Laki' : 'Perempuan'),
                TextColumn::make('agama.nama_agama'),
                TextColumn::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date('d F y'),
                TextColumn::make('nama_status_aktif')
                    ->label('Status'),
            ])
            ->defaultSort('nama_dosen')
            ->filters([
                SelectFilter::make('nama_status_aktif')
                    ->options(fn () => Dosen::distinct()
                        ->pluck('nama_status_aktif', 'nama_status_aktif')
                        ->mapWithKeys(fn ($value) => [$value => ucfirst($value)])
                        ->toArray())
                    ->default('Aktif'),
            ])
            ->recordActions([
                EditAction::make()
                    ->disabled(fn ($record) => $record->sync_at != null),
                DeleteAction::make()
                    ->disabled(fn ($record) => $record->sync_at != null),
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
            'index' => ManageDosens::route('/'),
        ];
    }
}
