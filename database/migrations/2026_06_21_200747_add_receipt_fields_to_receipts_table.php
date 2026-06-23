<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {

            $table->string('receipt_no')->nullable();

            $table->date('receipt_date')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {

            $table->dropColumn('receipt_no');

            $table->dropColumn('receipt_date');

        });
    }
};