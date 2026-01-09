<?php

namespace App\Filament\Resources\Finance\FinancialInvoices;

use App\Filament\Resources\Finance\FinancialInvoices\Pages\CreateFinancialInvoice;
use App\Filament\Resources\Finance\FinancialInvoices\Pages\EditFinancialInvoice;
use App\Filament\Resources\Finance\FinancialInvoices\Pages\ListFinancialInvoices;
use App\Filament\Resources\Finance\FinancialInvoices\Schemas\FinancialInvoiceForm;
use App\Filament\Resources\Finance\FinancialInvoices\Tables\FinancialInvoicesTable;
use App\Models\Finance\FinancialInvoice;
use BackedEnum, UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FinancialInvoiceResource extends Resource
{
    protected static ?string $model = FinancialInvoice::class;


    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Tagihan Pembayaran';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function form(Schema $schema): Schema
    {
        return FinancialInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialInvoicesTable::configure($table);
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
            'index' => ListFinancialInvoices::route('/'),
            'create' => CreateFinancialInvoice::route('/create'),
            'edit' => EditFinancialInvoice::route('/{record}/edit'),
        ];
    }
}
