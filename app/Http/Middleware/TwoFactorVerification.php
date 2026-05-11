<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TwoFactorVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! auth()->check() || $this->isExemptPath($request)) {
            return $next($request);
        }

        $user = auth()->user();

        if (session()->get('totp_required') === true && ! $request->is('verify/totp*')) {
            return redirect()->route('verify-totp');
        }

        if (! empty($user->verify_code)) {
            $expireDate = $user->verify_code_expires ? strtotime((string) $user->verify_code_expires) : false;

            if ($expireDate === false || $expireDate <= time()) {
                $user->resetTwoFactor();
                auth()->logout();

                return redirect()->route('login');
            }

            if (! $request->is('verify*')) {
                return redirect()->route('verify-user');
            }
        }
        return $next($request);
    }

    private function isExemptPath(Request $request): bool
    {
        if (
            $request->routeIs('login') ||
            $request->routeIs('logout') ||
            $request->routeIs('mylogout') ||
            $request->routeIs('verify-user') ||
            $request->routeIs('verify-store') ||
            $request->routeIs('verify-store-ext') ||
            $request->routeIs('verify-resend') ||
            $request->routeIs('verify-totp') ||
            $request->routeIs('verify-totp-store') ||
            $request->routeIs('password.*')
        ) {
            return true;
        }

        return $request->is('login') ||
            $request->is('logout') ||
            $request->is('logout/*') ||
            $request->is('verify*') ||
            $request->is('password/*');
    }
}
