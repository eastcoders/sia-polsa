<?php

namespace App\Livewire;

use App\Jobs\SyncJalurMasukJob;
use App\Models\JalurMasuk;
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

class JalurMasukTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => JalurMasuk::query())
            ->heading('Jalur Masuk')
            ->description('Data jalur masuk yang tersedia di sistem')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('id_jalur_masuk'),
                TextColumn::make('nama_jalur_masuk'),
                TextColumn::make('sync_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Jalur Masuk')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncJalurMasukJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Jalur Masuk sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.jalur-masuk-table');
    }
}
