<?php

namespace App\Filament\Pegawai\Resources\Finance\FinancialPayments;

use App\Filament\Pegawai\Resources\Finance\FinancialPayments\Pages;
use App\Filament\Resources\Finance\FinancialPayments\Schemas\FinancialPaymentForm;
use App\Filament\Resources\Finance\FinancialPayments\Tables\FinancialPaymentsTable;
use App\Models\Finance\FinancialPayment;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FinancialPaymentResource extends Resource
{
    protected static ?string $model = FinancialPayment::class;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Verifikasi Pembayaran';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'payment_number';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('keuangan') ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'PENDING')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return FinancialPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialPaymentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialPayments::route('/'),
            'create' => Pages\CreateFinancialPayment::route('/create'),
            'edit' => Pages\EditFinancialPayment::route('/{record}/edit'),
        ];
    }
}
