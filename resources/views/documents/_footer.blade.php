@php
$footerCompany=\App\Models\Company::find($companyId??null);
$footerSetting=\App\Models\Setting::where('company_id',$companyId??null)->first();
$footerCompanyName=$footerSetting?->company_name?:($footerCompany?->name?:'Al Waleed');
@endphp
<table class="document-footer"><tr><td>{{ $footerCompanyName }}</td><td>{{ $footerSetting?->address?:'-' }}</td><td>Al Waleed ERP</td></tr></table>
