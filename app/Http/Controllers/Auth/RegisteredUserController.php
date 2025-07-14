<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'mobile' => 'required|string|max:20|unique:users,mobile',
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Créer le nom complet pour la compatibilité
        $fullName = trim($request->first_name . ' ' . $request->last_name);

        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password),
        ]);

        // Le profil sera créé automatiquement par l'Observer
        // avec les informations détaillées (first_name, last_name, birth_date, gender)

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
