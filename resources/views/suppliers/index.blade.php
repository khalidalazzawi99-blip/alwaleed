@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">{{ __('إدارة الموردين') }}</h1>
</div>

<div class="card">
    <h2>{{ __('إضافة مورد جديد') }}</h2>

    <form method="POST" action="/suppliers">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
            <input type="text" name="name" placeholder="{{ __('اسم المورد') }}" required>
            <input type="text" name="phone" placeholder="{{ __('رقم الهاتف') }}">
            <input type="text" name="company_name" placeholder="{{ __('اسم الشركة') }}">
            <input type="text" name="address" placeholder="{{ __('العنوان') }}">
        </div>

        <br>

        <textarea name="notes" rows="4" placeholder="{{ __('ملاحظات') }}"></textarea>

        <br><br>

        <button type="submit">{{ __('حفظ المورد') }}</button>
    </form>
</div>

<div class="card">
    <h2>{{ __('قائمة الموردين') }}</h2>
<input
    type="text"
    id="supplierSearch"
    placeholder="{{ __('بحث عن مورد...') }}"
    style="margin-bottom:20px">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('الاسم') }}</th>
                <th>{{ __('الهاتف') }}</th>
                <th>{{ __('الشركة') }}</th>
                <th>{{ __('العنوان') }}</th>
                <th>{{ __('إجراء') }}</th>
            </tr>
        </thead>

        <tbody>
            @foreach($suppliers as $supplier)
            <tr>
                <td>{{ $supplier->id }}</td>
                <td>
    <a href="/suppliers/{{ $supplier->id }}"
       style="font-weight:700;text-decoration:none;color:#CDBA9E">
        {{ $supplier->name }}
    </a>
</td>
                <td>{{ $supplier->phone }}</td>
                <td>{{ $supplier->company_name }}</td>
                <td>{{ $supplier->address }}</td>
                <td><a href="/suppliers/{{ $supplier->id }}/edit" class="btn">{{ __('تعديل') }}</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
document.getElementById('supplierSearch').addEventListener('keyup', function () {

    let value = this.value.toLowerCase();

    let rows = document.querySelectorAll('tbody tr');

    rows.forEach(function(row){

        row.style.display =
            row.innerText.toLowerCase().includes(value)
            ? ''
            : 'none';

    });

});
</script>
@endsection
