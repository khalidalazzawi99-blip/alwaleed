<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', fn (Blueprint $table) => $table->softDeletes());
        Schema::table('cashboxes', function (Blueprint $table) {
            $table->softDeletes();
            $table->boolean('is_system_legacy')->default(false)->index();
        });
        Schema::table('daily_expenses', function (Blueprint $table) {
            // Kept as an indexed nullable reference so historical rows remain
            // untouched and SQLite does not rebuild this populated table.
            $table->unsignedBigInteger('cashbox_id')->nullable()->after('company_id');
            $table->index(['company_id', 'cashbox_id']);
        });

        // This exact name was created by the old automatic fallback. Keep the
        // row and all linked history, but retire it from operational use/sync.
        DB::table('cashboxes')->where('name', 'الصندوق الرئيسي')->update([
            'is_active' => false,
            'is_system_legacy' => true,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('daily_expenses', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'cashbox_id']);
            $table->dropColumn('cashbox_id');
        });
        Schema::table('cashboxes', function (Blueprint $table) {
            $table->dropColumn('is_system_legacy');
            $table->dropSoftDeletes();
        });
        Schema::table('customers', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
