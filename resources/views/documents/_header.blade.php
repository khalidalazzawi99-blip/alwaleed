@php
$documentCompany = \App\Models\Company::find($companyId ?? null);
$documentSetting = \App\Models\Setting::where('company_id', $companyId ?? null)->first();
$documentCompanyName = $documentSetting?->company_name ?: ($documentCompany?->name ?: 'Al Waleed');
$documentPhone = $documentSetting?->phone ?: '-';
$documentEmail = $documentSetting?->email ?: '-';
$documentAddress = $documentSetting?->address ?: '-';
@endphp
<div class="top-gradient"></div>
<table class="document-header"><tr>
<td class="brand-cell">
    <img class="brand-logo" src="{{ app(\App\Services\CompanyBrandService::class)->logoDataUri($companyId ?? null) }}" alt="{{ $documentCompanyName }}">
    <span class="brand-copy">
        <span class="brand-name" dir="auto">{{ $documentCompanyName }}</span><br>
        <span class="muted brand-contact"><span>{{ $documentEmail }}</span><b>·</b><span>{{ $documentPhone }}</span></span>
    </span>
</td>
<td class="title-cell">
    <h1 class="document-title">{{ $documentTitle }}</h1>
    <span class="muted">تاريخ الإصدار: <span dir="ltr">{{ now()->format('Y/m/d H:i') }}</span></span>
</td>
</tr></table>
