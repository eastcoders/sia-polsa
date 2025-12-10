<?php

namespace App\Livewire;

use App\Jobs\SyncBidangMinatJob;
use App\Models\BidangMinat;
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

class BidangMinatTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => BidangMinat::query())
            ->heading('Bidang Minat')
            ->description('Data Bidang Minat')
            ->columns([
                TextColumn::make('id')
                    ->rowIndex()
                    ->label('#'),
                TextColumn::make('nm_bidang_minat')
                    ->label('Nama Bidang'),
                TextColumn::make('nama_program_studi'),
                TextColumn::make('smt_dimulai')
                    ->label('Semester mulai'),
                TextColumn::make('tamat_sk_bidang_minat')
                    ->label('Tanggal selesai'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Bidang Minat')
                    ->button()
                    ->color('primary')
                    ->action(function () {
                        // Jalankan job
                        SyncBidangMinatJob::dispatch();

                        Notification::make()
                            ->title('Sinkronisasi Dimulai')
                            ->body('Proses sinkronisasi Bidang Minat sedang berjalan di belakang layar.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.bidang-minat-table');
    }
}
