<?php

namespace App\Livewire\Kemahasiswaan;

use App\Models\User;
use App\Models\Dosen;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Illuminate\Contracts\View\View;
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
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class ListKemahasiswaan extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => User::query()->role('kemahasiswaan'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('dosen.nidn')
                    ->label('NIDN / NIP')
                    ->formatStateUsing(fn($state, User $record) => $state ?? $record->dosen->nip ?? '-')
                    ->searchable(['dosen.nidn', 'dosen.nip']),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('add_kemahasiswaan')
                    ->label('Tambah Staf Kemahasiswaan')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Select::make('dosen_id')
                            ->label('Pilih Dosen')
                            ->options(Dosen::query()->pluck('nama_dosen', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->label('Password User')
                            ->disabled(fn(Get $get) => User::where('dosen_id', $get('dosen_id'))->exists())
                            ->helperText(fn(Get $get) =>
                                User::where('dosen_id', $get('dosen_id'))->exists()
                                ? 'User ini sudah aktif. Password hanya bisa diubah via Menu Master Dosen.'
                                : 'Wajib diisi untuk pembuatan akun baru.')
                            ->required(fn(Get $get) => !User::where('dosen_id', $get('dosen_id'))->exists()),
                    ])
                    ->action(function (array $data) {
                        $dosenId = $data['dosen_id'];

                        $dosen = Dosen::find($dosenId);
                        $user = User::where('dosen_id', $dosenId)->orWhere('email', $dosen->email ?? 'dummy')->first();

                        if (!$user) {
                            $username = $dosen->nidn ?? $dosen->nip ?? strtolower(str_replace(' ', '', $dosen->nama_dosen));
                            $email = $dosen->email ?? $username . '@example.com';
                            $password = filled($data['password']) ? $data['password'] : 'password';

                            $user = User::create([
                                'name' => $dosen->nama_dosen,
                                'email' => $email,
                                'username' => $username,
                                'password' => Hash::make($password),
                                'dosen_id' => $dosen->id,
                            ]);
                        } else {
                            if (!$user->dosen_id) {
                                $user->dosen_id = $dosen->id;
                                $user->save();
                            }
                        }

                        if (!$user->hasRole('kemahasiswaan')) {
                            $user->assignRole('kemahasiswaan');
                            Notification::make()->title('Role Kemahasiswaan Berhasil Ditambahkan')->success()->send();
                        } else {
                            Notification::make()->title('Dosen ini sudah memiliki role Kemahasiswaan')->warning()->send();
                        }
                    })
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Hapus Role')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Akses Kemahasiswaan?')
                    ->modalDescription('User ini tidak akan bisa mengakses menu Kemahasiswaan lagi, namun akun User tetap ada.')
                    ->action(function (User $record) {
                        $record->removeRole('kemahasiswaan');
                        Notification::make()->title('Akses Kemahasiswaan Dihapus')->success()->send();
                    })
            ])
            ->toolbarActions([
                //
            ]);
    }

    public function render(): View
    {
        return view('livewire.kemahasiswaan.list-kemahasiswaan');
    }
}
