<?php

namespace App\Livewire\Kaprodi;

use App\Models\User;
use App\Models\Dosen;
use App\Models\Prodi;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Illuminate\Contracts\View\View;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class ListKaprodi extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => User::query()->role('kaprodi'))
            ->columns([
                TextColumn::make('dosen.nama_dosen')
                    ->label('Nama Kaprodi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dosen.memimpinProdi.nama_program_studi')
                    ->label('Program Studi')
                    ->badge()
                    ->separator(',')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->copyable(),
                TextColumn::make('dosen.nidn')
                    ->label('NIDN / NIP')
                    ->formatStateUsing(fn($state, $record) => $state ?? $record->dosen?->nip ?? '-')
                    ->searchable(['dosen.nidn', 'dosen.nip']),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('create_kaprodi')
                    ->label('Tambah Kaprodi')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Select::make('dosen_id')
                            ->label('Pilih Dosen')
                            ->options(Dosen::query()->pluck('nama_dosen', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('prodi_id')
                            ->label('Pilih Prodi')
                            ->options(Prodi::query()->get()->mapWithKeys(fn($prodi) => [$prodi->id => $prodi->program_studi_lengkap]))
                            ->searchable()
                            ->required(),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->helperText('Password untuk login Kaprodi baru (jika user belum ada).'),
                    ])
                    ->action(function (array $data) {
                        $dosen = Dosen::find($data['dosen_id']);
                        $prodi = Prodi::find($data['prodi_id']);

                        if (!$dosen || !$prodi) {
                            return;
                        }

                        // 1. Cek User Dosen
                        $user = User::where('dosen_id', $dosen->id)->first();

                        if (!$user) {
                            // Create User Baru
                            $username = $dosen->nidn ?? $dosen->nip ?? explode('@', $dosen->email)[0] ?? strtolower(str_replace(' ', '', $dosen->nama_dosen));

                            $user = User::create([
                                'name' => $dosen->nama_dosen,
                                'email' => $dosen->email ?? $username . '@example.com', // Fallback if no email
                                'username' => $username,
                                'password' => bcrypt($data['password']),
                                'dosen_id' => $dosen->id,
                            ]);
                        }

                        // 2. Assign Role
                        $user->assignRole('kaprodi');

                        // 3. Update Prodi
                        // Karena 1 Dosen bisa banyak Prodi, kita tidak perlu cleanup prodi lama dosen ini (User A).
                        // Kita hanya perlu pastikan Prodi yang dipilih ini (Prodi X) tidak memiliki ketua ganda yang tidak diinginkan.
                        // Logic: Timpa saja ketua lama.
            
                        $prodi->ketua_prodi_id = $dosen->id;
                        $prodi->save();

                        Notification::make()
                            ->title('Berhasil')
                            ->body("Kaprodi {$dosen->nama_dosen} berhasil diplot ke {$prodi->nama_program_studi}")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn($state) => \Illuminate\Support\Facades\Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create'),
                    ]),
                Action::make('remove_kaprodi')
                    ->label('Hapus Jabatan')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Apakah Anda yakin ingin menghapus jabatan Kaprodi dari user ini? User akan dicopot dari SEMUA prodi yang dipimpin.')
                    ->action(function (User $record) {
                        // 1. Null-kan ketua_prodi_id di SEMUA Prodi yang dia pimpin
                        if ($record->dosen) {
                            $record->dosen->memimpinProdi()->update(['ketua_prodi_id' => null]);
                        }

                        // 2. Remove Role
                        $record->removeRole('kaprodi');

                        Notification::make()
                            ->title('Jabatan Dihapus')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public function render(): View
    {
        return view('livewire.kaprodi.list-kaprodi');
    }
}
