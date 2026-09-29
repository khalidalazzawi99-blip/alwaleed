@php($documentBrand = app(\App\Services\CompanyBrandService::class)->profile($companyId ?? null))
<div class="top-gradient"></div>
<table class="document-header" dir="ltr"><tr>
<td class="title-cell" dir="rtl">
    <h1 class="document-title">{{ $documentTitle }}</h1>
    <span class="muted">تاريخ الإصدار: <span dir="ltr">{{ now()->format('Y/m/d H:i') }}</span></span>
</td>
<td class="brand-cell" dir="rtl">
    @if($documentBrand['logo'])<img class="brand-logo" src="{{ $documentBrand['logo'] }}" alt="">@endif
    <span class="brand-copy">
        @if($documentBrand['name_ar'])<span class="brand-name brand-name-ar" dir="rtl">{{ $documentBrand['name_ar'] }}</span>@endif
        @if($documentBrand['name_en'])<span class="brand-name brand-name-en" dir="ltr">{{ $documentBrand['name_en'] }}</span>@endif
        @if($documentBrand['phone'])<br><span class="muted brand-contact" dir="ltr">{{ $documentBrand['phone'] }}</span>@endif
    </span>
</td>
</tr></table>
