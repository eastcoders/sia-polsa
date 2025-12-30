<?php

namespace App\Filament\Resources\Dosens;

use BackedEnum;
use App\Models\Agama;
use App\Models\Dosen;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use App\Livewire\Dosen\RegistrasiDosen;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Livewire;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Dosens\Pages\ManageDosens;

class DosenResource extends Resource
{
    protected static ?string $model = Dosen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

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
                    ->options(fn() => Agama::orderBy('id_agama')->pluck('nama_agama', 'id_agama'))
                    ->required(),
                DatePicker::make('tanggal_lahir')
                    ->required(),
                Section::make()
                    ->visibleOn(['edit', 'view'])
                    ->disabledOn('view')
                    ->columnSpanFull()
                    ->schema([
                        Livewire::make(RegistrasiDosen::class)
                            ->key('register-dosen')
                            ->hidden(fn(?Model $record): bool => $record === null),
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
                    ->formatStateUsing(fn($state) => $state == 'L' ? 'Laki Laki' : 'Perempuan'),
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
                    ->options(fn() => Dosen::distinct()
                        ->pluck('nama_status_aktif', 'nama_status_aktif')
                        ->mapWithKeys(fn($value) => [$value => ucfirst($value)])
                        ->toArray())
                    ->default('Aktif'),
            ])
            ->recordUrl(false)
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Data')
                    ->disabled(fn($record) => $record->sync_at != null)
                    ->url(fn($record) => DosenResource::getUrl('edit', ['record' => $record])),
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View Data'),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus')
                    ->disabled(fn($record) => $record->sync_at != null),
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
