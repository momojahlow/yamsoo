<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use Illuminate\Console\Command;

class FixGenderRelations extends Command
{
    protected $signature = 'fix:gender-relations';
    protected $description = 'Corrige les relations avec des genres incorrects';

    public function handle()
    {
        $this->info('🔧 Correction des relations avec des genres incorrects');

        $fixedCount = 0;

        // Récupérer toutes les relations
        $relationships = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();

        foreach ($relationships as $relationship) {
            $user = $relationship->user;
            $relatedUser = $relationship->relatedUser;
            $relationType = $relationship->relationshipType;

            if (!$user || !$relatedUser || !$relationType) {
                continue;
            }

            $shouldFix = false;
            $newRelationType = null;

            // Vérifier les relations parent-enfant incorrectes
            if ($relationType->name === 'mother' && $this->getUserGender($user) === 'male') {
                $newRelationType = RelationshipType::where('name', 'father')->first();
                $shouldFix = true;
                $this->info("🔧 {$user->name} (homme) ne peut pas être 'mère' → 'père'");
            }
            
            if ($relationType->name === 'father' && $this->getUserGender($user) === 'female') {
                $newRelationType = RelationshipType::where('name', 'mother')->first();
                $shouldFix = true;
                $this->info("🔧 {$user->name} (femme) ne peut pas être 'père' → 'mère'");
            }

            if ($relationType->name === 'daughter' && $this->getUserGender($user) === 'male') {
                $newRelationType = RelationshipType::where('name', 'son')->first();
                $shouldFix = true;
                $this->info("🔧 {$user->name} (homme) ne peut pas être 'fille' → 'fils'");
            }

            if ($relationType->name === 'son' && $this->getUserGender($user) === 'female') {
                $newRelationType = RelationshipType::where('name', 'daughter')->first();
                $shouldFix = true;
                $this->info("🔧 {$user->name} (femme) ne peut pas être 'fils' → 'fille'");
            }

            // Vérifier les relations frère/sœur incorrectes
            if ($relationType->name === 'sister' && $this->getUserGender($user) === 'male') {
                $newRelationType = RelationshipType::where('name', 'brother')->first();
                $shouldFix = true;
                $this->info("🔧 {$user->name} (homme) ne peut pas être 'sœur' → 'frère'");
            }

            if ($relationType->name === 'brother' && $this->getUserGender($user) === 'female') {
                $newRelationType = RelationshipType::where('name', 'sister')->first();
                $shouldFix = true;
                $this->info("🔧 {$user->name} (femme) ne peut pas être 'frère' → 'sœur'");
            }

            // Vérifier les relations de mariage incorrectes
            if ($relationType->name === 'wife' && $this->getUserGender($user) === 'male') {
                $newRelationType = RelationshipType::where('name', 'husband')->first();
                $shouldFix = true;
                $this->info("🔧 {$user->name} (homme) ne peut pas être 'épouse' → 'mari'");
            }

            if ($relationType->name === 'husband' && $this->getUserGender($user) === 'female') {
                $newRelationType = RelationshipType::where('name', 'wife')->first();
                $shouldFix = true;
                $this->info("🔧 {$user->name} (femme) ne peut pas être 'mari' → 'épouse'");
            }

            // Appliquer la correction
            if ($shouldFix && $newRelationType) {
                $relationship->update(['relationship_type_id' => $newRelationType->id]);
                $fixedCount++;
                $this->info("✅ Corrigé: {$user->name} → {$relatedUser->name} ({$newRelationType->display_name_fr})");
            }
        }

        $this->info("\n🎉 Correction terminée !");
        $this->info("📊 Nombre de relations corrigées: {$fixedCount}");

        // Afficher un échantillon des relations après correction
        $this->info("\n📋 Échantillon des relations après correction:");
        $sampleRelations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])
            ->limit(10)
            ->get();

        foreach ($sampleRelations as $rel) {
            $userGender = $this->getUserGender($rel->user);
            $this->info("  - {$rel->user->name} ({$userGender}) → {$rel->relatedUser->name}: {$rel->relationshipType->display_name_fr}");
        }
    }

    private function getUserGender(User $user): string
    {
        $gender = $user->profile?->gender;
        
        if (!$gender) {
            // Deviner le genre à partir du prénom
            $firstName = explode(' ', trim($user->name))[0];
            $firstName = strtolower($firstName);

            $femaleNames = [
                'fatima', 'zahra', 'amina', 'khadija', 'aicha', 'maryam', 'sara', 'leila', 'nadia', 'samira',
                'marie', 'sophie', 'julie', 'claire', 'anne', 'isabelle', 'catherine', 'sylvie', 'martine',
                'nour', 'yasmine', 'salma', 'iman', 'rajae', 'zineb', 'houda', 'siham', 'karima', 'hanae'
            ];

            $maleNames = [
                'mohammed', 'ahmed', 'hassan', 'omar', 'ali', 'youssef', 'karim', 'said', 'abdelkader', 'rachid',
                'pierre', 'jean', 'michel', 'philippe', 'alain', 'nicolas', 'christophe', 'laurent', 'david',
                'abderrahim', 'mustapha', 'khalid', 'nabil', 'fouad', 'tarik', 'amine', 'othmane', 'adil'
            ];

            if (in_array($firstName, $femaleNames)) {
                return 'female';
            } elseif (in_array($firstName, $maleNames)) {
                return 'male';
            }
            
            return 'unknown';
        }

        return $gender;
    }
}
