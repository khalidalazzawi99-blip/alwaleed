<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('external_invoices', function (Blueprint $table) {
            $table->string('invoice_name')->nullable()->after('invoice_no');
            $table->string('order_no')->nullable()->after('invoice_name');
        });
        DB::table('external_invoices')->where('status', '!=', 'cancelled')->update(['status' => 'active']);

        DB::table('customers')->whereNull('external_customer_id')->orderBy('id')->each(function ($customer): void {
            $candidate = 'C-'.$customer->id;
            if (DB::table('customers')->where('company_id', $customer->company_id)->where('external_customer_id', $candidate)->exists()) {
                $candidate = 'C-'.$customer->id.'-'.$customer->company_id;
            }
            DB::table('customers')->where('id', $customer->id)->update(['external_customer_id' => $candidate]);
        });
    }

    public function down(): void
    {
        Schema::table('external_invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_name', 'order_no']);
        });
    }
};
