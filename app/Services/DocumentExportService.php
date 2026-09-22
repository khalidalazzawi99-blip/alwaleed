<?php

namespace App\Services;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class DocumentExportService
{
    public function pdf(string $view, array $data, string $filename, string $orientation = 'portrait'): Response
    {
        $safeFilename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?: 'export.pdf';
        $html = view($view, $data + ['pdfMode' => true])->render();
        $arabic = new Arabic();

        // DomPDF does not perform Arabic joining/bidi shaping. Shape only visible
        // text nodes so markup, CSS and attributes remain untouched.
        $html = preg_replace_callback(
            '/(?<=>)([^<]*[\x{0600}-\x{06FF}][^<]*)(?=<)/u',
            fn (array $match): string => $arabic->utf8Glyphs($match[1], 1000, false),
            $html
        ) ?? $html;

        $pdf = Pdf::setOptions([
            'defaultFont' => 'Tajawal',
            'defaultMediaType' => 'print',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
        ])->loadHTML($html, 'UTF-8')
            ->setPaper('a4', $orientation);

        $pdf->render();

        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('Tajawal', 'normal');
        $width = $canvas->get_width();
        $height = $canvas->get_height();

        // Keep page metadata outside the document flow so long tables have a
        // consistent footer without pushing their final row to another page.
        $canvas->line(28, $height - 28, $width - 28, $height - 28, [0.88, 0.84, 0.78], 0.5);
        $canvas->page_text(28, $height - 20, 'AL WALEED ERP', $font, 7, [0.50, 0.54, 0.61]);
        $canvas->page_text($width - 72, $height - 20, '{PAGE_NUM} / {PAGE_COUNT}', $font, 8, [0.50, 0.54, 0.61]);

        return $pdf->download($safeFilename);
    }
}
