<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('integration_id', 28)->nullable()->after('company_id');
            $table->unique('integration_id', 'customers_integration_id_unique');
        });

        Schema::table('cashboxes', function (Blueprint $table) {
            $table->string('integration_id', 28)->nullable()->after('company_id');
            $table->unique('integration_id', 'cashboxes_integration_id_unique');
        });

        DB::table('customers')->whereNull('integration_id')->orderBy('id')->each(function ($customer): void {
            $integrationId = 'C-'.Str::ulid();
            DB::table('customers')->where('id', $customer->id)->update(['integration_id' => $integrationId]);
            DB::table('external_invoices')->where('customer_id', $customer->id)
                ->where('company_id', $customer->company_id)->update(['external_customer_id' => $integrationId]);
        });

        DB::table('cashboxes')->whereNull('integration_id')->orderBy('id')->each(function ($cashbox): void {
            DB::table('cashboxes')->where('id', $cashbox->id)->update(['integration_id' => 'B-'.Str::ulid()]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('integration_id', 28)->nullable(false)->change();
        });
        Schema::table('cashboxes', function (Blueprint $table) {
            $table->string('integration_id', 28)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('cashboxes', function (Blueprint $table) {
            $table->dropUnique('cashboxes_integration_id_unique');
            $table->dropColumn('integration_id');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_integration_id_unique');
            $table->dropColumn('integration_id');
        });
    }
};
