<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Récupérer la langue depuis la session, l'utilisateur ou la langue par défaut
        $locale = $this->getLocale($request);

        // Définir la langue de l'application
        App::setLocale($locale);

        // Stocker la langue dans la session
        Session::put('locale', $locale);

        return $next($request);
    }

    /**
     * Déterminer la langue à utiliser
     */
    private function getLocale(Request $request): string
    {
        $availableLocales = array_keys(config('app.available_locales', ['fr', 'ar']));

        // 1. Vérifier si une langue est demandée via paramètre
        if ($request->has('lang') && in_array($request->get('lang'), $availableLocales)) {
            return $request->get('lang');
        }

        // 2. Vérifier la session
        if (Session::has('locale') && in_array(Session::get('locale'), $availableLocales)) {
            return Session::get('locale');
        }

        // 3. Vérifier les préférences utilisateur (si connecté)
        if ($request->user() && $request->user()->profile &&
            in_array($request->user()->profile->language ?? 'fr', $availableLocales)) {
            return $request->user()->profile->language;
        }

        // 4. Langue par défaut
        return config('app.locale', 'fr');
    }
}
