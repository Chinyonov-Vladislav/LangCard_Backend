<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $acceptedLanguages = ['ar', 'de', 'en','es','fr','ja','pt', 'ru', 'uk', 'zh'];
        $locale = $request->header('Accept-Language');
        if (in_array($locale, $acceptedLanguages)) {
            App::setLocale($locale);
        } else {
            App::setLocale(config('app.fallback_locale'));
        }
        return $next($request);
    }
}
