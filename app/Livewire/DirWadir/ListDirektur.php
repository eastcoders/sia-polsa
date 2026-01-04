<?php

namespace App\Livewire\DirWadir;

use App\Models\Dosen;
use App\Models\ProfilePT;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ListDirektur extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => User::query()->role(['direktur', 'wadir']))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pejabat')
                    ->searchable(),
                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Direktur' => 'primary',
                        default => 'info',
                    })
                    ->state(function (User $record) {
                        $profile = ProfilePT::first();
                        if (!$profile)
                            return '-';

                        if ($profile->direktur_id == $record->dosen_id)
                            return 'Direktur';
                        if ($profile->wadir1_id == $record->dosen_id)
                            return 'Wakil Direktur 1';
                        if ($profile->wadir2_id == $record->dosen_id)
                            return 'Wakil Direktur 2';
                        if ($profile->wadir3_id == $record->dosen_id)
                            return 'Wakil Direktur 3';

                        // Fallback logic if manual role assignment without ProfilePT sync
                        if ($record->hasRole('direktur'))
                            return 'Direktur (Unsynced)';
                        if ($record->hasRole('wadir'))
                            return 'Wakil Direktur (Unsynced)';

                        return '-';
                    }),
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
                Action::make('set_pejabat')
                    ->label('Set Pejabat')
                    ->icon('heroicon-o-user-group')
                    ->form([
                        Select::make('jabatan')
                            ->label('Jabatan')
                            ->options([
                                'direktur_id' => 'Direktur',
                                'wadir1_id' => 'Wakil Direktur 1',
                                'wadir2_id' => 'Wakil Direktur 2',
                                'wadir3_id' => 'Wakil Direktur 3',
                            ])
                            ->required(),
                        Select::make('dosen_id')
                            ->label('Pilih Dosen')
                            ->options(Dosen::query()->pluck('nama_dosen', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->label('Set Password User (Opsional)')
                            ->helperText('Kosongkan jika tidak ingin mengubah/membuat password khusus. User existing password tidak berubah.')
                            ->dehydrated(fn($state) => filled($state)),
                    ])
                    ->action(function (array $data) {
                        $profile = ProfilePT::first();
                        if (!$profile) {
                            $profile = ProfilePT::create(['id_perguruan_tinggi' => 'INIT', 'kode_perguruan_tinggi' => 'INIT', 'nama_perguruan_tinggi' => 'INIT']); // Fallback create if empty
                        }

                        $jabatanField = $data['jabatan']; // e.g., 'direktur_id'
                        $newDosenId = $data['dosen_id'];
                        $previousDosenId = $profile->$jabatanField;

                        // 1. Handle Previous Official (Remove Role if necessary)
                        // Note: A user might hold multiple positions? Unlikely. 
                        // But if we remove him from Direktur, we should check if he holds other wadir positions before stripping roles completely?
                        // For simplicity, we assume one person one structural role.
            
                        if ($previousDosenId && $previousDosenId != $newDosenId) {
                            $prevUser = User::where('dosen_id', $previousDosenId)->first();
                            if ($prevUser) {
                                // Determine role to remove
                                $roleToRemove = ($jabatanField == 'direktur_id') ? 'direktur' : 'wadir';
                                $prevUser->removeRole($roleToRemove);
                            }
                        }

                        // 2. Handle New Official
                        $dosen = Dosen::find($newDosenId);
                        $user = User::where('dosen_id', $newDosenId)->orWhere('email', $dosen->email ?? 'dummy')->first();

                        if (!$user) {
                            $username = $dosen->nidn ?? $dosen->nip ?? strtolower(str_replace(' ', '', $dosen->nama_dosen));
                            $email = $dosen->email ?? $username . '@example.com';

                            $user = User::create([
                                'name' => $dosen->nama_dosen,
                                'email' => $email,
                                'username' => $username,
                                'password' => Hash::make($data['password'] ?? 'password'), // Default password if not provided
                                'dosen_id' => $dosen->id,
                            ]);
                        } else {
                            // Update password if provided
                            if (!empty($data['password'])) {
                                $user->password = Hash::make($data['password']);
                                $user->save();
                            }
                            // Link dosen_id if missing
                            if (!$user->dosen_id) {
                                $user->dosen_id = $dosen->id;
                                $user->save();
                            }
                        }

                        // Assign Role
                        $roleToAssign = ($jabatanField == 'direktur_id') ? 'direktur' : 'wadir';
                        $user->assignRole($roleToAssign);

                        // 3. Update ProfilePT
                        $profile->$jabatanField = $newDosenId;
                        $profile->save();

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Pejabat berhasil diupdate.')
                            ->success()
                            ->send();
                    })
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email()->required(),
                        TextInput::make('password')->password()->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state)),
                    ]),
                Action::make('lepas_jabatan')
                    ->label('Lepas Jabatan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $profile = ProfilePT::first();
                        if ($profile) {
                            if ($profile->direktur_id == $record->dosen_id)
                                $profile->update(['direktur_id' => null]);
                            if ($profile->wadir1_id == $record->dosen_id)
                                $profile->update(['wadir1_id' => null]);
                            if ($profile->wadir2_id == $record->dosen_id)
                                $profile->update(['wadir2_id' => null]);
                            if ($profile->wadir3_id == $record->dosen_id)
                                $profile->update(['wadir3_id' => null]);
                        }

                        $record->removeRole('direktur');
                        $record->removeRole('wadir');

                        Notification::make()->title('Jabatan dilepas')->success()->send();
                    })
            ])
            ->toolbarActions([
                //
            ]);
    }

    public function render(): View
    {
        return view('livewire.dir-wadir.list-direktur');
    }
}
