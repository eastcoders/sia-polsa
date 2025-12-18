<?php

namespace App\Filament\Auth;

use App\Models\Semester;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                Select::make('active_semester_id')
                    ->label('Semester Aktif')
                    ->options(fn() => Semester::where('a_periode_aktif', '1')->orderBy('id_semester', 'desc')->pluck('nama_semester', 'id_semester'))
                    ->default(fn() => Semester::where('a_periode_aktif', '1')->orderBy('id_semester', 'desc')->first()?->id_semester)
                    ->required(),
                // $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $semesterId = $data['active_semester_id'];
        session()->put('active_semester_id', $semesterId);

        unset($data['active_semester_id']);

        return $data;
    }
}
