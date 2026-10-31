<style>
    @font-face { font-family: Tajawal; src: url("data:font/truetype;base64,{{ base64_encode(file_get_contents(public_path('fonts/Tajawal-Regular.ttf'))) }}") format('truetype'); font-weight: 400; }
    @font-face { font-family: Tajawal; src: url("data:font/truetype;base64,{{ base64_encode(file_get_contents(public_path('fonts/Tajawal-Bold.ttf'))) }}") format('truetype'); font-weight: 700; }
    @page { size: A4 portrait; margin: 12mm 8mm 35mm; }
    * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    body { margin: 0; padding: 0; background: #fff; color: #082348; font-family: 'DejaVu Sans', Tajawal, sans-serif; font-size: 14px; line-height: 1.35; }
    /* Do not reset html margins: DomPDF uses the root margins for @page. */
    .kudia-sheet { position: relative; direction: ltr; }
    .kudia-sheet table { width: 100%; direction: ltr; }
    .kudia-corner { position: absolute; top: -12mm; right: -8mm; width: 61mm; height: 53mm; }
    .kudia-brand { height: 43mm; border-collapse: collapse; position: relative; }
    .kudia-brand td { vertical-align: middle; padding: 0; }
    .kudia-brand .brand-logo-cell { width: 31%; }
    .kudia-logo { display: block; width: 26mm; height: 27mm; }
    .kudia-brand .brand-wordmark { font-size: 0; line-height: 0; }
    .brand-wordmark img { display: block; width: 63mm; height: 17.5mm; }
    .kudia-intro { border-collapse: collapse; margin: 17mm 0 7mm; }
    .kudia-intro td { vertical-align: top; padding: 0; }
    .kudia-heading { margin: 0; font-size: 0; line-height: 0; }
    .kudia-heading img { display: block; }
    .issued-date { margin: 4mm 0 0; font-size: 15px; }
    .statement-period { margin: 2mm 0 0; font-size: 12px; }
    .client-label { font-weight: 700; font-size: 15px; }
    .client-name { font-weight: 700; font-size: 21px; margin: 0 0 2mm; overflow-wrap: break-word; }
    .client-address { font-size: 16px; white-space: pre-line; }
    .kudia-grid { table-layout: fixed; border-collapse: separate; border-spacing: 0; }
    .kudia-grid thead { display: table-header-group; }
    .kudia-grid th { padding: 11px 6px; vertical-align: middle; color: #fff; background: #003046; font-size: 14px; font-weight: 700; text-align: center; }
    .kudia-grid th:first-child { border-radius: 24px 0 0 24px; }
    .kudia-grid th:last-child { border-radius: 0 24px 24px 0; }
    .kudia-grid tbody td { padding: 0; vertical-align: middle; text-align: center; overflow-wrap: break-word; }
    .kudia-grid tbody td + td { border-left: .6pt solid #8799b6; }
    .kudia-grid .cell { padding: 9px 6px; margin: 3px 0; min-height: 6mm; }
    .kudia-grid tr.stripe .cell { background: #dce3e6; }
    .kudia-grid tr.stripe td:first-child .cell { border-radius: 20px 0 0 20px; }
    .kudia-grid tr.stripe td:last-child .cell { border-radius: 0 20px 20px 0; }
    .kudia-grid .description { text-align: left; white-space: pre-line; }
    .kudia-grid .money { direction: ltr; white-space: nowrap; font-size: 13px; }
    .kudia-grid .reference-detail { display: block; font-size: 9px; color: #526580; }
    .kudia-grid tr { page-break-inside: avoid; }
    .kudia-settlement { margin-top: 9mm; border-collapse: collapse; page-break-inside: avoid; }
    .kudia-settlement > tbody > tr > td { vertical-align: top; padding: 0; }
    .kudia-payment-info { width: 55%; padding: 2mm 4mm !important; border-right: 1px solid #00ade7; }
    .kudia-payment-info strong { display: block; margin-bottom: 2mm; font-size: 15px; }
    .kudia-payment-info .payment-note { white-space: pre-line; font-size: 14px; }
    .kudia-totals { border-spacing: 0; border-collapse: separate; font-size: 15px; }
    .kudia-totals td { padding: 8px 7px; vertical-align: middle; text-align: right; }
    .kudia-totals td:first-child { width: 58%; }
    .kudia-totals td + td { border-left: .6pt solid #8799b6; white-space: nowrap; }
    .kudia-totals td:last-child { font-size: 14px; }
    .kudia-totals .grand td { background: #eaf7fe; font-weight: 700; font-size: 17px; }
    .kudia-totals .grand td:first-child { border-radius: 15px 0 0 15px; }
    .kudia-totals .grand td:last-child { border-radius: 0 15px 15px 0; }
    .kudia-totals .grand td:last-child { font-size: 15px; }
    .kudia-thanks { border-top: 1px solid #00ade7; padding-top: 5mm; margin: 8mm 4mm 0; text-align: center; font-size: 0; line-height: 0; page-break-inside: avoid; }
    .kudia-thanks img { display: block; width: 52mm; height: 6mm; margin: 0 auto; }
    .kudia-footer-art { position: fixed; bottom: -35mm; left: 75mm; width: 44mm; height: 38mm; }
    .print-actions { margin: 20px; text-align: center; }
    .print-actions button { padding: 12px 24px; border: 0; border-radius: 8px; background: #003046; color: white; font: 700 15px Tajawal; cursor: pointer; }
    .rtl-content { direction: rtl; text-align: right; }
    @media screen {
        body { background: #eaf0f4; }
        .kudia-sheet { width: 210mm; min-height: 297mm; padding: 12mm 8mm 35mm; margin: 20px auto; overflow: hidden; background: white; box-shadow: 0 15px 50px #c7d2dc; }
        .kudia-corner { top: 0; right: 0; }
        .kudia-footer-art { position: absolute; bottom: 0; left: 83mm; }
    }
    @media print { .print-actions { display: none; } }
</style>
