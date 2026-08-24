@php
$documentCompany=\App\Models\Company::find($companyId??null);
$documentSetting=\App\Models\Setting::where('company_id',$companyId??null)->first();
$documentCompanyName=$documentSetting?->company_name?:($documentCompany?->name?:'Al Waleed');
$documentPhone=$documentSetting?->phone?:'-';$documentEmail=$documentSetting?->email?:'-';$documentAddress=$documentSetting?->address?:'-';
@endphp
<div class="top-gradient"></div><table class="document-header"><tr><td class="brand-cell"><img class="brand-logo" src="{{ app(\App\Services\CompanyBrandService::class)->logoDataUri($companyId??null) }}" alt="{{ $documentCompanyName }}"><span class="brand-copy"><span class="brand-name">{{ $documentCompanyName }}</span><br><span class="muted">{{ $documentEmail }} · {{ $documentPhone }}</span></span></td><td class="title-cell"><h1 class="document-title">{{ $documentTitle }}</h1><span class="muted">تاريخ الإصدار: {{ now()->format('Y/m/d H:i') }}</span></td></tr></table>
