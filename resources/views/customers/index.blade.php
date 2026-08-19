@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">{{ __('إدارة الزبائن') }}</h1>
</div>

<div class="card">

    <h2>{{ __('إضافة زبون جديد') }}</h2>

    <form method="POST" action="/customers">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">

            <input type="text" name="name" placeholder="{{ __('اسم الزبون') }}" required>

            <input type="text" name="phone" placeholder="{{ __('رقم الهاتف') }}">

            <input type="text" name="company_name" placeholder="{{ __('اسم الشركة') }}">

            <input type="text" name="address" placeholder="{{ __('العنوان') }}">

        </div>

        <br>

        <textarea name="notes" rows="4" placeholder="{{ __('ملاحظات') }}"></textarea>

        <br><br>

        <button type="submit">{{ __('حفظ الزبون') }}</button>

    </form>

</div>

<div class="card">

    <h2>{{ __('قائمة الزبائن') }}</h2>
<input
    type="text"
    id="customerSearch"
    placeholder="{{ __('بحث عن زبون...') }}"
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
                        {{ __('حذف') }}
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