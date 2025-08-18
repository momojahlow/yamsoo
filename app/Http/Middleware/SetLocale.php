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
        // Configuration des langues disponibles avec fallback sécurisé
        $availableLocalesConfig = config('app.available_locales');

        // Si la config n'existe pas ou est null, utiliser les langues par défaut
        if (!is_array($availableLocalesConfig)) {
            $availableLocales = ['fr', 'ar'];
        } else {
            $availableLocales = array_keys($availableLocalesConfig);
        }

        // S'assurer qu'on a au moins une langue disponible
        if (empty($availableLocales)) {
            $availableLocales = ['fr'];
        }

        // 1. Vérifier si une langue est demandée via paramètre
        $requestLang = $request->get('lang');
        if ($requestLang && in_array($requestLang, $availableLocales)) {
            return $requestLang;
        }

        // 2. Vérifier la session
        $sessionLocale = Session::get('locale');
        if ($sessionLocale && in_array($sessionLocale, $availableLocales)) {
            return $sessionLocale;
        }

        // 3. Vérifier les préférences utilisateur (si connecté)
        $user = $request->user();
        if ($user && $user->profile) {
            $userLanguage = $user->profile->language;
            if ($userLanguage && in_array($userLanguage, $availableLocales)) {
                return $userLanguage;
            }
        }

        // 4. Langue par défaut de l'application
        $defaultLocale = config('app.locale');
        if ($defaultLocale && in_array($defaultLocale, $availableLocales)) {
            return $defaultLocale;
        }

        // 5. Fallback ultime - toujours retourner une string valide
        return $availableLocales[0] ?? 'fr';
    }
}
