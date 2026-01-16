<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('financial_invoices', function (Blueprint $table) {
            // Batch ID for tracking bulk-generated invoices (for rollback capability)
            $table->uuid('batch_id')->nullable()->after('generated_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('financial_invoices', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }
};
