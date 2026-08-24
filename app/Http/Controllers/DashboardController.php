<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Cashbox;
use App\Models\Receipt;
use App\Models\Payment;
use App\Models\Company;
use App\Models\User;
use App\Models\Setting;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dashboard الشركة
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $user = auth()->user();

        // إذا مالك النظام دخل /dashboard نوديه للوحته الخاصة
        if ($user->role === 'super_admin') {
            return redirect('/admin/dashboard');
        }

        $companyId = $user->company_id;
        $company = $user->company;
        $currency = Setting::where('company_id', $companyId)->value('currency') ?: 'IQD';
        $lowBalanceThreshold = config('notifications.low_balance_thresholds.'.$currency, 100000);

        /*
        |--------------------------------------------------------------------------
        | الاشتراك
        |--------------------------------------------------------------------------
        */

        $subscriptionDaysLeft = null;

        if ($company && $company->subscription_end) {

            $subscriptionDaysLeft = now()
                ->startOfDay()
                ->diffInDays(
                    Carbon::parse($company->subscription_end)
                        ->startOfDay(),
                    false
                );
        }

        /*
        |--------------------------------------------------------------------------
        | الصندوق
        |--------------------------------------------------------------------------
        */

        $balance = Cashbox::where('company_id', $companyId)->sum('balance');

        /*
        |--------------------------------------------------------------------------
        | الزبائن والموردين
        |--------------------------------------------------------------------------
        */

        $customers = Customer::where('company_id', $companyId)
            ->count();

        $suppliers = Supplier::where('company_id', $companyId)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | سندات القبض
        |--------------------------------------------------------------------------
        */

        $totalReceipts = Receipt::where('company_id', $companyId)
            ->sum('amount');

        $receiptsCount = Receipt::where('company_id', $companyId)
            ->count();

        $latestReceipts = Receipt::with(['customer', 'supplier'])
            ->where('company_id', $companyId)
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | سندات الصرف
        |--------------------------------------------------------------------------
        */

        $totalPayments = Payment::where('company_id', $companyId)
            ->sum('amount');

        $paymentsCount = Payment::where('company_id', $companyId)
            ->count();

        $latestPayments = Payment::with(['customer', 'supplier'])
            ->where('company_id', $companyId)
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | حركة اليوم
        |--------------------------------------------------------------------------
        */

        $todayReceipts = Receipt::where('company_id', $companyId)
            ->whereDate('receipt_date', today())
            ->sum('amount');

        $todayPayments = Payment::where('company_id', $companyId)
            ->whereDate('payment_date', today())
            ->sum('amount');

        $todayNet = $todayReceipts - $todayPayments;

        /*
        |--------------------------------------------------------------------------
        | حركة الشهر الحالي
        |--------------------------------------------------------------------------
        */

        $monthReceipts = Receipt::where('company_id', $companyId)
            ->whereYear('receipt_date', now()->year)
            ->whereMonth('receipt_date', now()->month)
            ->sum('amount');

        $monthPayments = Payment::where('company_id', $companyId)
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('amount');

        $monthNet = $monthReceipts - $monthPayments;

        /*
        |--------------------------------------------------------------------------
        | صافي الحركة
        |--------------------------------------------------------------------------
        */

        $netMovement = $totalReceipts - $totalPayments;

        /*
        |--------------------------------------------------------------------------
        | عدد مستخدمي الشركة
        |--------------------------------------------------------------------------
        */

        $companyUsersCount = User::where('company_id', $companyId)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | إرسال البيانات
        |--------------------------------------------------------------------------
        */

        return view('dashboard', [

            'company' => $company,

            'subscriptionDaysLeft' => $subscriptionDaysLeft,

            'customers' => $customers,

            'suppliers' => $suppliers,

            'balance' => $balance,

            'lowBalanceThreshold' => $lowBalanceThreshold,

            'totalReceipts' => $totalReceipts,

            'totalPayments' => $totalPayments,

            'receiptsCount' => $receiptsCount,

            'paymentsCount' => $paymentsCount,

            'latestReceipts' => $latestReceipts,

            'latestPayments' => $latestPayments,

            'todayReceipts' => $todayReceipts,

            'todayPayments' => $todayPayments,

            'todayNet' => $todayNet,

            'monthReceipts' => $monthReceipts,

            'monthPayments' => $monthPayments,

            'monthNet' => $monthNet,

            'netMovement' => $netMovement,

            'companyUsersCount' => $companyUsersCount,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Dashboard مالك النظام
    |--------------------------------------------------------------------------
    */
    public function superAdmin()
    {
        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            abort(403, __('غير مسموح بالدخول إلى لوحة مالك النظام'));
        }

        /*
        |--------------------------------------------------------------------------
        | الشركات
        |--------------------------------------------------------------------------
        */

        $companiesCount = Company::count();

        $activeCompanies = Company::where('status', 'active')
            ->count();

        $inactiveCompanies = Company::where('status', 'inactive')
            ->count();

        $expiredCompanies = Company::where(function ($query) {

            $query->where('status', 'expired')
                ->orWhereDate('subscription_end', '<', now());

        })->count();

        /*
        |--------------------------------------------------------------------------
        | الاشتراكات القريبة من الانتهاء
        |--------------------------------------------------------------------------
        */

        $today = now()->startOfDay();

        $next7Days = now()
            ->copy()
            ->addDays(7)
            ->endOfDay();

        $endingSoonCompanies = Company::where('status', 'active')
            ->whereNotNull('subscription_end')
            ->whereBetween('subscription_end', [
                $today,
                $next7Days,
            ])
            ->orderBy('subscription_end')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | المستخدمون
        |--------------------------------------------------------------------------
        */

        $usersCount = User::where('role', '!=', 'super_admin')
            ->count();

        $adminsCount = User::where('role', 'admin')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | أحدث الشركات
        |--------------------------------------------------------------------------
        */

        $latestCompanies = Company::withCount('users')
            ->latest()
            ->take(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | إرسال البيانات للوحة مالك النظام
        |--------------------------------------------------------------------------
        */

        return view('dashboard_super_admin', [

            'companiesCount' => $companiesCount,

            'activeCompanies' => $activeCompanies,

            'inactiveCompanies' => $inactiveCompanies,

            'expiredCompanies' => $expiredCompanies,

            'endingSoonCount' => $endingSoonCompanies->count(),

            'endingSoonCompanies' => $endingSoonCompanies,

            'usersCount' => $usersCount,

            'adminsCount' => $adminsCount,

            'latestCompanies' => $latestCompanies,
        ]);
    }
}
