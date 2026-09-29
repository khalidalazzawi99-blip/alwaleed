<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateSubmission
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() || $request->expectsJson()) {
            return $next($request);
        }

        $submissionToken = $request->input('_submission_token');
        if (! is_string($submissionToken) || ! preg_match('/^[a-zA-Z0-9-]{20,80}$/', $submissionToken)) {
            return $next($request);
        }

        $identity = auth()->id() ?: $request->session()->getId();
        $key = 'form-submit:'.hash('sha256', $identity.'|'.$submissionToken);

        if (! Cache::add($key, true, now()->addSeconds(8))) {
            return back()->withInput()->withErrors(['form' => 'تم استلام الطلب مسبقًا، يرجى الانتظار وعدم الضغط مرتين.']);
        }

        return $next($request);
    }
}
