<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | قائمة الشركات
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $companies = Company::withCount('users')
            ->latest()
            ->get();

        return view('companies.index', compact('companies'));
    }

    /*
    |--------------------------------------------------------------------------
    | صفحة إضافة شركة
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('companies.create');
    }

    /*
    |--------------------------------------------------------------------------
    | إنشاء شركة + مدير الشركة
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:100',
                'unique:companies,code',
            ],

            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],

            'manager_name' => ['required', 'string', 'max:255'],

            'manager_email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'manager_password' => [
                'required',
                'string',
                'min:6',
            ],

            'subscription_days' => [
                'required',
                'integer',
                'min:1',
            ],

            'max_users' => [
                'required',
                'integer',
                'min:1',
            ],

            'status' => [
                'required',
                'in:active,inactive,expired',
            ],
        ]);

        DB::transaction(function () use ($request) {

            $startDate = now();

            $endDate = now()
                ->copy()
                ->addDays((int) $request->subscription_days);

            $company = Company::create([
                'name' => $request->name,
                'code' => strtoupper(trim($request->code)),
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,

                'subscription_start' => $startDate,
                'subscription_end' => $endDate,

                'status' => $request->status,
                'max_users' => $request->max_users,
            ]);

            User::create([
                'company_id' => $company->id,
                'name' => $request->manager_name,
                'email' => $request->manager_email,
                'password' => Hash::make($request->manager_password),
                'role' => 'admin',
            ]);
        });

        return redirect('/admin/companies')
            ->with('success', __('تم إنشاء الشركة ومديرها بنجاح'));
    }

    /*
    |--------------------------------------------------------------------------
    | تفاصيل الشركة
    |--------------------------------------------------------------------------
    */
    public function show(Company $company)
    {
        $company->load(['users', 'features']);

        $manager = $company->users
            ->where('role', 'admin')
            ->first();

        $daysLeft = null;

        if ($company->subscription_end) {
            $daysLeft = now()
                ->startOfDay()
                ->diffInDays(
                    \Carbon\Carbon::parse(
                        $company->subscription_end
                    )->startOfDay(),
                    false
                );
        }

        return view('companies.show', [
            'company' => $company,
            'manager' => $manager,
            'daysLeft' => $daysLeft,
            'featureModules' => collect(config('features.modules'))->map(function ($module, $key) use ($company) {
                return $module + ['key' => $key, 'enabled' => $company->hasFeature($key)];
            })->values(),
        ]);
    }

    public function updateFeature(Request $request, Company $company)
    {
        $allowed = array_keys(config('features.modules', []));

        $data = $request->validate([
            'feature_key' => ['required', 'string', \Illuminate\Validation\Rule::in($allowed)],
            'enabled' => ['required', 'boolean'],
        ]);

        $feature = $company->features()->updateOrCreate(
            ['feature_key' => $data['feature_key']],
            ['enabled' => $data['enabled']]
        );

        return response()->json([
            'message' => __('messages.feature_updated'),
            'enabled' => $feature->enabled,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | صفحة تعديل الشركة
    |--------------------------------------------------------------------------
    */
    public function edit(Company $company)
    {
        $company->load('users');

        $manager = $company->users
            ->where('role', 'admin')
            ->first();

        return view('companies.edit', [
            'company' => $company,
            'manager' => $manager,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | تحديث بيانات الشركة + المدير
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Company $company)
    {
        $manager = $company->users()
            ->where('role', 'admin')
            ->first();

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:100',
                'unique:companies,code,' . $company->id,
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'max_users' => [
                'required',
                'integer',
                'min:1',
            ],

            'status' => [
                'required',
                'in:active,inactive,expired',
            ],

            'manager_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'manager_email' => [
                'nullable',
                'email',
                'max:255',
                'unique:users,email,' . ($manager?->id ?? 'NULL'),
            ],

            'manager_password' => [
                'nullable',
                'string',
                'min:6',
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $company,
            $manager
        ) {

            $company->update([
                'name' => $request->name,
                'code' => strtoupper(
                    trim($request->code)
                ),

                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,

                'max_users' => $request->max_users,
                'status' => $request->status,
            ]);

            /*
            |--------------------------------------------------------------------------
            | تعديل المدير إذا موجود
            |--------------------------------------------------------------------------
            */
            if ($manager) {

                $managerData = [
                    'name' => $request->manager_name
                        ?: $manager->name,

                    'email' => $request->manager_email
                        ?: $manager->email,
                ];

                if ($request->filled('manager_password')) {
                    $managerData['password'] = Hash::make(
                        $request->manager_password
                    );
                }

                $manager->update($managerData);
            }

            /*
            |--------------------------------------------------------------------------
            | إذا الشركة ما عدها مدير ونطيت بيانات مدير
            |--------------------------------------------------------------------------
            */
            if (
                !$manager &&
                $request->filled('manager_name') &&
                $request->filled('manager_email')
            ) {

                User::create([
                    'company_id' => $company->id,
                    'name' => $request->manager_name,
                    'email' => $request->manager_email,

                    'password' => Hash::make(
                        $request->manager_password ?: '12345678'
                    ),

                    'role' => 'admin',
                ]);
            }
        });

        return redirect(
            '/admin/companies/' . $company->id
        )->with(
            'success',
            __('تم تحديث بيانات الشركة والمدير بنجاح')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | تجديد الاشتراك
    |--------------------------------------------------------------------------
    */
    public function renew(
        Request $request,
        Company $company
    ) {

        $request->validate([
            'days' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | إذا الاشتراك بعده فعال
        | نضيف الأيام على تاريخ الانتهاء الحالي
        |
        | إذا منتهي
        | نبدأ من اليوم
        |--------------------------------------------------------------------------
        */
        if (
            $company->subscription_end &&
            \Carbon\Carbon::parse(
                $company->subscription_end
            )->isFuture()
        ) {

            $baseDate = \Carbon\Carbon::parse(
                $company->subscription_end
            );

        } else {

            $baseDate = now();

        }

        $company->update([
            'subscription_start' => now(),

            'subscription_end' => $baseDate
                ->copy()
                ->addDays(
                    (int) $request->days
                ),

            'status' => 'active',
        ]);

        return redirect(
            '/admin/companies/' . $company->id
        )->with(
            'success',
            __('تم تجديد اشتراك الشركة بنجاح')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | تفعيل الشركة
    |--------------------------------------------------------------------------
    */
    public function activate(Company $company)
    {
        $company->update([
            'status' => 'active',
        ]);

        return back()->with(
            'success',
            __('تم تفعيل الشركة')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | إيقاف الشركة
    |--------------------------------------------------------------------------
    */
    public function deactivate(Company $company)
    {
        $company->update([
            'status' => 'inactive',
        ]);

        return back()->with(
            'success',
            __('تم إيقاف الشركة')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | حذف الشركة
    |--------------------------------------------------------------------------
    */
    public function destroy(Company $company)
    {
        DB::transaction(function () use ($company) {

            /*
            |--------------------------------------------------------------------------
            | نحذف مستخدمي الشركة
            |--------------------------------------------------------------------------
            */
            $company->users()->delete();

            /*
            |--------------------------------------------------------------------------
            | بعدها نحذف الشركة
            |--------------------------------------------------------------------------
            */
            $company->delete();
        });

        return redirect('/admin/companies')
            ->with(
                'success',
                __('تم حذف الشركة وجميع مستخدميها بنجاح')
            );
    }
}
