<?php

namespace App\Filament\Mahasiswa\Resources\TagihanSayas;

use App\Enums\Finance\InvoiceStatus;
use App\Enums\Finance\PaymentMethod;
use App\Models\Finance\FinancialInvoice;
use App\Models\Finance\FinancialPayment;
use App\Models\Semester;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class TagihanSayaResource extends Resource
{
    protected static ?string $model = FinancialInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Tagihan Saya';

    protected static ?string $slug = 'tagihan-saya';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralModelLabel = 'Tagihan';

    protected static ?string $modelLabel = 'Tagihan';

    // ===== PREVENT CRUD OPERATIONS =====
    // Mahasiswa hanya bisa VIEW dan SUBMIT PAYMENT, tidak bisa CREATE/EDIT/DELETE

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    // ===== QUERY FILTER: Only show current student's invoices =====

    public static function getEloquentQuery(): Builder
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || !$user->mahasiswa) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->with(['riwayatPendidikan.mahasiswa', 'items', 'payments'])
            ->whereHas('riwayatPendidikan', function (Builder $query) use ($user) {
                $query->where('id_mahasiswa', $user->mahasiswa->id_mahasiswa);
            })
            ->orderByDesc('period_date');
    }

    // ===== TABLE CONFIGURATION =====

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),

                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor invoice disalin'),

                TextColumn::make('period_date')
                    ->label('Periode')
                    ->date('F Y')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Tagihan')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(function (FinancialInvoice $record) {
                        if ($record->status === InvoiceStatus::PAID) {
                            return null;
                        }
                        return $record->isOverdue() ? 'danger' : null;
                    })
                    ->description(function (FinancialInvoice $record) {
                        if ($record->status === InvoiceStatus::PAID) {
                            return null;
                        }
                        if ($record->isOverdue()) {
                            return 'Terlambat!';
                        }
                        $daysLeft = now()->diffInDays($record->due_date, false);
                        if ($daysLeft <= 7 && $daysLeft > 0) {
                            return "{$daysLeft} hari lagi";
                        }
                        return null;
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label() ?? $state)
                    ->color(fn($state) => $state?->color() ?? 'gray'),

                TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->state(function (FinancialInvoice $record) {
                        if ($record->status === InvoiceStatus::PAID) {
                            return 'Lunas';
                        }

                        // Check if there's a pending payment
                        $pendingPayment = $record->payments()
                            ->wherePivot('amount_allocated', '>', 0)
                            ->where('status', 'PENDING')
                            ->exists();

                        if ($pendingPayment) {
                            return 'Menunggu Verifikasi';
                        }

                        return 'Belum Dibayar';
                    })
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Lunas' => 'success',
                        'Menunggu Verifikasi' => 'warning',
                        'Belum Dibayar' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('paid_at')
                    ->label('Dibayar Pada')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('period_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'UNPAID' => 'Belum Lunas',
                        'PAID' => 'Lunas',
                    ]),

                SelectFilter::make('period_year')
                    ->label('Tahun')
                    ->options(function () {
                        return FinancialInvoice::query()
                            ->selectRaw('YEAR(period_date) as year')
                            ->distinct()
                            ->orderByDesc('year')
                            ->pluck('year', 'year')
                            ->toArray();
                    }),
            ])
            ->actions([
                // ===== ACTION: SUBMIT PAYMENT =====
                Action::make('ajukan_pembayaran')
                    ->label('Ajukan Pembayaran')
                    ->icon('heroicon-o-banknotes')
                    ->color('primary')
                    ->visible(function (FinancialInvoice $record) {
                        // Only show if UNPAID and no pending payment exists
                        if ($record->status !== InvoiceStatus::UNPAID) {
                            return false;
                        }

                        // Check for existing pending payment to prevent duplicate submissions
                        $hasPendingPayment = $record->payments()
                            ->where('status', 'PENDING')
                            ->exists();

                        return !$hasPendingPayment;
                    })
                    ->modalHeading('Ajukan Pembayaran')
                    ->modalDescription(function (FinancialInvoice $record) {
                        return "Total tagihan: Rp " . number_format($record->total_amount, 0, ',', '.');
                    })
                    ->form([
                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                PaymentMethod::MANUAL_TRANSFER->value => PaymentMethod::MANUAL_TRANSFER->label(),
                                PaymentMethod::VIRTUAL_ACCOUNT->value => PaymentMethod::VIRTUAL_ACCOUNT->label(),
                                PaymentMethod::CASH->value => PaymentMethod::CASH->label(),
                            ])
                            ->required()
                            ->native(false),

                        FileUpload::make('proof_file')
                            ->label('Bukti Pembayaran')
                            ->helperText('Upload foto/screenshot bukti transfer. Format: JPG, PNG, PDF. Maks: 2MB')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
                            ->maxSize(2048) // 2MB
                            ->directory('payment-proofs/' . date('Y/m'))
                            ->visibility('private')
                            ->required(),

                        Textarea::make('notes')
                            ->label('Catatan (Opsional)')
                            ->placeholder('Contoh: Transfer dari rekening atas nama XYZ')
                            ->rows(2),
                    ])
                    ->action(function (array $data, FinancialInvoice $record) {
                        // Generate unique payment number
                        $paymentNumber = 'PAY/' . date('Y/m/') . strtoupper(substr(uniqid(), -6));

                        // Calculate file hash for duplicate detection
                        $filePath = $data['proof_file'];
                        $fileHash = null;

                        if ($filePath && Storage::disk('public')->exists($filePath)) {
                            $fileContent = Storage::disk('public')->get($filePath);
                            $fileHash = hash('sha256', $fileContent);

                            // Check for duplicate file submission
                            $duplicateExists = FinancialPayment::where('proof_file_hash', $fileHash)
                                ->exists();

                            if ($duplicateExists) {
                                Notification::make()
                                    ->title('Bukti Pembayaran Duplikat')
                                    ->body('File bukti pembayaran ini sudah pernah digunakan sebelumnya.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }

                        // Create payment record with PENDING status
                        $payment = FinancialPayment::create([
                            'payment_number' => $paymentNumber,
                            'payment_method' => $data['payment_method'],
                            'proof_file_path' => $filePath,
                            'proof_file_hash' => $fileHash,
                            'status' => 'PENDING',
                            'notes' => $data['notes'],
                        ]);

                        // Link payment to invoice via pivot
                        $payment->invoices()->attach($record->id, [
                            'amount_allocated' => $record->total_amount,
                        ]);

                        // Send notification to current user
                        Notification::make()
                            ->title('Pembayaran Berhasil Diajukan')
                            ->body("Nomor pembayaran: {$paymentNumber}. Menunggu verifikasi dari bagian keuangan.")
                            ->success()
                            ->send();

                        // Send notification to admin/finance staff
                        $admins = \App\Models\User::role(['admin', 'keuangan', 'super_admin'])->get();
                        foreach ($admins as $admin) {
                            $admin->notify(new \App\Notifications\PaymentSubmittedNotification(
                                $payment,
                                $record->invoice_number,
                                $record->total_amount
                            ));
                        }
                    }),

                // ===== ACTION: VIEW PENDING PAYMENT STATUS =====
                Action::make('lihat_status')
                    ->label('Lihat Status Pembayaran')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(function (FinancialInvoice $record) {
                        if ($record->status === InvoiceStatus::PAID) {
                            return false;
                        }

                        return $record->payments()
                            ->where('status', 'PENDING')
                            ->exists();
                    })
                    ->modalHeading('Status Pembayaran')
                    ->modalContent(function (FinancialInvoice $record) {
                        $pendingPayment = $record->payments()
                            ->where('status', 'PENDING')
                            ->first();

                        return view('filament.mahasiswa.resources.tagihan-saya-resource.modals.payment-status', [
                            'payment' => $pendingPayment,
                            'invoice' => $record,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                // ===== ACTION: VIEW INVOICE DETAILS =====
                Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn(FinancialInvoice $record) => "Detail Invoice: {$record->invoice_number}")
                    ->modalContent(function (FinancialInvoice $record) {
                        return view('filament.mahasiswa.resources.tagihan-saya-resource.modals.invoice-detail', [
                            'invoice' => $record->load('items'),
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->emptyStateHeading('Tidak ada tagihan')
            ->emptyStateDescription('Anda tidak memiliki tagihan yang perlu dibayar saat ini.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Mahasiswa\Resources\TagihanSayas\Pages\ListTagihanSayas::route('/'),
        ];
    }
}
