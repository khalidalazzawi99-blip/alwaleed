<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashbox_logs', function (Blueprint $table) {
            $table->date('transaction_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cashbox_logs', function (Blueprint $table) {
            $table->dropColumn('transaction_date');
        });
    }
};
