<?php

namespace App\Services;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;

class ProfileService
{
    public function getProfile(User $user): ?Profile
    {
        return $user->profile;
    }

    public function updateProfile(User $user, array $data): Profile
    {
        $profile = $user->profile;

        if (!$profile) {
            $profile = new Profile();
            $profile->user_id = $user->id;
        }

        $profile->fill($data);
        $profile->save();

        return $profile;
    }

    public function createProfile(User $user, array $data): Profile
    {
        $profile = new Profile();
        $profile->user_id = $user->id;
        $profile->fill($data);
        $profile->save();

        return $profile;
    }
}
