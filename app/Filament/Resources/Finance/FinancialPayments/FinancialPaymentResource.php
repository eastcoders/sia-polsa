<?php

namespace App\Filament\Resources\Finance\FinancialPayments;

use App\Filament\Resources\Finance\FinancialPayments\Pages\CreateFinancialPayment;
use App\Filament\Resources\Finance\FinancialPayments\Pages\EditFinancialPayment;
use App\Filament\Resources\Finance\FinancialPayments\Pages\ListFinancialPayments;
use App\Filament\Resources\Finance\FinancialPayments\Schemas\FinancialPaymentForm;
use App\Filament\Resources\Finance\FinancialPayments\Tables\FinancialPaymentsTable;
use App\Models\Finance\FinancialPayment;
use BackedEnum, UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FinancialPaymentResource extends Resource
{
    protected static ?string $model = FinancialPayment::class;


    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Verifikasi Pembayaran';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'payment_number';

    public static function form(Schema $schema): Schema
    {
        return FinancialPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialPaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinancialPayments::route('/'),
            'create' => CreateFinancialPayment::route('/create'),
            'edit' => EditFinancialPayment::route('/{record}/edit'),
        ];
    }
}
