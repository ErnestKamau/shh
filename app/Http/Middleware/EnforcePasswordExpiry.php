<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforcePasswordExpiry
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! auth()->check() || $this->isExemptPath($request)) {
            return $next($request);
        }

        /** @var \App\User $user */
        $user = auth()->user();

        if ($user->isPasswordExpired()) {
            return redirect()->route('password.force-change')
                ->with('password_expired', true);
        }

        return $next($request);
    }

    private function isExemptPath(Request $request): bool
    {
        return $request->routeIs(
            'login',
            'logout',
            'mylogout',
            'password.force-change',
            'password.force-change.update',
            'password.*',
            'verify-user',
            'verify-store',
            'verify-store-ext',
            'verify-resend',
            'verify-totp',
            'verify-totp-store',
            'public.test-report.show',
            'public.test-report.pdf',
            'public.collection-qr.show',
        );
    }
}
