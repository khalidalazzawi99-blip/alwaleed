<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // مالك النظام يدخل لكل الصفحات المحمية بالصلاحيات
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Laravel ممكن يرسل الصلاحيات كـ "admin,accountant"
        // فنسوي تفكيك يدوي وآمن
        $allowedRoles = [];

        foreach ($roles as $roleGroup) {
            foreach (explode(',', $roleGroup) as $role) {
                $role = trim($role);

                if ($role !== '') {
                    $allowedRoles[] = $role;
                }
            }
        }

        if (!in_array($user->role, $allowedRoles, true)) {
            abort(403, __('ليس لديك صلاحية للوصول إلى هذه الصفحة'));
        }

        return $next($request);
    }
}
