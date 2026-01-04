<?php

namespace App\Filament\Resources\Dosens;

use App\Filament\Resources\Dosens\Pages\ManageDosens;
use App\Livewire\Dosen\RegistrasiDosen;
use App\Models\Agama;
use App\Models\Dosen;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class DosenResource extends Resource
{
    protected static ?string $model = Dosen::class;

    protected static string|UnitEnum|null $navigationGroup = 'Dosen & Pegawai';

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
                Action::make('create_user')
                    ->tooltip(fn(Dosen $record) => $record->user ? 'Aktifkan Role Dosen' : 'Buat User')
                    ->label(fn(Dosen $record) => $record->user ? 'Aktifkan Dosen' : 'Buat User')
                    ->icon(fn(Dosen $record) => $record->user ? 'heroicon-o-shield-check' : 'heroicon-o-user-plus')
                    ->color('success')
                    ->iconButton()
                    ->requiresConfirmation(fn(Dosen $record) => $record->user !== null)
                    ->modalHeading(fn(Dosen $record) => $record->user ? 'Tambahkan Akses Dosen?' : 'Buat Akun Baru')
                    ->modalDescription(fn(Dosen $record) => $record->user ? 'User ini sudah memiliki akun (misal: Kaprodi). Klik Konfirmasi untuk menambahkan role Dosen.' : null)
                    ->form(function (Dosen $record) {
                        if ($record->user) {
                            return [];
                        }

                        return [
                            TextInput::make('email')
                                ->label('Email Login')
                                ->email()
                                ->required(),
                            TextInput::make('password')
                                ->label('Password')
                                ->password()
                                ->confirmed()
                                ->required(),
                            TextInput::make('password_confirmation')
                                ->label('Konfirmasi Password')
                                ->password()
                                ->required(),
                        ];
                    })
                    ->action(function (Dosen $record, array $data) {
                        $user = $record->user;

                        if (!$user) {
                            // 1. Cek User Exist by Email (Manual Input fallback)
                            $user = \App\Models\User::where('email', $data['email'])->first();

                            if ($user) {
                                // Match by email -> Link dosen_id
                                if (!$user->dosen_id) {
                                    $user->dosen_id = $record->id;
                                    $user->save();
                                }
                            } else {
                                // Create User Baru
                                $username = $record->nidn ?? $record->nip ?? explode('@', $data['email'])[0] ?? strtolower(str_replace(' ', '', $record->nama_dosen));

                                $user = \App\Models\User::create([
                                    'name' => $record->nama_dosen,
                                    'email' => $data['email'],
                                    'username' => $username,
                                    'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
                                    'dosen_id' => $record->id,
                                ]);
                            }
                        }

                        if (!$user->hasRole('dosen')) {
                            $user->assignRole('dosen');
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Sukses')
                            ->body("User {$user->name} berhasil diset sebagai Dosen.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Dosen $record) => $record->user === null || !$record->user->hasRole('dosen')),
            ], RecordActionsPosition::BeforeColumns)
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
