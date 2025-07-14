<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Profile;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Récupérer les données de la requête pour les informations détaillées
        $request = request();

        // Créer automatiquement un profil pour le nouvel utilisateur
        Profile::create([
            'user_id' => $user->id,
            'first_name' => $request->input('first_name') ?? $this->extractFirstName($user->name),
            'last_name' => $request->input('last_name') ?? $this->extractLastName($user->name),
            'birth_date' => $request->input('birth_date'),
            'gender' => $request->input('gender'),
            'phone' => $user->mobile, // Utiliser le mobile de l'utilisateur
        ]);
    }

    /**
     * Extraire le prénom du nom complet
     */
    private function extractFirstName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        return $parts[0] ?? '';
    }

    /**
     * Extraire le nom de famille du nom complet
     */
    private function extractLastName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        if (count($parts) > 1) {
            array_shift($parts); // Enlever le premier élément (prénom)
            return implode(' ', $parts);
        }
        return '';
    }
}
