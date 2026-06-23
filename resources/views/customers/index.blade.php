@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">إدارة الزبائن</h1>
</div>

<div class="card">

    <h2>إضافة زبون جديد</h2>

    <form method="POST" action="/customers">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">

            <input type="text" name="name" placeholder="اسم الزبون" required>

            <input type="text" name="phone" placeholder="رقم الهاتف">

            <input type="text" name="company_name" placeholder="اسم الشركة">

            <input type="text" name="address" placeholder="العنوان">

        </div>

        <br>

        <textarea name="notes" rows="4" placeholder="ملاحظات"></textarea>

        <br><br>

        <button type="submit">حفظ الزبون</button>

    </form>

</div>

<div class="card">

    <h2>قائمة الزبائن</h2>
<input
    type="text"
    id="customerSearch"
    placeholder="بحث عن زبون..."
    style="margin-bottom:20px">
    <table>

        <thead>
        <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>الهاتف</th>
            <th>الشركة</th>
            <th>العنوان</th>
            <th>إجراء</th>
        </tr>
        </thead>

        <tbody>

        @foreach($customers as $customer)

        <tr>
            <td>{{ $customer->id }}</td>
            <td>
    <a href="/customers/{{ $customer->id }}"
       style="font-weight:700;text-decoration:none;color:#CDBA9E">
        {{ $customer->name }}
    </a>
</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ $customer->company_name }}</td>
            <td>{{ $customer->address }}</td>

            <td>
                <form method="POST" action="/customers/{{ $customer->id }}">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="danger">
                        حذف
                    </button>
                </form>
            </td>
        </tr>

        @endforeach

        </tbody>

    </table>

</div>
<script>
document.getElementById('customerSearch').addEventListener('keyup', function () {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr');

    rows.forEach(function (row) {
        row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
    });
});
</script>
@endsection