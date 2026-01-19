<?php

namespace App\Filament\Resources\Pegawais\Pages;

use App\Models\Pegawai;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Filament\Resources\Pegawais\PegawaiResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePegawais extends ManageRecords
{
    protected static string $resource = PegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_dummy')
                ->label('Generate Dummy Pegawai')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->visible(fn() => app()->environment('local'))
                ->requiresConfirmation()
                ->modalHeading('Generate Dummy Pegawai')
                ->modalDescription('Ini akan membuat data pegawai dummy. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Generate')
                ->form([
                    TextInput::make('jumlah')
                        ->label('Jumlah Data')
                        ->numeric()
                        ->default(10)
                        ->minValue(1)
                        ->maxValue(100)
                        ->required()
                        ->helperText('Masukkan jumlah data pegawai dummy yang ingin dibuat (1-100)'),
                ])
                ->action(function (array $data) {
                    $faker = \Faker\Factory::create('id_ID');
                    $jumlah = $data['jumlah'];

                    for ($i = 0; $i < $jumlah; $i++) {
                        Pegawai::create([
                            'nip' => $faker->optional(0.7)->numerify('####################'),
                            'nama_lengkap' => $faker->name(),
                            'email' => $faker->unique()->safeEmail(),
                            'no_hp' => $faker->phoneNumber(),
                            'alamat' => $faker->address(),
                            'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                            'jabatan_fungsional' => $faker->optional()->randomElement([
                                'Asisten Ahli',
                                'Lektor',
                                'Lektor Kepala',
                                'Guru Besar',
                            ]),
                            'unit_kerja' => $faker->optional()->randomElement([
                                'Fakultas Teknik',
                                'Fakultas Ekonomi',
                                'Fakultas Hukum',
                                'Fakultas Kedokteran',
                                'Bagian Akademik',
                                'Bagian Keuangan',
                                'Bagian Kepegawaian',
                            ]),
                            'is_active' => $faker->boolean(85),
                        ]);
                    }

                    Notification::make()
                        ->title("Berhasil membuat {$jumlah} data pegawai dummy")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
