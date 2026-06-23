<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function onlyAdmin()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'غير مسموح بالدخول');
        }
    }

    private function logActivity($action, $details = null)
    {
        ActivityLog::create([
            'user_name' => auth()->user()->name ?? 'System',
            'action' => $action,
            'details' => $details,
        ]);
    }

    public function index()
    {
        $this->onlyAdmin();

        return view('users.index', [
            'users' => User::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->onlyAdmin();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $this->logActivity(
            'إضافة موظف',
            'تمت إضافة الموظف: ' . $user->name . ' | الصلاحية: ' . $user->role
        );

        return redirect('/users');
    }

    public function update(Request $request, User $user)
    {
        $this->onlyAdmin();

        $oldName = $user->name;
        $oldEmail = $user->email;
        $oldRole = $user->role;

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        $details = 'تم تعديل الموظف: ' . $oldName;

        if ($oldName !== $user->name) {
            $details .= ' | الاسم من ' . $oldName . ' إلى ' . $user->name;
        }

        if ($oldEmail !== $user->email) {
            $details .= ' | البريد من ' . $oldEmail . ' إلى ' . $user->email;
        }

        if ($oldRole !== $user->role) {
            $details .= ' | الصلاحية من ' . $oldRole . ' إلى ' . $user->role;
        }

        if ($request->filled('password')) {
            $details .= ' | تم تغيير كلمة المرور';
        }

        $this->logActivity('تعديل موظف', $details);

        return redirect('/users');
    }

    public function destroy(User $user)
    {
        $this->onlyAdmin();

        if ($user->id === auth()->id()) {
            return back();
        }

        $deletedName = $user->name;
        $deletedEmail = $user->email;
        $deletedRole = $user->role;

        $user->delete();

        $this->logActivity(
            'حذف موظف',
            'تم حذف الموظف: ' . $deletedName . ' | البريد: ' . $deletedEmail . ' | الصلاحية: ' . $deletedRole
        );

        return redirect('/users');
    }
}