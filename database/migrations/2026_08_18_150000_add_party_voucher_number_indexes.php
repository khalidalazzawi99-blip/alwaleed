<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->unique(
                ['company_id', 'customer_id', 'receipt_no'],
                'receipts_party_number_unique'
            );
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unique(
                ['company_id', 'supplier_id', 'payment_no'],
                'payments_party_number_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropUnique('receipts_party_number_unique');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_party_number_unique');
        });
    }
};
