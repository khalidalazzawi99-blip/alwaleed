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

        return Pdf::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'defaultMediaType' => 'print',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
        ])->loadHTML($html, 'UTF-8')
            ->setPaper('a4', $orientation)
            ->download($safeFilename);
    }
}
