<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | التحقق من صلاحية إدارة المستخدمين
    |--------------------------------------------------------------------------
    */
    private function canManageUsers()
    {
        if (
            !auth()->check() ||
            !in_array(auth()->user()->role, ['super_admin', 'admin'])
        ) {
            abort(403, __('غير مسموح بالدخول'));
        }
    }


    /*
    |--------------------------------------------------------------------------
    | قائمة المستخدمين
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $this->canManageUsers();

        /*
        | Super Admin يشوف كل المستخدمين
        */
        if (auth()->user()->role === 'super_admin') {

            $users = User::with('company')
                ->latest()
                ->get();

            $companies = Company::latest()
                ->get();

        } else {

            /*
            | مدير الشركة يشوف موظفي شركته فقط
            */
            $users = User::where(
                'company_id',
                auth()->user()->company_id
            )
            ->latest()
            ->get();

            $companies = collect();
        }

        return view('users.index', [
            'users' => $users,
            'companies' => $companies,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | إضافة مستخدم
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $this->canManageUsers();

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:6',
            ],

            'role' => [
                'required',
                'in:super_admin,admin,accountant,data_entry,viewer',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | تحديد الشركة
        |--------------------------------------------------------------------------
        */
        if (auth()->user()->role === 'super_admin') {

            $companyId = $request->company_id;

        } else {

            $companyId = auth()->user()->company_id;

            /*
            | مدير الشركة ما يگدر ينشئ Super Admin
            */
            if ($request->role === 'super_admin') {
                abort(403, __('غير مسموح بإنشاء مالك نظام'));
            }
        }


        /*
        |--------------------------------------------------------------------------
        | التحقق من الحد الأقصى للمستخدمين
        |--------------------------------------------------------------------------
        */
        if ($companyId) {

            $company = Company::findOrFail($companyId);

            $currentUsers = User::where(
                'company_id',
                $companyId
            )->count();

            if ($currentUsers >= $company->max_users) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'email' => __('تم الوصول إلى الحد الأقصى للمستخدمين لهذه الشركة.'),
                    ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | إنشاء المستخدم
        |--------------------------------------------------------------------------
        */
        $user = User::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(
                $request->password
            ),
            'role' => $request->role,
        ]);


        return redirect('/users')
            ->with(
                'success',
                __('تم إضافة المستخدم بنجاح')
            );
    }


    /*
    |--------------------------------------------------------------------------
    | تعديل المستخدم
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, User $user)
    {
        $this->canManageUsers();


        /*
        |--------------------------------------------------------------------------
        | مدير الشركة ما يگدر يعدل مستخدم شركة ثانية
        |--------------------------------------------------------------------------
        */
        if (
            auth()->user()->role !== 'super_admin' &&
            $user->company_id !== auth()->user()->company_id
        ) {
            abort(
                403,
                __('غير مسموح بتعديل هذا المستخدم')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | مدير الشركة ما يگدر يعدل Super Admin
        |--------------------------------------------------------------------------
        */
        if (
            auth()->user()->role !== 'super_admin' &&
            $user->role === 'super_admin'
        ) {
            abort(
                403,
                __('غير مسموح بتعديل مالك النظام')
            );
        }


        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],

            'role' => [
                'required',
                'in:super_admin,admin,accountant,data_entry,viewer',
            ],

            'password' => [
                'nullable',
                'string',
                'min:6',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | منع مدير الشركة من منح Super Admin
        |--------------------------------------------------------------------------
        */
        if (
            auth()->user()->role !== 'super_admin' &&
            $request->role === 'super_admin'
        ) {
            abort(
                403,
                __('غير مسموح بمنح صلاحية مالك النظام')
            );
        }


        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];


        /*
        |--------------------------------------------------------------------------
        | Super Admin فقط يگدر ينقل المستخدم بين الشركات
        |--------------------------------------------------------------------------
        */
        if (auth()->user()->role === 'super_admin') {
            $data['company_id'] = $request->company_id;
        }


        /*
        |--------------------------------------------------------------------------
        | تغيير كلمة المرور إذا تم إدخالها
        |--------------------------------------------------------------------------
        */
        if ($request->filled('password')) {

            $data['password'] = Hash::make(
                $request->password
            );
        }


        $user->update($data);


        return redirect('/users')
            ->with(
                'success',
                __('تم تحديث المستخدم بنجاح')
            );
    }


    /*
    |--------------------------------------------------------------------------
    | حذف المستخدم
    |--------------------------------------------------------------------------
    */
    public function destroy(User $user)
    {
        $this->canManageUsers();


        /*
        |--------------------------------------------------------------------------
        | منع المستخدم من حذف نفسه
        |--------------------------------------------------------------------------
        */
        if ($user->id === auth()->id()) {

            return back()->withErrors([
                'user' => __('لا يمكنك حذف حسابك الحالي.'),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | مدير الشركة ما يگدر يحذف مستخدم شركة ثانية
        |--------------------------------------------------------------------------
        */
        if (
            auth()->user()->role !== 'super_admin' &&
            $user->company_id !== auth()->user()->company_id
        ) {
            abort(
                403,
                __('غير مسموح بحذف هذا المستخدم')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | منع مدير الشركة من حذف Super Admin
        |--------------------------------------------------------------------------
        */
        if (
            auth()->user()->role !== 'super_admin' &&
            $user->role === 'super_admin'
        ) {
            abort(
                403,
                __('غير مسموح بحذف مالك النظام')
            );
        }


        $user->delete();


        return redirect('/users')
            ->with(
                'success',
                __('تم حذف المستخدم بنجاح')
            );
    }
}
