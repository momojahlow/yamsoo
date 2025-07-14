<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use Illuminate\Database\Seeder;

class FamilyRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les types de relations
        $fatherType = RelationshipType::where('code', 'father')->first();
        $motherType = RelationshipType::where('code', 'mother')->first();
        $sonType = RelationshipType::where('code', 'son')->first();
        $daughterType = RelationshipType::where('code', 'daughter')->first();
        $brotherType = RelationshipType::where('code', 'brother')->first();
        $sisterType = RelationshipType::where('code', 'sister')->first();
        $husbandType = RelationshipType::where('code', 'husband')->first();
        $wifeType = RelationshipType::where('code', 'wife')->first();

        // Récupérer les utilisateurs
        $users = User::with('profile')->get();

        // Créer des relations familiales existantes
        $existingRelations = [
            // Famille Benali
            [
                'user_id' => $users->where('email', 'ahmed.benali@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'fatima.zahra@example.com')->first()->id,
                'relationship_type_id' => $wifeType->id,
                'status' => 'accepted',
            ],
            [
                'user_id' => $users->where('email', 'fatima.zahra@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'ahmed.benali@example.com')->first()->id,
                'relationship_type_id' => $husbandType->id,
                'status' => 'accepted',
            ],
            [
                'user_id' => $users->where('email', 'ahmed.benali@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'amina.tazi@example.com')->first()->id,
                'relationship_type_id' => $daughterType->id,
                'status' => 'accepted',
            ],
            [
                'user_id' => $users->where('email', 'amina.tazi@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'ahmed.benali@example.com')->first()->id,
                'relationship_type_id' => $fatherType->id,
                'status' => 'accepted',
            ],
            [
                'user_id' => $users->where('email', 'fatima.zahra@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'amina.tazi@example.com')->first()->id,
                'relationship_type_id' => $daughterType->id,
                'status' => 'accepted',
            ],
            [
                'user_id' => $users->where('email', 'amina.tazi@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'fatima.zahra@example.com')->first()->id,
                'relationship_type_id' => $motherType->id,
                'status' => 'accepted',
            ],

            // Famille Alami
            [
                'user_id' => $users->where('email', 'mohammed.alami@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'leila.mansouri@example.com')->first()->id,
                'relationship_type_id' => $wifeType->id,
                'status' => 'accepted',
            ],
            [
                'user_id' => $users->where('email', 'leila.mansouri@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'mohammed.alami@example.com')->first()->id,
                'relationship_type_id' => $husbandType->id,
                'status' => 'accepted',
            ],

            // Famille Bennani
            [
                'user_id' => $users->where('email', 'youssef.bennani@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'sara.benjelloun@example.com')->first()->id,
                'relationship_type_id' => $wifeType->id,
                'status' => 'accepted',
            ],
            [
                'user_id' => $users->where('email', 'sara.benjelloun@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'youssef.bennani@example.com')->first()->id,
                'relationship_type_id' => $husbandType->id,
                'status' => 'accepted',
            ],

            // Relations fraternelles
            [
                'user_id' => $users->where('email', 'karim.elfassi@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'omar.cherkaoui@example.com')->first()->id,
                'relationship_type_id' => $brotherType->id,
                'status' => 'accepted',
            ],
            [
                'user_id' => $users->where('email', 'omar.cherkaoui@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'karim.elfassi@example.com')->first()->id,
                'relationship_type_id' => $brotherType->id,
                'status' => 'accepted',
            ],

            // Relations sœurs
            [
                'user_id' => $users->where('email', 'nadia.berrada@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'zineb.elkhayat@example.com')->first()->id,
                'relationship_type_id' => $sisterType->id,
                'status' => 'accepted',
            ],
            [
                'user_id' => $users->where('email', 'zineb.elkhayat@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'nadia.berrada@example.com')->first()->id,
                'relationship_type_id' => $sisterType->id,
                'status' => 'accepted',
            ],

            // Famille Idrissi
            [
                'user_id' => $users->where('email', 'hassan.idrissi@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'hanae.mernissi@example.com')->first()->id,
                'relationship_type_id' => $wifeType->id,
                'status' => 'accepted',
            ],
            [
                'user_id' => $users->where('email', 'hanae.mernissi@example.com')->first()->id,
                'related_user_id' => $users->where('email', 'hassan.idrissi@example.com')->first()->id,
                'relationship_type_id' => $husbandType->id,
                'status' => 'accepted',
            ],
        ];

        foreach ($existingRelations as $relation) {
            FamilyRelationship::create($relation);
        }

        // Créer des demandes de relation en attente
        $pendingRequests = [
            [
                'requester_id' => $users->where('email', 'adil.benslimane@example.com')->first()->id,
                'target_user_id' => $users->where('email', 'rachid.alaoui@example.com')->first()->id,
                'relationship_type_id' => $brotherType->id,
                'message' => 'Bonjour, je pense que nous pourrions être frères. Notre père nous a parlé de vous.',
                'status' => 'pending',
            ],
            [
                'requester_id' => $users->where('email', 'rachid.alaoui@example.com')->first()->id,
                'target_user_id' => $users->where('email', 'adil.benslimane@example.com')->first()->id,
                'relationship_type_id' => $brotherType->id,
                'message' => 'Salut ! J\'ai trouvé des informations sur notre famille et je pense que nous sommes liés.',
                'status' => 'pending',
            ],
        ];

        foreach ($pendingRequests as $request) {
            RelationshipRequest::create($request);
        }
    }
}
