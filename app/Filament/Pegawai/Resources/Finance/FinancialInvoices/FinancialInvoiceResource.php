<?php

namespace App\Filament\Pegawai\Resources\Finance\FinancialInvoices;

use App\Filament\Pegawai\Resources\Finance\FinancialInvoices\Pages;
use App\Filament\Resources\Finance\FinancialInvoices\Schemas\FinancialInvoiceForm;
use App\Filament\Resources\Finance\FinancialInvoices\Tables\FinancialInvoicesTable;
use App\Models\Finance\FinancialInvoice;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FinancialInvoiceResource extends Resource
{
    protected static ?string $model = FinancialInvoice::class;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Tagihan Pembayaran';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('keuangan') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return FinancialInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialInvoicesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialInvoices::route('/'),
            'create' => Pages\CreateFinancialInvoice::route('/create'),
            'edit' => Pages\EditFinancialInvoice::route('/{record}/edit'),
        ];
    }
}
