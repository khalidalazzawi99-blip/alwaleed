<?php

namespace App\Exports;

use App\Services\DailyAccountsService;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DailyAccountsExcelExport implements Export, WithMultipleSheets
{
    public function __construct(private readonly DailyAccountsService $service) {}

    public function sheets(): array
    {
        $report = $this->service->report();

        return array_map(fn ($kind) => new DailyAccountsSheet($this->service, $report, $kind),
            ['daily', 'summary', 'people', 'months']);
    }
}
