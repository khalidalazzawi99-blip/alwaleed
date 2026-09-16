<?php

namespace App\Services;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Picqer\Barcode\Renderers\SvgRenderer;
use Picqer\Barcode\Types\TypeCode128;

class VoucherCodeService
{
    public function qrDataUri(string $verificationUrl): string
    {
        $qr = new QrCode(
            data: $verificationUrl,
            encoding: new Encoding('ISO-8859-1'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 260,
            margin: 12,
        );

        return (new PngWriter)->write($qr)->getDataUri();
    }

    public function barcodeDataUri(string $number): string
    {
        $barcode = (new TypeCode128)->getBarcode($number);
        $renderer = new SvgRenderer;
        $renderer->setSvgType(SvgRenderer::TYPE_SVG_STANDALONE);
        $svg = $renderer->render($barcode, max(360, $barcode->getWidth() * 2), 72);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
