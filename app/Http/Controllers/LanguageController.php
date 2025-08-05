<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    /**
     * Changer la langue de l'application
     */
    public function switch(Request $request, string $locale)
    {
        $availableLocales = array_keys(config('app.available_locales', ['fr', 'ar']));

        // Vérifier que la langue est supportée
        if (!in_array($locale, $availableLocales)) {
            return back()->withErrors(['language' => 'Langue non supportée']);
        }

        // Stocker la langue dans la session
        Session::put('locale', $locale);

        // Si l'utilisateur est connecté, sauvegarder sa préférence
        if (Auth::check() && Auth::user()->profile) {
            Auth::user()->profile->update(['language' => $locale]);
        }

        // Rediriger vers la page précédente ou le dashboard
        return Redirect::back();
    }

    /**
     * Obtenir les langues disponibles
     */
    public function getAvailableLanguages()
    {
        return response()->json([
            'current' => app()->getLocale(),
            'available' => config('app.available_locales', ['fr' => 'Français', 'ar' => 'العربية'])
        ]);
    }
}
