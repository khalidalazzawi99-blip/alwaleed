<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->foreignId('cashbox_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->index(['company_id', 'cashbox_id']);
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('cashbox_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->index(['company_id', 'cashbox_id']);
        });
        Schema::table('cashbox_logs', function (Blueprint $table) {
            $table->foreignId('cashbox_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->index(['company_id', 'cashbox_id']);
        });

        DB::table('companies')->orderBy('id')->each(function ($company) {
            $cashboxId = DB::table('cashboxes')->where('company_id', $company->id)->orderBy('id')->value('id');
            if (! $cashboxId) {
                return;
            }
            DB::table('receipts')->where('company_id', $company->id)->whereNull('cashbox_id')->update(['cashbox_id' => $cashboxId]);
            DB::table('payments')->where('company_id', $company->id)->whereNull('cashbox_id')->update(['cashbox_id' => $cashboxId]);
            DB::table('cashbox_logs')->where('company_id', $company->id)->whereNull('cashbox_id')->update(['cashbox_id' => $cashboxId]);
        });
    }

    public function down(): void
    {
        Schema::table('cashbox_logs', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'cashbox_id']);
            $table->dropConstrainedForeignId('cashbox_id');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'cashbox_id']);
            $table->dropConstrainedForeignId('cashbox_id');
        });
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'cashbox_id']);
            $table->dropConstrainedForeignId('cashbox_id');
        });
    }
};
