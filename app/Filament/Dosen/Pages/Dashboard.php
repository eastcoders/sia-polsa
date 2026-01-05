<?php

namespace App\Filament\Dosen\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('semester_id')
                    ->label('Periode Semester')
                    ->options(fn() => \App\Models\Semester::orderBy('nama_semester', 'desc')->pluck('nama_semester', 'id_semester'))
                    ->default(fn() => \App\Models\Semester::where('a_periode_aktif', 1)->orderBy('id_semester', 'desc')->value('id_semester'))
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(), // Agar tidak terlalu sempit di sidebar filter (jika model sidebar)
            ]);
    }
}
