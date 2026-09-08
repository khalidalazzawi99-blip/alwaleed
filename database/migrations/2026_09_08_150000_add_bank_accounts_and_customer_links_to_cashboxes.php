<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashboxes', function (Blueprint $table) {
            $table->string('account_type', 20)->default('cash')->after('name');
            $table->string('bank_name')->nullable()->after('account_type');
            $table->string('account_number', 100)->nullable()->after('bank_name');
        });

        Schema::create('cashbox_customer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['cashbox_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbox_customer');
        Schema::table('cashboxes', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'bank_name', 'account_number']);
        });
    }
};
