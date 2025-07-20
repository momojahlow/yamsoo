<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role = 'admin'): Response
    {
        $user = $request->user();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        // Vérifier si l'utilisateur est actif
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Votre compte a été désactivé. Contactez un administrateur.');
        }

        // Vérifier les permissions selon le rôle requis
        $hasPermission = match($role) {
            'super_admin' => $user->isSuperAdmin(),
            'admin' => $user->isAdmin(),
            'moderator' => $user->isModerator(),
            default => false,
        };

        if (!$hasPermission) {
            abort(403, 'Accès refusé. Vous n\'avez pas les permissions nécessaires.');
        }

        return $next($request);
    }
}
