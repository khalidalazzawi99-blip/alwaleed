@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">إدارة الموردين</h1>
</div>

<div class="card">
    <h2>إضافة مورد جديد</h2>

    <form method="POST" action="/suppliers">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
            <input type="text" name="name" placeholder="اسم المورد" required>
            <input type="text" name="phone" placeholder="رقم الهاتف">
            <input type="text" name="company_name" placeholder="اسم الشركة">
            <input type="text" name="address" placeholder="العنوان">
        </div>

        <br>

        <textarea name="notes" rows="4" placeholder="ملاحظات"></textarea>

        <br><br>

        <button type="submit">حفظ المورد</button>
    </form>
</div>

<div class="card">
    <h2>قائمة الموردين</h2>
<input
    type="text"
    id="supplierSearch"
    placeholder="بحث عن مورد..."
    style="margin-bottom:20px">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الهاتف</th>
                <th>الشركة</th>
                <th>العنوان</th>
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