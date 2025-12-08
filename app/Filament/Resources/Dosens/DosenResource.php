<?php

namespace App\Filament\Resources\Dosens;

use App\Filament\Resources\Dosens\Pages\ManageDosens;
use App\Livewire\Dosen\RegistrasiDosen;
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
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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
                Section::make()
                    ->visibleOn('edit')
                    ->columnSpanFull()
                    ->schema([
                        Livewire::make(RegistrasiDosen::class)
                            ->key('register-dosen')
                            ->hidden(fn (?Model $record): bool => $record === null),
                    ]),
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
                    ->url(fn ($record) => DosenResource::getUrl('edit', ['record' => $record])),
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
            'index' => ManageDosens::route('/'),
            'edit' => Pages\EditDosen::route('/{record}/edit'),
        ];
    }
}
