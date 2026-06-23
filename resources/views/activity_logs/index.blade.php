```php
@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">سجل النشاطات</h1>
    <p style="color:#8A8178">
        جميع العمليات التي تمت داخل النظام
    </p>
</div>

<div class="card">

    <table>
        <thead>
            <tr>
                <th>المستخدم</th>
                <th>العملية</th>
                <th>التفاصيل</th>
                <th>التاريخ</th>
            </tr>
        </thead>

        <tbody>

        @foreach($logs as $log)

            <tr>
                <td>{{ $log->user_name }}</td>
                <td>{{ $log->action }}</td>
                <td>{{ $log->details }}</td>
                <td>{{ $log->created_at }}</td>
            </tr>

        @endforeach

        </tbody>

    </table>

</div>

@endsection
```
