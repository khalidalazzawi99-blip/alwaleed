<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $companyIds = DB::table('companies')->orderBy('id')->pluck('id');

        if ($companyIds->count() !== 1) {
            return;
        }

        $companyId = $companyIds->first();
        $companyAlreadyHasSettings = DB::table('settings')->where('company_id', $companyId)->exists();

        if (! $companyAlreadyHasSettings) {
            $legacySettingId = DB::table('settings')->whereNull('company_id')->orderBy('id')->value('id');

            if ($legacySettingId) {
                DB::table('settings')->where('id', $legacySettingId)->update(['company_id' => $companyId]);
            }
        }
    }

    public function down(): void
    {
        // Intentionally left unchanged: the original company association is not
        // recoverable with certainty and must never be erased on rollback.
    }
};
