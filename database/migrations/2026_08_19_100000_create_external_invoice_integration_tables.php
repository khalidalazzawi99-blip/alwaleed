<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('external_invoice_integrations', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('default'); $table->string('base_url')->nullable();
            $table->text('api_key')->nullable(); $table->boolean('enabled')->default(true);
            $table->json('settings')->nullable(); $table->timestamp('last_sync_at')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'provider']);
        });

        Schema::create('customer_external_links', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('default'); $table->string('external_customer_id'); $table->timestamps();
            $table->unique(['company_id', 'provider', 'external_customer_id'], 'customer_external_identity_unique');
            $table->unique(['customer_id', 'provider'], 'customer_provider_unique');
        });

        Schema::create('external_invoices', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('default'); $table->string('external_invoice_id');
            $table->string('external_customer_id'); $table->string('invoice_number');
            $table->date('invoice_date'); $table->date('due_date')->nullable(); $table->string('currency', 3)->default('IQD');
            $table->decimal('subtotal', 18, 2)->default(0); $table->decimal('total', 18, 2);
            $table->decimal('paid_amount', 18, 2)->default(0); $table->string('status', 30)->default('unpaid');
            $table->text('notes')->nullable(); $table->json('payload')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'provider', 'external_invoice_id'], 'external_invoice_identity_unique');
            $table->index(['company_id', 'customer_id', 'invoice_date']);
        });

        Schema::create('company_api_tokens', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->string('token_hash', 64)->unique(); $table->string('token_prefix', 16);
            $table->json('scopes'); $table->timestamp('last_used_at')->nullable(); $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable(); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_api_tokens'); Schema::dropIfExists('external_invoices');
        Schema::dropIfExists('customer_external_links'); Schema::dropIfExists('external_invoice_integrations');
    }
};
