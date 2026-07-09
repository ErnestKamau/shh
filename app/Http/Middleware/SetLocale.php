<?php

namespace App\Http\Middleware;

use App\Models\System\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale');

        if (!is_string($locale) || $locale === '') {
            try {
                $locale = Language::query()
                    ->where('is_default', true)
                    ->value('code');
            } catch (\Throwable $e) {
                $locale = null;
            }
        }

        if (!is_string($locale) || $locale === '') {
            $locale = (string) config('app.locale', 'en');
        }

        $locale = strtolower($locale);

        try {
            $activeCodes = Language::query()->active()->pluck('code')->map(
                fn (string $code): string => strtolower($code)
            )->all();
        } catch (\Throwable $e) {
            $activeCodes = ['en', 'sw', 'pt', 'ar'];
        }

        if ($activeCodes !== [] && !in_array($locale, $activeCodes, true)) {
            $locale = in_array('en', $activeCodes, true) ? 'en' : $activeCodes[0];
        }

        App::setLocale($locale);

        return $next($request);
    }
}
