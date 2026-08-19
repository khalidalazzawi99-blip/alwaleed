<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // إذا ماكو مستخدم مسجل دخول
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Super Admin ما يخضع لأي اشتراك
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // الشركة المرتبطة بالمستخدم
        $company = $user->company;

        // إذا المستخدم مو مربوط بشركة
        if (!$company) {

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => __('هذا الحساب غير مرتبط بشركة.'),
            ]);
        }

        // إذا الشركة موقوفة من الـ Super Admin
        if ($company->status === 'inactive') {

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => __('تم إيقاف حساب الشركة. يرجى التواصل مع إدارة النظام.'),
            ]);
        }

        // فحص تاريخ انتهاء الاشتراك
        if ($company->subscription_end) {

            $subscriptionEnd = Carbon::parse(
                $company->subscription_end
            )->endOfDay();

            if (now()->greaterThan($subscriptionEnd)) {

                // تحويل الشركة تلقائياً إلى منتهية
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

        // إذا حالتها منتهية
        if ($company->status === 'expired') {

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => __('اشتراك الشركة منتهي. يرجى التواصل مع إدارة النظام للتجديد.'),
            ]);
        }

        return $next($request);
    }
}
