<?php

namespace App\Livewire;

use App\Jobs\SyncSemesterJob;
use App\Models\Semester;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class SemesterTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Semester::query()->where('a_periode_aktif', 1))
            ->heading('Semester')
            ->description('Data Semester')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('id_semester'),
                TextColumn::make('nama_semester'),
                TextColumn::make('id_tahun_ajaran'),
                TextColumn::make('semester'),
                TextColumn::make('a_periode_aktif'),
                TextColumn::make('tanggal_mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('tanggal_selesai')
                    ->date()
                    ->sortable(),
                TextColumn::make('sync_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Semester')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncSemesterJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Semester sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.semester-table');
    }
}
