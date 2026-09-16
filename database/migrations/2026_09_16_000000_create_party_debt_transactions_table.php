<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_debt_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20); // borrowing | debt_payment
            $table->date('transaction_date');
            $table->decimal('amount', 18, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'transaction_date']);
            $table->index(['customer_id', 'transaction_date']);
            $table->index(['supplier_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_debt_transactions');
    }
};
