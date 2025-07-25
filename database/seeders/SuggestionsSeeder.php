<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Suggestion;
use App\Models\RelationshipType;
use Illuminate\Database\Seeder;

class SuggestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        // Vérifier que les types de relations existent
        $relationshipTypes = RelationshipType::whereIn('name', ['cousin', 'sister', 'brother', 'uncle', 'aunt'])->get()->keyBy('name');

        $suggestions = [
            // Suggestions pour Ahmed Benali
            [
                'user_email' => 'ahmed.benali@example.com',
                'suggested_user_email' => 'youssef.bennani@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'reason' => 'Score 85 - Même nom de famille et même région d\'origine',
                'status' => 'pending',
                'confidence_score' => 85,
            ],
            [
                'user_email' => 'ahmed.benali@example.com',
                'suggested_user_email' => 'karim.elfassi@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'reason' => 'Score 72 - Liens familiaux détectés dans les documents historiques',
                'status' => 'pending',
                'confidence_score' => 72,
            ],

            // Suggestions pour Fatima Zahra
            [
                'user_email' => 'fatima.zahra@example.com',
                'suggested_user_email' => 'nadia.berrada@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'sister',
                'confidence_score' => 90,
                'reason' => 'Même nom de famille et même date de naissance proche',
                'status' => 'pending',
            ],

            // Suggestions pour Mohammed Alami
            [
                'user_email' => 'mohammed.alami@example.com',
                'suggested_user_email' => 'hassan.idrissi@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 78,
                'reason' => 'Ancêtres communs identifiés dans l\'arbre généalogique',
                'status' => 'pending',
            ],

            // Suggestions pour Amina Tazi
            [
                'user_email' => 'amina.tazi@example.com',
                'suggested_user_email' => 'sara.benjelloun@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 65,
                'reason' => 'Même âge et même ville d\'origine',
                'status' => 'pending',
            ],

            // Suggestions pour Youssef Bennani
            [
                'user_email' => 'youssef.bennani@example.com',
                'suggested_user_email' => 'omar.cherkaoui@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 82,
                'reason' => 'Liens familiaux détectés par analyse ADN',
                'status' => 'pending',
            ],

            // Suggestions pour Leila Mansouri
            [
                'user_email' => 'leila.mansouri@example.com',
                'suggested_user_email' => 'zineb.elkhayat@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 70,
                'reason' => 'Même profession et même région',
                'status' => 'pending',
            ],

            // Suggestions pour Karim El Fassi
            [
                'user_email' => 'karim.elfassi@example.com',
                'suggested_user_email' => 'adil.benslimane@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 88,
                'reason' => 'Documents familiaux anciens mentionnent cette relation',
                'status' => 'pending',
            ],

            // Suggestions pour Nadia Berrada
            [
                'user_email' => 'nadia.berrada@example.com',
                'suggested_user_email' => 'hanae.mernissi@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 75,
                'reason' => 'Même spécialité médicale et même formation',
                'status' => 'pending',
            ],

            // Suggestions pour Hassan Idrissi
            [
                'user_email' => 'hassan.idrissi@example.com',
                'suggested_user_email' => 'rachid.alaoui@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 80,
                'reason' => 'Même tradition culinaire et même région',
                'status' => 'pending',
            ],

            // Suggestions pour Sara Benjelloun
            [
                'user_email' => 'sara.benjelloun@example.com',
                'suggested_user_email' => 'amina.tazi@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 68,
                'reason' => 'Même passion pour l\'art et même âge',
                'status' => 'pending',
            ],

            // Suggestions pour Omar Cherkaoui
            [
                'user_email' => 'omar.cherkaoui@example.com',
                'suggested_user_email' => 'youssef.bennani@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 85,
                'reason' => 'Même domaine professionnel et même formation',
                'status' => 'pending',
            ],

            // Suggestions pour Zineb El Khayat
            [
                'user_email' => 'zineb.elkhayat@example.com',
                'suggested_user_email' => 'leila.mansouri@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 72,
                'reason' => 'Même passion pour l\'écriture et même région',
                'status' => 'pending',
            ],

            // Suggestions pour Adil Benslimane
            [
                'user_email' => 'adil.benslimane@example.com',
                'suggested_user_email' => 'karim.elfassi@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 90,
                'reason' => 'Documents historiques confirment cette relation',
                'status' => 'pending',
            ],

            // Suggestions pour Hanae Mernissi
            [
                'user_email' => 'hanae.mernissi@example.com',
                'suggested_user_email' => 'nadia.berrada@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 78,
                'reason' => 'Même spécialité médicale et même approche thérapeutique',
                'status' => 'pending',
            ],

            // Suggestions pour Rachid Alaoui
            [
                'user_email' => 'rachid.alaoui@example.com',
                'suggested_user_email' => 'hassan.idrissi@example.com',
                'type' => 'family_link',
                'suggested_relation_code' => 'cousin',
                'confidence_score' => 82,
                'reason' => 'Même tradition religieuse et même région',
                'status' => 'pending',
            ],
        ];

        foreach ($suggestions as $suggestionData) {
            $user = $users->where('email', $suggestionData['user_email'])->first();
            $suggestedUser = $users->where('email', $suggestionData['suggested_user_email'])->first();

            if ($user && $suggestedUser) {
                // Vérifier que le type de relation suggéré existe
                $relationCode = $suggestionData['suggested_relation_code'] ?? 'cousin';
                if (!$relationshipTypes->has($relationCode)) {
                    $relationCode = 'cousin'; // Fallback vers cousin si le type n'existe pas
                }

                Suggestion::create([
                    'user_id' => $user->id,
                    'suggested_user_id' => $suggestedUser->id,
                    'type' => $suggestionData['type'] ?? 'family_link',
                    'suggested_relation_code' => $relationCode,
                    'message' => $suggestionData['reason'],
                    'status' => $suggestionData['status'] ?? 'pending',
                    'confidence_score' => $suggestionData['confidence_score'] ?? 75,
                    'created_at' => now()->subDays(rand(1, 7)),
                ]);
            }
        }
    }
}
