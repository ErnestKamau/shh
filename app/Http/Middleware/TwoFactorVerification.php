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

        if (auth()->check() && $user->verify_code){
            $expire_date = strtotime($user->verify_code_expires);
            $now = strtotime(now());
            $diff = $expire_date - $now;
            if(floor($diff/(60)) > 30){
                $user->resetTwoFactor();
                auth()->logout();
                
                return redirect()->route('login');
            }
            if(!$request->is('verify*')){
                return redirect()->route('verify-user');
            }
        }
        return $next($request);
    }
}
