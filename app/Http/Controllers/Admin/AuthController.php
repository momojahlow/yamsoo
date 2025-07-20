<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion admin
     */
    public function showLoginForm(): Response
    {
        return Inertia::render('Admin/Auth/Login');
    }

    /**
     * Traiter la connexion admin
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        
        // Tentative de connexion
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $admin = Auth::guard('admin')->user();
            
            // Vérifier si l'admin est actif
            if (!$admin->is_active) {
                Auth::guard('admin')->logout();
                throw ValidationException::withMessages([
                    'email' => 'Votre compte administrateur a été désactivé.',
                ]);
            }

            // Enregistrer la connexion
            $admin->recordLogin($request->ip());
            
            // Log de l'activité
            AdminActivityLog::log(
                $admin,
                'login',
                'Connexion à l\'interface d\'administration',
            );

            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', "Bienvenue {$admin->name} !");
        }

        throw ValidationException::withMessages([
            'email' => 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.',
        ]);
    }

    /**
     * Déconnexion admin
     */
    public function logout(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        
        if ($admin) {
            // Log de l'activité
            AdminActivityLog::log(
                $admin,
                'logout',
                'Déconnexion de l\'interface d\'administration',
            );
        }

        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function showChangePasswordForm(): Response
    {
        return Inertia::render('Admin/Auth/ChangePassword');
    }

    /**
     * Changer le mot de passe admin
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Auth::guard('admin')->user();

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Le mot de passe actuel est incorrect.',
            ]);
        }

        // Mettre à jour le mot de passe
        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        // Log de l'activité
        AdminActivityLog::log(
            $admin,
            'password_change',
            'Changement de mot de passe',
        );

        return redirect()->route('admin.dashboard')
            ->with('success', 'Votre mot de passe a été mis à jour avec succès.');
    }

    /**
     * Afficher le profil admin
     */
    public function showProfile(): Response
    {
        $admin = Auth::guard('admin')->user();
        
        return Inertia::render('Admin/Auth/Profile', [
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'role_name' => $admin->role_name,
                'is_active' => $admin->is_active,
                'last_login_at' => $admin->last_login_at,
                'last_login_ip' => $admin->last_login_ip,
                'permissions' => $admin->permissions,
                'created_at' => $admin->created_at,
            ],
        ]);
    }

    /**
     * Mettre à jour le profil admin
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
        ]);

        $oldValues = [
            'name' => $admin->name,
            'email' => $admin->email,
        ];

        $admin->update($request->only('name', 'email'));

        // Log de l'activité
        AdminActivityLog::log(
            $admin,
            'profile_update',
            'Mise à jour du profil administrateur',
            null,
            null,
            $oldValues,
            $request->only('name', 'email')
        );

        return redirect()->route('admin.profile')
            ->with('success', 'Votre profil a été mis à jour avec succès.');
    }
}
