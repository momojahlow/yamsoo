<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $profile = $this->profileService->getProfile($user);

        return Inertia::render('Profile', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function show(Profile $profile): Response
    {
        return Inertia::render('Profile/Show', [
            'profile' => $profile->load('user'),
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = $request->user();
        $profile = $this->profileService->getProfile($user);

        return Inertia::render('Profile/Edit', [
            'profile' => $profile,
        ]);
    }

    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:M,F',
            'bio' => 'nullable|string|max:1000',
        ]);

        $this->profileService->updateProfile($user, $validated);

        return redirect()->route('profile.index')->with('success', 'Profil mis à jour avec succès.');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'bio' => 'nullable|string|max:1000',
        ]);

        $this->profileService->createProfile($user, $validated);

        return redirect()->route('profiles.index')->with('success', 'Profil créé avec succès.');
    }
}
