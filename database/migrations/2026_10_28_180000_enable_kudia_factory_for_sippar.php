<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $companyIds = DB::table('companies')
            ->whereRaw('UPPER(code) = ?', [strtoupper(config('daily_accounts.company_code', 'SIPPAR'))])
            ->pluck('id');

        foreach ($companyIds as $companyId) {
            $feature = DB::table('company_features')
                ->where('company_id', $companyId)->where('feature_key', 'kudia_factory');
            if ($feature->exists()) {
                $feature->update(['enabled' => true, 'updated_at' => now()]);
            } else {
                DB::table('company_features')->insert([
                    'company_id' => $companyId, 'feature_key' => 'kudia_factory',
                    'enabled' => true, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep the owner's feature setting and existing invoice data on rollback.
    }
};
