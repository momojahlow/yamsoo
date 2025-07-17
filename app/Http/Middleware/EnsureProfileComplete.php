<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Si l'utilisateur n'est pas connecté, laisser passer
        if (!$user) {
            return $next($request);
        }
        
        // Vérifier si l'utilisateur a un profil
        if (!$user->profile) {
            // Rediriger vers la page de création de profil
            if (!$request->routeIs('profile.create')) {
                return redirect()->route('profile.create')
                    ->with('warning', 'Veuillez compléter votre profil pour continuer.');
            }
            return $next($request);
        }
        
        $profile = $user->profile;
        
        // Vérifier si les informations essentielles sont présentes
        $missingFields = [];
        
        if (!$profile->first_name) {
            $missingFields[] = 'prénom';
        }
        
        if (!$profile->last_name) {
            $missingFields[] = 'nom de famille';
        }
        
        if (!$profile->hasDefinedGender()) {
            $missingFields[] = 'genre';
        }
        
        // Si des champs essentiels manquent, rediriger vers l'édition du profil
        if (!empty($missingFields) && !$request->routeIs('profile.edit')) {
            $message = 'Veuillez compléter votre profil : ' . implode(', ', $missingFields) . '.';
            return redirect()->route('profile.edit')
                ->with('warning', $message);
        }
        
        return $next($request);
    }
}
