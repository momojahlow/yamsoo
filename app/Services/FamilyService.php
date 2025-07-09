<?php

namespace App\Services;

use App\Models\Family;
use App\Models\User;
use Illuminate\Support\Collection;

class FamilyService
{
    public function getUserFamily(User $user): ?Family
    {
        return $user->family;
    }

    public function createFamily(User $user, array $data): Family
    {
        $family = Family::create($data);
        $user->family_id = $family->id;
        $user->save();

        return $family;
    }

    public function addFamilyMember(Family $family, User $user, string $relation): void
    {
        $family->members()->attach($user->id, ['relation' => $relation]);
    }

    public function getFamilyMembers(Family $family): Collection
    {
        return $family->members;
    }

    public function getFamilyTree(Family $family): array
    {
        // Logique pour construire l'arbre généalogique
        return [
            'family' => $family,
            'members' => $family->members,
            'relations' => $family->members()->pivot->get()
        ];
    }
}
