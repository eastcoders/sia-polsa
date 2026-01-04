<?php

namespace App\Filament\Resources\Pegawais;

use UnitEnum;
use App\Models\User;
use App\Models\Pegawai;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Pegawais\Pages\ManagePegawais;

class PegawaiResource extends Resource
{
    protected static ?string $model = Pegawai::class;
    protected static string|UnitEnum|null $navigationGroup = 'Dosen & Pegawai';

    protected static ?int $navigationSort = 99;

    protected static ?string $title = 'Pegawai';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('nip')
                    ->label('NIP (Opsional)')
                    ->nullable()
                    ->maxLength(20),
                TextInput::make('no_hp')
                    ->label('Nomor HP')
                    ->tel()
                    ->maxLength(20),
                Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
                TextInput::make('alamat')
                    ->label('Alamat Domisili')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('nama_lengkap')
                            ->weight(FontWeight::Bold)
                            ->searchable()
                            ->icon('heroicon-m-user-circle')
                            ->grow(false),
                        TextColumn::make('email')
                            ->icon('heroicon-m-envelope')
                            ->color('gray')
                            ->copyable()
                            ->searchable(),
                    ])->space(2),

                    Stack::make([
                        TextColumn::make('nip')
                            ->label('NIP')
                            ->badge()
                            ->color('info')
                            ->copyable()
                            ->formatStateUsing(fn($state) => $state ?? 'No NIP')
                            ->searchable(),
                        TextColumn::make('no_hp')
                            ->icon('heroicon-m-phone')
                            ->color('gray')
                            ->searchable(),
                    ])->space(2),

                    ToggleColumn::make('is_active')
                        ->label('Status'),
                ])->from('md')
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Keaktifan')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Non-Aktif'),
                TrashedFilter::make(),
            ])
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('create_user')
                    ->label(fn(Pegawai $record) => $record->user ? 'Edit Akses' : 'Buat Akun User')
                    ->icon('heroicon-o-user-plus')
                    ->color(fn(Pegawai $record) => $record->user ? 'success' : 'warning')
                    ->form(function (Pegawai $record) {
                        return [
                            TextInput::make('email')
                                ->default($record->user->email ?? $record->email)
                                ->required()
                                ->email(),
                            TextInput::make('password')
                                ->label('Password')
                                ->password()
                                ->revealable()
                                ->required(fn($livewire) => !$record->user)
                                ->helperText(fn($livewire) => $record->user ? 'Kosongkan jika tidak ingin mengubah password' : 'Wajib diisi untuk akun baru'),
                        ];
                    })
                    ->action(function (Pegawai $record, array $data) {
                        if ($record->user) {
                            $updateData = ['email' => $data['email']];
                            if (!empty($data['password'])) {
                                $updateData['password'] = Hash::make($data['password']);
                            }
                            $record->user->update($updateData);

                            // Ensure role
                            if (!$record->user->hasRole('kepegawaian')) {
                                $record->user->assignRole('kepegawaian');
                            }
                        } else {
                            $user = User::create([
                                'name' => $record->nama_lengkap,
                                'email' => $data['email'],
                                'password' => Hash::make($data['password']),
                                'username' => $record->nip ?? strtolower(str_replace(' ', '', $record->nama_lengkap)), // Fallback username
                                'pegawai_id' => $record->id,
                            ]);
                            $user->assignRole('kepegawaian');
                        }

                        Notification::make()
                            ->title('Akun User Berhasil Disimpan')
                            ->success()
                            ->send();
                    }),
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
            'index' => ManagePegawais::route('/'),
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
