<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin panel language: the one picked in the switcher (session),
 * otherwise the browser's preferred language.
 */
class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', ['en']);
        $locale = $request->session()->get('admin_locale') ?: $request->getPreferredLanguage($supported);

        if (in_array($locale, $supported, true)) {
            app()->setLocale($locale);
            Carbon::setLocale($locale);
        }

        return $next($request);
    }
}
