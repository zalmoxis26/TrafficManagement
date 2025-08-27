<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerifiedAndApproved
{
    public function handle($request, \Closure $next)
{
    if ($request->routeIs([
        'login','login.*',
        'logout',
        'register','register.*',
        'password.request','password.email','password.reset','password.update',
        'verification.notice','verification.resend','verification.send','verification.verify','admin.approve',
        'admin.reject',
        'admin.registration.setRole',
    ])) {
        return $next($request);
    }

    $user = $request->user();
    if (!$user) {
        return redirect()->route('login');
    }

    if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    if (is_null($user->approved_at)) {
        return response()->view('auth.not-approved');
    }

    return $next($request);
}

}