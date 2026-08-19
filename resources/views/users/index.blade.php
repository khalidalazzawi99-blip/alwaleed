@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">
        @if(auth()->user()->role == 'super_admin')
            {{ __('مستخدمو النظام') }}
        @else
            {{ __('موظفو الشركة') }}
        @endif
    </h1>

    <p style="color:#8A8178">{{ __('إضافة وتعديل المستخدمين والصلاحيات') }}</p>
</div>

<div class="card">
    <h2>{{ __('إضافة مستخدم جديد') }}</h2>

    <form method="POST" action="/users">
        @csrf

        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:15px">

            <input type="text" name="name" placeholder="{{ __('اسم المستخدم') }}" required>

            <input type="email" name="email" placeholder="{{ __('البريد الإلكتروني') }}" required>

            <input type="password" name="password" placeholder="{{ __('كلمة المرور') }}" required>

            @if(auth()->user()->role == 'super_admin')
                <select name="company_id">
                    <option value="">{{ __('بدون شركة / مالك النظام') }}</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="role" required>
                @if(auth()->user()->role == 'super_admin')
                    <option value="super_admin">{{ __('مالك النظام') }}</option>
                @endif

                <option value="admin">{{ __('مدير الشركة') }}</option>
                <option value="accountant">{{ __('محاسب') }}</option>
                <option value="data_entry">{{ __('إدخال بيانات') }}</option>
                <option value="viewer">{{ __('مشاهدة فقط') }}</option>
            </select>

        </div>

        <br>

        <button type="submit">{{ __('إضافة المستخدم') }}</button>
    </form>
</div>

<div class="card">
    <h2>{{ __('قائمة المستخدمين') }}</h2>

    <table>
        <thead>
            <tr>
                <th>{{ __('الاسم') }}</th>
                <th>{{ __('البريد') }}</th>

                @if(auth()->user()->role == 'super_admin')
                    <th>{{ __('الشركة') }}</th>
                @endif

                <th>{{ __('الصلاحية') }}</th>
                <th>{{ __('كلمة مرور جديدة') }}</th>
                <th>{{ __('إجراءات') }}</th>
            </tr>
        </thead>

        <tbody>
        @foreach($users as $user)
            <tr>
                <form method="POST" action="/users/{{ $user->id }}">
                    @csrf
                    @method('PUT')

                    <td>
                        <input type="text" name="name" value="{{ $user->name }}" required>
                    </td>

                    <td>
                        <input type="email" name="email" value="{{ $user->email }}" required>
                    </td>

                    @if(auth()->user()->role == 'super_admin')
                        <td>
                            <select name="company_id">
                                <option value="">{{ __('بدون شركة') }}</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $user->company_id == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    @endif

                    <td>
                        <select name="role" required>
                            @if(auth()->user()->role == 'super_admin')
                                <option value="super_admin" {{ $user->role == 'super_admin' ? 'selected' : '' }}>
                                    {{ __('مالك النظام') }}
                                </option>
                            @endif

                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>{{ __('مدير الشركة') }}</option>
                            <option value="accountant" {{ $user->role == 'accountant' ? 'selected' : '' }}>{{ __('محاسب') }}</option>
                            <option value="data_entry" {{ $user->role == 'data_entry' ? 'selected' : '' }}>{{ __('إدخال بيانات') }}</option>
                            <option value="viewer" {{ $user->role == 'viewer' ? 'selected' : '' }}>{{ __('مشاهدة فقط') }}</option>
                        </select>
                    </td>

                    <td>
                        <input type="password" name="password" placeholder="{{ __('اتركه فارغ إذا ما تريد تغييرها') }}">
                    </td>

                    <td>
                        <button type="submit" class="success" style="margin-left:8px">
                            {{ __('حفظ') }}
                        </button>
                </form>

                        @if($user->id !== auth()->id())
                            <form method="POST" action="/users/{{ $user->id }}" style="display:inline">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="danger"
                                        style="background:#DC2626"
                                        onclick="return confirm(@js(__('هل تريد حذف هذا المستخدم؟')))"
                                    {{ __('حذف') }}
                                </button>
                            </form>
                        @endif
                    </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection
