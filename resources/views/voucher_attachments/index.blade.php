@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1 class="page-title">📎 مرفقات السندات</h1><p style="color:#8A8178">ربط الفواتير والصور والملفات بسندات القبض والصرف.</p></div></div>
@if(session('success'))<div class="card" style="color:#166534;background:#ECFDF3">{{ session('success') }}</div>@endif
@if($errors->any())<div class="card" style="color:#B91C1C">{{ $errors->first() }}</div>@endif
<div class="card"><h2>رفع مرفق</h2><form method="POST" enctype="multipart/form-data" action="/voucher-attachments" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">@csrf
<div><label>السند</label><select name="voucher_type" id="voucherType"><option value="receipt">سند قبض</option><option value="payment">سند صرف</option></select></div>
<div><label>رقم السند</label><select name="voucher_id" id="voucherId" required></select></div>
<div><label>الملف (حتى 10MB)</label><input type="file" name="attachment" required></div><button>رفع وربط</button></form></div>
<div class="card"><h2>المرفقات</h2><table><thead><tr><th>السند</th><th>اسم الملف</th><th>الحجم</th><th>التاريخ</th><th></th></tr></thead><tbody>
@forelse($attachments as $item)<tr><td>{{ $item->voucher_type === 'receipt' ? 'قبض' : 'صرف' }} #{{ $item->voucher_id }}</td><td>{{ $item->original_name }}</td><td>{{ number_format($item->size/1024,1) }} KB</td><td>{{ $item->created_at }}</td><td><a class="btn" href="{{ route('voucher-attachments.download',$item) }}">تنزيل</a><form method="POST" action="/voucher-attachments/{{ $item->id }}" style="display:inline">@csrf @method('DELETE')<button style="background:#B91C1C">حذف</button></form></td></tr>@empty<tr><td colspan="5">لا توجد مرفقات.</td></tr>@endforelse
</tbody></table></div>
<script>
const vouchers={receipt:@json($receipts->map(fn($v)=>['id'=>$v->id,'label'=>$v->receipt_no])->values()),payment:@json($payments->map(fn($v)=>['id'=>$v->id,'label'=>$v->payment_no])->values())};
function fillVouchers(){voucherId.innerHTML=vouchers[voucherType.value].map(v=>`<option value="${v.id}">${v.label}</option>`).join('')}voucherType.addEventListener('change',fillVouchers);fillVouchers();
</script>
@endsection
