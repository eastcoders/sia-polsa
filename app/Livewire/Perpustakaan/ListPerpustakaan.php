<?php

namespace App\Livewire\Perpustakaan;

use App\Models\User;
use App\Models\Dosen;
use App\Models\Pegawai;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\Dosens\DosenResource;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use App\Filament\Resources\Pegawais\PegawaiResource;

class ListPerpustakaan extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => User::query()->role('perpustakaan'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('source_type')
                    ->label('Sumber Data')
                    ->badge()
                    ->colors([
                        'primary' => 'Dosen',
                        'success' => 'Pegawai',
                    ])
                    ->formatStateUsing(function (User $record) {
                        if ($record->dosen_id) {
                            return 'Dosen';
                        }
                        if ($record->pegawai_id) {
                            return 'Pegawai';
                        }

                        return 'Unknown';
                    })
                    ->getStateUsing(function (User $record) {
                        if ($record->dosen_id) {
                            return 'Dosen';
                        }
                        if ($record->pegawai_id) {
                            return 'Pegawai';
                        }

                        return 'Unknown';
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('add_perpustakaan')
                    ->label('Tambah User Perpustakaan')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Radio::make('source_type')
                            ->label('Sumber Data Personil')
                            ->options([
                                'dosen' => 'Dari Dosen',
                                'pegawai' => 'Dari Pegawai',
                            ])
                            ->default('dosen')
                            ->inline()
                            ->live()
                            ->required(),

                        // Dosen Selection
                        Select::make('dosen_id')
                            ->label('Pilih Dosen')
                            ->options(Dosen::query()->pluck('nama_dosen', 'id'))
                            ->searchable()
                            ->visible(fn(Get $get) => $get('source_type') === 'dosen')
                            ->required(fn(Get $get) => $get('source_type') === 'dosen')
                            ->live()
                            ->hint(new HtmlString('Data tidak ditemukan? <a href="' . DosenResource::getUrl('index') . '" target="_blank" class="text-primary-600 underline">Tambah Dosen Baru</a>')),

                        // Pegawai Selection
                        Select::make('pegawai_id')
                            ->label('Pilih Pegawai')
                            ->options(Pegawai::query()->pluck('nama_lengkap', 'id'))
                            ->searchable()
                            ->visible(fn(Get $get) => $get('source_type') === 'pegawai')
                            ->required(fn(Get $get) => $get('source_type') === 'pegawai')
                            ->live()
                            ->hint(new HtmlString('Data tidak ditemukan? <a href="' . PegawaiResource::getUrl('index') . '" target="_blank" class="text-primary-600 underline">Tambah Pegawai Baru</a>')),

                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->label('Password User')
                            ->disabled(function (Get $get) {
                                if ($get('source_type') === 'dosen') {
                                    return User::where('dosen_id', $get('dosen_id'))->exists();
                                }
                                if ($get('source_type') === 'pegawai') {
                                    return User::where('pegawai_id', $get('pegawai_id'))->exists();
                                }

                                return false;
                            })
                            ->helperText(function (Get $get) {
                                $exists = false;
                                if ($get('source_type') === 'dosen') {
                                    $exists = User::where('dosen_id', $get('dosen_id'))->exists();
                                } elseif ($get('source_type') === 'pegawai') {
                                    $exists = User::where('pegawai_id', $get('pegawai_id'))->exists();
                                }

                                return $exists
                                    ? 'User ini sudah aktif. Password hanya bisa diubah via Menu Master.'
                                    : 'Wajib diisi untuk pembuatan akun baru.';
                            })
                            ->required(function (Get $get) {
                                if ($get('source_type') === 'dosen') {
                                    return !User::where('dosen_id', $get('dosen_id'))->exists();
                                }
                                if ($get('source_type') === 'pegawai') {
                                    return !User::where('pegawai_id', $get('pegawai_id'))->exists();
                                }

                                return true;
                            }),
                    ])
                    ->action(function (array $data) {
                        $sourceType = $data['source_type'];
                        $user = null;
                        $person = null;
                        $role = 'perpustakaan';

                        if ($sourceType === 'dosen') {
                            $person = Dosen::find($data['dosen_id']);
                            $user = User::where('dosen_id', $person->id)->orWhere('email', $person->email ?? 'dummy')->first();
                        } elseif ($sourceType === 'pegawai') {
                            $person = Pegawai::find($data['pegawai_id']);
                            $user = User::where('pegawai_id', $person->id)->orWhere('email', $person->email ?? 'dummy')->first();
                        }

                        if (!$user) {
                            $name = $sourceType === 'dosen' ? $person->nama_dosen : $person->nama_lengkap;
                            $identity = $sourceType === 'dosen'
                                ? ($person->nidn ?? $person->nip)
                                : ($person->nip);

                            // Fallback username logic
                            $username = $identity ?? strtolower(str_replace(' ', '', $name));

                            $email = $person->email ?? $username . '@example.com';
                            $password = filled($data['password']) ? $data['password'] : 'password';

                            $createData = [
                                'name' => $name,
                                'email' => $email,
                                'username' => $username,
                                'password' => Hash::make($password),
                            ];

                            if ($sourceType === 'dosen') {
                                $createData['dosen_id'] = $person->id;
                            } else {
                                $createData['pegawai_id'] = $person->id;
                            }

                            $user = User::create($createData);
                        } else {
                            // Link existing user if not linked
                            if ($sourceType === 'dosen' && !$user->dosen_id) {
                                $user->dosen_id = $person->id;
                                $user->save();
                            } elseif ($sourceType === 'pegawai' && !$user->pegawai_id) {
                                $user->pegawai_id = $person->id;
                                $user->save();
                            }
                        }

                        if (!$user->hasRole($role)) {
                            $user->assignRole($role);
                            Notification::make()->title('Akses Perpustakaan Berhasil Ditambahkan')->success()->send();
                        } else {
                            Notification::make()->title('User ini sudah memiliki akses Perpustakaan')->warning()->send();
                        }
                    })
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Hapus Role')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Akses Perpustakaan?')
                    ->modalDescription('User ini tidak akan bisa mengakses menu Perpustakaan lagi, namun akun User tetap ada.')
                    ->action(function (User $record) {
                        $record->removeRole('perpustakaan');
                        Notification::make()->title('Akses Perpustakaan Dihapus')->success()->send();
                    })
            ])
            ->toolbarActions([
                //
            ]);
    }

    public function render(): View
    {
        return view('livewire.perpustakaan.list-perpustakaan');
    }
}
