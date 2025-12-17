<?php

namespace App\Filament\Resources\Kurikulums;

use App\Filament\Resources\Kurikulums\Pages\ManageKurikulums;
use App\Models\Kurikulum;
use App\Models\Prodi;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class KurikulumResource extends Resource
{
    protected static ?string $model = Kurikulum::class;

    protected static string|UnitEnum|null $navigationGroup = 'Perkulihan';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kurikulum')
                    ->required(),
                Select::make('id_prodi')
                    ->label('Program Studi')
                    ->relationship(
                        name: 'prodi',
                        titleAttribute: 'nama_program_studi', // kolom nyata
                        modifyQueryUsing: fn(Builder $query) => $query
                            ->orderBy('nama_jenjang_pendidikan')
                            ->orderBy('nama_program_studi')
                    )
                    ->getOptionLabelFromRecordUsing(fn(Prodi $record) => $record->programStudiLengkap)
                    ->native(false)
                    ->required(),
                Select::make('id_semester')
                    ->label('Mulai Berlaku')
                    ->native(false)
                    ->relationship(
                        name: 'semester',
                        titleAttribute: 'nama_semester',
                        modifyQueryUsing: fn(Builder $query) => $query
                            ->where('id_tahun_ajaran', '>=', now()->year)
                            ->orderBy('id_tahun_ajaran', 'asc')
                            ->orderBy('nama_semester', 'asc')
                    )
                    ->required(),
                TextInput::make('jumlah_sks_wajib')
                    ->label('SKS Wajib')
                    ->required()
                    ->default(0)
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, Get $get) => $set(
                        'jumlah_sks_lulus',
                        ($get('jumlah_sks_pilihan') ?? 0) +
                        ($get('jumlah_sks_wajib') ?? 0)
                    )),
                TextInput::make('jumlah_sks_pilihan')
                    ->label('SKS Pilihan')
                    ->required()
                    ->default(0)
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, Get $get) => $set(
                        'jumlah_sks_lulus',
                        ($get('jumlah_sks_pilihan') ?? 0) +
                        ($get('jumlah_sks_wajib') ?? 0)
                    )),
                TextInput::make('jumlah_sks_lulus')
                    ->label('Jumlah SKS')
                    ->required()
                    ->default(0)
                    ->numeric()
                    ->helperText('( sks Wajib + sks Pilihan )')
                    ->readOnly(),
                View::make('livewire.table-matkul-kurikulum')
                    ->visibleOn('edit')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->rowIndex()
                    ->label('#'),
                TextColumn::make('nama_kurikulum')
                    ->searchable(),
                TextColumn::make('prodi.programStudiLengkap')
                    ->searchable(),
                TextColumn::make('semester.nama_semester')
                    ->label('Mulai Berlaku')
                    ->searchable(),
                TextColumn::make('jumlah_sks_lulus')
                    ->label('SKS Lulus')
                    ->searchable(),
                TextColumn::make('jumlah_sks_wajib')
                    ->label('SKS Wajib')
                    ->searchable(),
                TextColumn::make('jumlah_sks_pilihan')
                    ->label('SKS Pilihan')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->options(
                        Prodi::orderBy('nama_jenjang_pendidikan')
                            ->orderBy('nama_program_studi')
                            ->pluck('nama_program_studi', 'id_prodi')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['values'])) {
                            $query->whereIn('id_prodi', $data['values']);
                        }

                        return $query;
                    })
                    ->multiple(),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn($record) => KurikulumResource::getUrl('edit', ['record' => $record->getKey()])),
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
            'index' => ManageKurikulums::route('/'),
            'edit' => Pages\EditKurikulum::route('/{record}/edit'),
        ];
    }
}
