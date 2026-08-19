<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('external_customer_id')->nullable()->after('company_id');
            $table->unique(['company_id', 'external_customer_id'], 'customers_company_external_unique');
        });

        DB::table('customer_external_links')->orderBy('id')->each(function ($link) {
            DB::table('customers')->where('id', $link->customer_id)->where('company_id', $link->company_id)
                ->whereNull('external_customer_id')->update(['external_customer_id' => $link->external_customer_id]);
        });

        Schema::table('external_invoices', function (Blueprint $table) {
            $table->dropUnique('external_invoice_identity_unique');
            $table->renameColumn('invoice_number', 'invoice_no');
            $table->renameColumn('total', 'amount');
            $table->unique(['company_id', 'external_invoice_id'], 'external_invoices_company_external_unique');
        });

        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('direction', 20)->default('inbound');
            $table->string('event_type', 80);
            $table->string('external_invoice_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('status', 30);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('error_message', 1000)->nullable();
            $table->timestamps();
            $table->index(['company_id', 'created_at']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
        Schema::table('external_invoices', function (Blueprint $table) {
            $table->dropUnique('external_invoices_company_external_unique');
            $table->renameColumn('invoice_no', 'invoice_number');
            $table->renameColumn('amount', 'total');
            $table->unique(['company_id', 'provider', 'external_invoice_id'], 'external_invoice_identity_unique');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_company_external_unique');
            $table->dropColumn('external_customer_id');
        });
    }
};
