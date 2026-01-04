<?php

namespace App\Livewire\PembinaAkademik;

use App\Models\User;
use App\Models\Dosen;
use App\Models\Prodi;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use App\Models\DosenPembinaProdi;
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

class ListDosenPembina extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => DosenPembinaProdi::query())
            ->columns([
                TextColumn::make('dosen.nama_dosen')
                    ->label('Nama Dosen')
                    ->searchable(),
                TextColumn::make('prodi.nama_program_studi')
                    ->label('Lingkup Prodi')
                    ->searchable(),
                TextColumn::make('dosen.user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('dosen.nidn')
                    ->label('NIDN / NIP')
                    ->formatStateUsing(fn($state, DosenPembinaProdi $record) => $state ?? $record->dosen->nip ?? '-')
                    ->searchable(['dosen.nidn', 'dosen.nip']),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('assign_dpa')
                    ->label('Tambah DPA')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Select::make('dosen_id')
                            ->label('Pilih Dosen')
                            ->options(Dosen::query()->pluck('nama_dosen', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        Select::make('prodi_id')
                            ->label('Pilih Prodi')
                            ->options(Prodi::query()->pluck('nama_program_studi', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->label('Password User')
                            ->disabled(fn(Get $get) => User::where('dosen_id', $get('dosen_id'))->exists())
                            ->helperText(fn(Get $get) =>
                                User::where('dosen_id', $get('dosen_id'))->exists()
                                ? 'User ini sudah aktif. Perubahan password hanya dapat dilakukan melalui Menu Master Dosen.'
                                : 'Diperlukan jika Dosen belum memiliki User Account.')
                            ->required(fn(Get $get) => !User::where('dosen_id', $get('dosen_id'))->exists()),
                    ])
                    ->action(function (array $data) {
                        $dosenId = $data['dosen_id'];
                        $prodiId = $data['prodi_id'];

                        // 1. Cek User Account
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
                            // Link dosen_id if missing
                            if (!$user->dosen_id) {
                                $user->dosen_id = $dosen->id;
                                $user->save();
                            }
                        }

                        // 2. Assign Role
                        if (!$user->hasRole('dosen_pembina_akademik')) {
                            $user->assignRole('dosen_pembina_akademik');
                        }

                        // 3. Create Pivot Record
                        $exists = DosenPembinaProdi::where('dosen_id', $dosenId)->where('prodi_id', $prodiId)->exists();
                        if (!$exists) {
                            DosenPembinaProdi::create([
                                'dosen_id' => $dosenId,
                                'prodi_id' => $prodiId,
                            ]);

                            Notification::make()->title('DPA Berhasil Ditambahkan')->success()->send();
                        } else {
                            Notification::make()->title('Dosen ini sudah menjadi DPA di Prodi tersebut')->warning()->send();
                        }
                    })
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Hapus Tugas')
                    ->tooltip('Menghapus penugasan DPA dari Prodi ini (User & Role tetap ada)')
                    ->modalHeading('Hapus Penugasan DPA?')
                    ->modalDescription('Apakah anda yakin ingin menghapus tugas Dosen ini sebagai DPA di Prodi ini? Akun User tidak akan terhapus.'),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public function render(): View
    {
        return view('livewire.pembina-akademik.list-dosen-pembina');
    }
}
