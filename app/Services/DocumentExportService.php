<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class DocumentExportService
{
    public function pdf(string $view, array $data, string $filename, string $orientation = 'portrait'): Response
    {
        $safeFilename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?: 'export.pdf';

        return Pdf::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
        ])->loadView($view, $data + ['pdfMode' => true])
            ->setPaper('a4', $orientation)
            ->download($safeFilename);
    }
}
