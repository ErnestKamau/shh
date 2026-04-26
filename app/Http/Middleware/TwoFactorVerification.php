<?php

namespace App\Http\Middleware;

use Closure;

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
        $user = auth()->user();

        if (auth()->check() && session()->get('totp_required') === true) {
            if (! $request->is('verify/totp*')) {
                return redirect()->route('verify-totp');
            }
        }

        if (auth()->check() && ! empty($user->verify_code)) {
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
}
