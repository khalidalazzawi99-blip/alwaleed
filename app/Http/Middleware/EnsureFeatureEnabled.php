<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        abort_unless(array_key_exists($feature, config('features.modules', [])), 404);

        if ($user?->role !== 'super_admin') {
            abort_unless($user?->company?->hasFeature($feature), 403, __('messages.feature_not_enabled'));
        }

        return $next($request);
    }
}
