@extends('layouts.app')
@section('content')
@include('daily_accounts._styles')
<div class="da">
<div class="da-head"><div><h1>تعديل المصروف</h1><p>الحسابات اليومية · أضواء سيبار</p></div></div>
@if($errors->any())<div class="da-alert da-errors" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form class="da-panel" method="post" action="{{ route('sippar.daily-accounts.update', $expense->id) }}">
@csrf @method('PUT')
@include('daily_accounts._form')
<div class="da-actions"><button class="da-primary" type="submit">حفظ التعديلات</button><a class="da-btn" href="{{ route('sippar.daily-accounts.index') }}">إلغاء</a></div>
</form>
</div>
@endsection
