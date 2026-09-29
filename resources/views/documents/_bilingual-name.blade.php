@php([$nameAr, $nameEn] = app(\App\Services\CompanyBrandService::class)->splitName($name ?? ''))
<span class="bilingual-name">
    @if($nameAr)<span class="bilingual-name-ar" dir="rtl">{{ $nameAr }}</span>@endif
    @if($nameEn)<span class="bilingual-name-en" dir="ltr">{{ $nameEn }}</span>@endif
    @if(!$nameAr && !$nameEn)<span dir="rtl">{{ $fallback ?? '-' }}</span>@endif
</span>
