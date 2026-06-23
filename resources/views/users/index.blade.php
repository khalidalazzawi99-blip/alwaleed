@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">إدارة الموظفين</h1>
    <p style="color:#8A8178">إضافة وتعديل صلاحيات الموظفين</p>
</div>

<div class="card">
    <h2>إضافة موظف جديد</h2>

    <form method="POST" action="/users">
        @csrf

        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:15px">
            <input type="text" name="name" placeholder="اسم الموظف" required>
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>

            <select name="role" required>
                <option value="admin">مدير</option>
                <option value="accountant">محاسب</option>
                <option value="data_entry">إدخال بيانات</option>
                <option value="viewer">مشاهدة فقط</option>
            </select>
        </div>

        <br>

        <button type="submit">إضافة الموظف</button>
    </form>
</div>

<div class="card">
    <h2>قائمة الموظفين</h2>

    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>البريد</th>
                <th>الصلاحية</th>
                <th>كلمة مرور جديدة</th>
                <th>إجراءات</th>
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

                    <td>
                        <select name="role" required>
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>مدير</option>
                            <option value="accountant" {{ $user->role == 'accountant' ? 'selected' : '' }}>محاسب</option>
                            <option value="data_entry" {{ $user->role == 'data_entry' ? 'selected' : '' }}>إدخال بيانات</option>
                            <option value="viewer" {{ $user->role == 'viewer' ? 'selected' : '' }}>مشاهدة فقط</option>
                        </select>
                    </td>

                    <td>
                        <input type="password" name="password" placeholder="اتركه فارغ إذا ما تريد تغييرها">
                    </td>

                    <td>
                        <button type="submit" class="success" style="margin-left:8px">
                            حفظ
                        </button>
                </form>

                        @if($user->id !== auth()->id())
                            <form method="POST" action="/users/{{ $user->id }}" style="display:inline">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="danger"
                                        style="background:#DC2626"
                                        onclick="return confirm('هل تريد حذف هذا الموظف؟')">
                                    حذف
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