<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_expense_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'name']);
            $table->unique(['company_id', 'id']);
        });
        Schema::create('daily_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->date('expense_date')->index();
            $table->decimal('amount', 18, 2);
            $table->string('currency', 3)->default('IQD')->index();
            $table->unsignedBigInteger('party_id')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['company_id', 'expense_date']);
            $table->index(['company_id', 'currency', 'expense_date']);
            $table->foreign(['company_id', 'party_id'])->references(['company_id', 'id'])
                ->on('daily_expense_parties')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_expenses');
        Schema::dropIfExists('daily_expense_parties');
    }
};
