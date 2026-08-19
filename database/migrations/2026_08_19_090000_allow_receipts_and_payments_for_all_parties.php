<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->index(['company_id', 'supplier_id', 'receipt_no'], 'receipts_supplier_number_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();
            $table->index(['company_id', 'customer_id', 'payment_no'], 'payments_customer_number_index');
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropIndex('receipts_supplier_number_index');
            $table->dropConstrainedForeignId('supplier_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_customer_number_index');
            $table->dropConstrainedForeignId('customer_id');
        });
    }
};
