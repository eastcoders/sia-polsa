<?php

namespace App\Filament\Resources\Finance\FinancialPayments\Tables;

use App\Enums\Finance\PaymentMethod;
use App\Models\Finance\FinancialPayment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class FinancialPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('invoices.riwayatPendidikan.mahasiswa.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('payment_number')
                    ->label('No. Pembayaran')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor pembayaran disalin'),
                TextColumn::make('total_allocated')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label() ?? $state)
                    ->color(fn($state) => match ($state) {
                        PaymentMethod::MANUAL_TRANSFER => 'info',
                        PaymentMethod::VIRTUAL_ACCOUNT => 'primary',
                        PaymentMethod::CASH => 'gray',
                        PaymentMethod::SCHOLARSHIP => 'success',
                        PaymentMethod::WAIVER => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'VERIFIED' => 'success',
                        'REJECTED' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'PENDING' => 'heroicon-o-clock',
                        'VERIFIED' => 'heroicon-o-check-circle',
                        'REJECTED' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                TextColumn::make('verified_at')
                    ->label('Waktu Verifikasi')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('Belum diverifikasi'),
                TextColumn::make('verifier.name')
                    ->label('Diverifikasi Oleh')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'PENDING' => 'Menunggu Verifikasi',
                        'VERIFIED' => 'Terverifikasi',
                        'REJECTED' => 'Ditolak',
                    ]),
                SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options(
                        collect(PaymentMethod::cases())
                            ->mapWithKeys(fn($method) => [$method->value => $method->label()])
                            ->toArray()
                    ),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('dari')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari'] ?? null) {
                            $indicators['dari'] = 'Dari: ' . \Carbon\Carbon::parse($data['dari'])->format('d M Y');
                        }
                        if ($data['sampai'] ?? null) {
                            $indicators['sampai'] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(3)
            ->recordActions([
                Action::make('verifikasi')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Pembayaran')
                    ->modalDescription('Apakah Anda yakin ingin memverifikasi pembayaran ini?')
                    ->modalSubmitActionLabel('Ya, Verifikasi')
                    ->visible(fn(FinancialPayment $record) => $record->status === 'PENDING')
                    ->action(function (FinancialPayment $record) {
                        $record->update([
                            'status' => 'VERIFIED',
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);

                        // Update related invoices status
                        foreach ($record->invoices as $invoice) {
                            $invoice->update(['status' => 'PAID']);
                        }

                        Notification::make()
                            ->title('Pembayaran berhasil diverifikasi')
                            ->success()
                            ->send();
                    }),
                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pembayaran')
                    ->modalDescription('Apakah Anda yakin ingin menolak pembayaran ini? Mahasiswa harus mengupload ulang bukti pembayaran.')
                    ->modalSubmitActionLabel('Ya, Tolak')
                    ->visible(fn(FinancialPayment $record) => $record->status === 'PENDING')
                    ->action(function (FinancialPayment $record) {
                        $record->update([
                            'status' => 'REJECTED',
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Pembayaran ditolak')
                            ->warning()
                            ->send();
                    }),
                EditAction::make()
                    ->label('Detail'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }
}

