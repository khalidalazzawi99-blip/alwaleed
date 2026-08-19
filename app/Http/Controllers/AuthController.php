<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __('بيانات الدخول غير صحيحة'),
                ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // مالك النظام لا يخضع لاشتراكات الشركات
        if ($user->role === 'super_admin') {
            return redirect('/dashboard');
        }

        $company = $user->company;

        // المستخدم غير مربوط بشركة
        if (!$company) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => __('هذا الحساب غير مرتبط بشركة.'),
            ]);
        }

        // الشركة موقوفة يدوياً
        if ($company->status === 'inactive') {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => __('تم إيقاف حساب الشركة. يرجى التواصل مع إدارة النظام.'),
            ]);
        }

        // فحص تاريخ الاشتراك
        if ($company->subscription_end) {

            $subscriptionEnd = Carbon::parse($company->subscription_end)->endOfDay();

            if (now()->greaterThan($subscriptionEnd)) {

                if ($company->status !== 'expired') {
                    $company->update([
                        'status' => 'expired',
                    ]);
                }

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->withErrors([
                    'email' => __('انتهى اشتراك الشركة. يرجى التواصل مع إدارة النظام للتجديد.'),
                ]);
            }
        }

        // إذا الحالة منتهية حتى لو التاريخ تغيّر يدوياً
        if ($company->status === 'expired') {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => __('اشتراك الشركة منتهي. يرجى التواصل مع إدارة النظام للتجديد.'),
            ]);
        }

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
