<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['company_id', 'name'], 'customers_company_name_index');
            $table->index(['company_id', 'phone'], 'customers_company_phone_index');
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->index(['company_id', 'name'], 'suppliers_company_name_index');
            $table->index(['company_id', 'phone'], 'suppliers_company_phone_index');
        });
        Schema::table('receipts', function (Blueprint $table) {
            $table->index(['company_id', 'receipt_no'], 'receipts_company_number_index');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['company_id', 'payment_no'], 'payments_company_number_index');
        });
    }

    public function down(): void
    {
        Schema::table('customers', fn (Blueprint $table) => $table->dropIndex('customers_company_name_index'));
        Schema::table('customers', fn (Blueprint $table) => $table->dropIndex('customers_company_phone_index'));
        Schema::table('suppliers', fn (Blueprint $table) => $table->dropIndex('suppliers_company_name_index'));
        Schema::table('suppliers', fn (Blueprint $table) => $table->dropIndex('suppliers_company_phone_index'));
        Schema::table('receipts', fn (Blueprint $table) => $table->dropIndex('receipts_company_number_index'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropIndex('payments_company_number_index'));
    }
};
