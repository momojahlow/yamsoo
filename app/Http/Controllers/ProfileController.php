<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        // Combine user and profile data for the form
        $profileData = [
            'id' => $profile?->id,
            'first_name' => $profile?->first_name,
            'last_name' => $profile?->last_name,
            'bio' => $profile?->bio,
            'avatar' => $profile?->avatar,
            'birth_date' => $profile?->birth_date,
            'gender' => $profile?->gender,
            'avatar_url' => $profile?->avatar_url,
            // User data
            'email' => $user->email,
            'mobile' => $user->mobile,
        ];

        return Inertia::render('Profile/Edit', [
            'profile' => $profileData,
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
            'gender' => 'nullable|in:male,female',
            'bio' => 'nullable|string|max:1000',
        ]);

        // Update user data (email, mobile)
        $user->update([
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
        ]);

        // Update profile data (everything else)
        $profileData = array_diff_key($validated, array_flip(['email', 'mobile']));
        $this->profileService->updateProfile($user, $profileData);

        return redirect()->route('profile.index')->with('success', 'Profil mis à jour avec succès.');
    }

    public function updateAvatar(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Store the uploaded file
            $avatarPath = $request->file('avatar')->store('avatars', 'public');

            // Get or create profile
            $profile = $user->profile;
            if (!$profile) {
                $profile = new \App\Models\Profile();
                $profile->user_id = $user->id;
            }

            // Delete old avatar if exists
            if ($profile->avatar && Storage::disk('public')->exists($profile->avatar)) {
                Storage::disk('public')->delete($profile->avatar);
            }

            // Update avatar path
            $profile->avatar = $avatarPath;
            $profile->save();

            return response()->json([
                'success' => true,
                'message' => 'Avatar mis à jour avec succès.',
                'avatar_url' => Storage::url($avatarPath)
            ]);

        } catch (\Exception) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'avatar.'
            ], 500);
        }
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
