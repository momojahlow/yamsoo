<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Profile;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use Illuminate\Console\Command;

class AnalyzeGenderData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'analyze:gender-data';

    /**
     * The console command description.
     */
    protected $description = 'Analyse la qualité des données de genre et les relations familiales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Analyse des données de genre et relations familiales...');
        $this->newLine();

        // 1. Analyse des genres
        $this->analyzeGenderDistribution();
        $this->newLine();

        // 2. Analyse des relations problématiques
        $this->analyzeProblematicRelations();
        $this->newLine();

        // 3. Suggestions d'amélioration
        $this->provideSuggestions();
    }

    private function analyzeGenderDistribution()
    {
        $this->info('📊 Distribution des genres :');

        $maleCount = Profile::where('gender', 'male')->count();
        $femaleCount = Profile::where('gender', 'female')->count();
        $otherCount = Profile::where('gender', 'other')->count();
        $nullCount = Profile::whereNull('gender')->orWhere('gender', '')->count();
        $total = Profile::count();

        $this->table(
            ['Genre', 'Nombre', 'Pourcentage'],
            [
                ['Masculin', $maleCount, $total > 0 ? round(($maleCount / $total) * 100, 1) . '%' : '0%'],
                ['Féminin', $femaleCount, $total > 0 ? round(($femaleCount / $total) * 100, 1) . '%' : '0%'],
                ['Autre', $otherCount, $total > 0 ? round(($otherCount / $total) * 100, 1) . '%' : '0%'],
                ['Non défini', $nullCount, $total > 0 ? round(($nullCount / $total) * 100, 1) . '%' : '0%'],
                ['TOTAL', $total, '100%'],
            ]
        );

        $definedGenders = $maleCount + $femaleCount;
        $qualityScore = $total > 0 ? round(($definedGenders / $total) * 100, 1) : 0;

        if ($qualityScore >= 90) {
            $this->info("✅ Excellente qualité des données : {$qualityScore}%");
        } elseif ($qualityScore >= 70) {
            $this->warn("⚠️  Qualité acceptable : {$qualityScore}%");
        } else {
            $this->error("❌ Qualité insuffisante : {$qualityScore}%");
        }
    }

    private function analyzeProblematicRelations()
    {
        $this->info('🔍 Analyse des relations potentiellement problématiques :');

        $problematicRelations = [];

        // Récupérer toutes les relations acceptées
        $relations = FamilyRelationship::where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get();

        foreach ($relations as $relation) {
            $userGender = $relation->user->profile?->gender;
            $relatedUserGender = $relation->relatedUser->profile?->gender;
            $relationType = $relation->relationshipType->code;

            // Vérifier les incohérences de genre
            $issue = $this->checkGenderConsistency($userGender, $relatedUserGender, $relationType);

            if ($issue) {
                $problematicRelations[] = [
                    'user' => $relation->user->name,
                    'related_user' => $relation->relatedUser->name,
                    'relation' => $relation->relationshipType->name_fr,
                    'issue' => $issue
                ];
            }
        }

        if (empty($problematicRelations)) {
            $this->info('✅ Aucune relation problématique détectée !');
        } else {
            $this->warn("⚠️  " . count($problematicRelations) . " relation(s) potentiellement problématique(s) :");
            $this->table(
                ['Utilisateur', 'Relation avec', 'Type de relation', 'Problème'],
                $problematicRelations
            );
        }
    }

    private function checkGenderConsistency(?string $userGender, ?string $relatedUserGender, string $relationType): ?string
    {
        // Si les genres ne sont pas définis, on ne peut pas vérifier
        if (!$userGender || !$relatedUserGender) {
            return 'Genre non défini pour un ou les deux utilisateurs';
        }

        // Vérifications spécifiques
        switch ($relationType) {
            case 'brother':
                if ($relatedUserGender !== 'male') {
                    return 'Relation "frère" avec une personne non-masculine';
                }
                break;

            case 'sister':
                if ($relatedUserGender !== 'female') {
                    return 'Relation "sœur" avec une personne non-féminine';
                }
                break;

            case 'father':
                if ($userGender !== 'male') {
                    return 'Utilisateur marqué comme "père" mais n\'est pas masculin';
                }
                break;

            case 'mother':
                if ($userGender !== 'female') {
                    return 'Utilisateur marqué comme "mère" mais n\'est pas féminin';
                }
                break;

            case 'son':
                if ($userGender !== 'male') {
                    return 'Utilisateur marqué comme "fils" mais n\'est pas masculin';
                }
                break;

            case 'daughter':
                if ($userGender !== 'female') {
                    return 'Utilisateur marqué comme "fille" mais n\'est pas féminin';
                }
                break;

            case 'husband':
                if ($relatedUserGender !== 'male') {
                    return 'Relation "mari" avec une personne non-masculine';
                }
                break;

            case 'wife':
                if ($relatedUserGender !== 'female') {
                    return 'Relation "épouse" avec une personne non-féminine';
                }
                break;
        }

        return null;
    }

    private function provideSuggestions()
    {
        $this->info('💡 Suggestions d\'amélioration :');

        $nullGenderCount = Profile::whereNull('gender')->orWhere('gender', '')->count();

        if ($nullGenderCount > 0) {
            $this->line("1. Exécuter la commande de mise à jour des genres :");
            $this->line("   php artisan users:update-genders");
            $this->newLine();
        }

        $this->line("2. Encourager les utilisateurs à compléter leur profil");
        $this->line("3. Ajouter une validation obligatoire du genre lors de l'inscription");
        $this->line("4. Implémenter des suggestions intelligentes basées sur le genre");
        $this->line("5. Ajouter des contrôles de cohérence lors de la création de relations");

        $this->newLine();
        $this->info('🎯 Objectif : Atteindre 100% de profils avec genre défini pour optimiser les suggestions');
    }
}
