<?php

namespace App\Services;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;

class DailyAccountsPdf
{
    public function download(DailyAccountsService $service)
    {
        $report = $service->report();
        $html = view('daily_accounts.pdf', $report + [
            'expenses' => $service->query()->with('party')->orderBy('expense_date')->orderBy('id')->lazy(500),
        ])->render();
        $arabic = new Arabic;
        $html = preg_replace_callback('/(?<=>)([^<]*[\\x{0600}-\\x{06FF}][^<]*)(?=<)/u',
            fn ($match) => htmlspecialchars($arabic->utf8Glyphs(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8'), 1000, false), ENT_QUOTES, 'UTF-8'), $html) ?? $html;
        $pdf = Pdf::setOptions([
            'defaultFont' => 'Tajawal', 'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true, 'isPhpEnabled' => false,
        ]);
        $pdf->loadHTML($html, 'UTF-8')->setPaper('a4', 'landscape');
        $pdf->render();
        $canvas = $pdf->getDomPDF()->getCanvas();
        $font = $pdf->getDomPDF()->getFontMetrics()->getFont('Tajawal', 'normal');
        $width = $canvas->get_width();
        $height = $canvas->get_height();
        $canvas->line(28, $height - 28, $width - 28, $height - 28, [0.88, 0.84, 0.78], 0.5);
        $canvas->page_text(28, $height - 20, 'SIPPAR LIGHTS  |  DAILY ACCOUNTS', $font, 7, [0.50, 0.54, 0.61]);
        $canvas->page_text($width - 72, $height - 20, '{PAGE_NUM} / {PAGE_COUNT}', $font, 8, [0.50, 0.54, 0.61]);

        return $pdf->download($service->filename('pdf'));
    }
}
