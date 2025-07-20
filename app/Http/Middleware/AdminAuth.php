<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        // Vérifier si l'admin est connecté
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Vous devez être connecté en tant qu\'administrateur.');
        }

        $admin = Auth::guard('admin')->user();

        // Vérifier si l'admin est actif
        if (!$admin->is_active) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')
                ->with('error', 'Votre compte administrateur a été désactivé.');
        }

        // Vérifier les permissions si spécifiées
        if ($permission && !$admin->hasPermission($permission) && !$admin->isSuperAdmin()) {
            abort(403, 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource.');
        }

        return $next($request);
    }
}
