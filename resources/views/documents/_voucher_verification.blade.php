<table class="voucher-verification" dir="ltr"><tr>
    <td class="qr-cell"><img src="{{ $qrCode }}" alt="QR verification"></td>
    <td class="barcode-cell"><img src="{{ $barcode }}" alt="Code 128 barcode"><strong>{{ $documentNumber }}</strong><span dir="rtl">امسح الرمز للتحقق من صحة {{ $documentLabel }}</span></td>
</tr></table>
