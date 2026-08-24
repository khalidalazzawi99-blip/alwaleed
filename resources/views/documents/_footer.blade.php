@php
$footerCompany=\App\Models\Company::find($companyId??null);
$footerSetting=\App\Models\Setting::where('company_id',$companyId??null)->first();
$footerCompanyName=$footerSetting?->company_name?:($footerCompany?->name?:'Al Waleed');
@endphp
<div class="document-thanks">
    <div class="document-thanks-ornament"><span></span><b>&#10022;</b><span></span></div>
    <div class="document-thanks-title">شكراً لتعاونكم معنا</div>
    <div class="document-thanks-subtitle">نعتز بثقتكم ونسعد دائماً بخدمتكم</div>
</div>
<table class="document-footer"><tr><td>{{ $footerCompanyName }}</td><td>{{ $footerSetting?->address?:'-' }}</td><td>Al Waleed ERP</td></tr></table>
