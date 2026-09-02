<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch the application locale.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $allowedLocales = config('locales.supported', []);

        if (in_array($locale, $allowedLocales)) {
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}
