<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('daily_expenses')
                ->whereNotNull('cashbox_id')
                ->selectRaw('cashbox_id, SUM(amount) as total_amount')
                ->groupBy('cashbox_id')
                ->get()
                ->each(function ($row): void {
                    DB::table('cashboxes')->where('id', $row->cashbox_id)->increment('balance', $row->total_amount);
                });

            DB::table('daily_expenses')->whereNotNull('cashbox_id')->update(['cashbox_id' => null]);
            DB::table('cashbox_logs')->where('reference_no', 'like', 'DAILY-%')->delete();
        });
    }

    public function down(): void
    {
        // Daily expenses intentionally remain independent from bank accounts.
    }
};
