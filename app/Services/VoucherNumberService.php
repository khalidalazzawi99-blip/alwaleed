<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class VoucherNumberService
{
    public function next(int $companyId, string $type, int $year): string
    {
        $prefix = $type === 'receipt' ? 'RCP' : 'PAY';

        DB::table('voucher_sequences')->insertOrIgnore([
            'company_id' => $companyId,
            'document_type' => $type,
            'year' => $year,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = DB::table('voucher_sequences')
            ->where('company_id', $companyId)
            ->where('document_type', $type)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        $next = ((int) $sequence->last_number) + 1;
        DB::table('voucher_sequences')->where('id', $sequence->id)->update([
            'last_number' => $next,
            'updated_at' => now(),
        ]);

        return sprintf('%s-%d-%06d', $prefix, $year, $next);
    }

    public function preview(int $companyId, string $type, int $year): string
    {
        $last = (int) DB::table('voucher_sequences')
            ->where('company_id', $companyId)
            ->where('document_type', $type)
            ->where('year', $year)
            ->value('last_number');

        return sprintf('%s-%d-%06d', $type === 'receipt' ? 'RCP' : 'PAY', $year, $last + 1);
    }
}
