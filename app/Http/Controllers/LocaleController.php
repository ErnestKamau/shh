<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Set the application locale.
     *
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setLocale($locale)
    {
        try {
            $activeCodes = \App\Models\System\Language::query()->active()->pluck('code')->all();
        } catch (\Throwable $e) {
            $activeCodes = ['en', 'sw'];
        }

        if (!in_array($locale, $activeCodes)) {
            $locale = 'en';
        }

        Session::put('locale', $locale);
        App::setLocale($locale);

        return redirect()->back();
    }
}
