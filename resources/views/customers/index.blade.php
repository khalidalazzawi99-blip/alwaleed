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
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin:12px 0 16px">
        <a href="{{ url('/customers') }}" class="btn" style="{{ $debtsOnly ? '' : 'background:#CDBA9E;color:#17233a' }}">{{ __('كل الزبائن') }}</a>
        <a href="{{ url('/customers?debts_only=1') }}" class="btn" style="{{ $debtsOnly ? 'background:#CDBA9E;color:#17233a' : '' }}">{{ __('المستحقين') }}</a>
        <a href="{{ route('customers.debtors.pdf') }}" class="btn">{{ __('PDF المستحقين') }}</a>
    </div>
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
            <th>{{ __('messages.remaining_amount') }}</th>
            <th>{{ __('messages.paid_amount') }}</th>
            <th>{{ __('الهاتف') }}</th>
            <th>{{ __('الشركة') }}</th>
            <th>{{ __('العنوان') }}</th>
            <th>{{ __('إجراء') }}</th>
        </tr>
        </thead>

        <tbody>

        @forelse($customers as $customer)

        <tr>
            <td>{{ $customer->id }}</td>
            <td>
    <a href="/customers/{{ $customer->id }}"
       style="font-weight:700;text-decoration:none;color:#CDBA9E">
        {{ $customer->name }}
    </a>
</td>
            <td style="white-space:nowrap;font-weight:800"><bdi>{{ number_format($customer->remaining_amount, 2) }}</bdi> {{ $companyCurrency }}</td>
            <td style="white-space:nowrap;font-weight:700"><bdi>{{ number_format($customer->paid_amount, 2) }}</bdi> {{ $companyCurrency }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ $customer->company_name }}</td>
            <td>{{ $customer->address }}</td>

            <td>
                <div style="display:flex;gap:8px;align-items:center">
                <a href="/customers/{{ $customer->id }}/edit" class="btn">{{ __('تعديل') }}</a>
                <form method="POST" action="/customers/{{ $customer->id }}" style="margin:0">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="danger">
                        {{ __('حذف') }}
                    </button>
                </form>
                </div>
            </td>
        </tr>

        @empty
        <tr><td colspan="8" style="padding:24px;text-align:center;color:#8b94a4">{{ __('لا يوجد زبائن عليهم مبالغ متبقية') }}</td></tr>
        @endforelse

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
