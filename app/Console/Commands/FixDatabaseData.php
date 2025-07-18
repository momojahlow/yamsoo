<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;

class FixDatabaseData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fix:database-data';

    /**
     * The description of the console command.
     */
    protected $description = 'Corriger les données incohérentes dans la base';

    public function handle()
    {
        $this->info('🔧 CORRECTION DES DONNÉES DE LA BASE');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        // 1. Corriger les genres
        $this->fixGenders();
        $this->newLine();

        // 2. Analyser les relations problématiques
        $this->analyzeProblematicRelationships();
        $this->newLine();

        // 3. Proposer des corrections
        $this->proposeCorrections();

        return 0;
    }

    private function fixGenders(): void
    {
        $this->info('👥 CORRECTION DES GENRES :');

        $corrections = [
            'fatima.zahra@example.com' => 'female',
            'amina.tazi@example.com' => 'female',
            'aicha.idrissi@example.com' => 'female',
        ];

        foreach ($corrections as $email => $correctGender) {
            $user = User::where('email', $email)->first();
            if ($user && $user->profile) {
                $oldGender = $user->profile->gender;
                $user->profile->update(['gender' => $correctGender]);
                $this->line("   ✅ {$user->name} : {$oldGender} → {$correctGender}");
            }
        }
    }

    private function analyzeProblematicRelationships(): void
    {
        $this->info('🔍 ANALYSE DES RELATIONS PROBLÉMATIQUES :');

        $relationships = FamilyRelationship::with(['user.profile', 'relatedUser.profile', 'relationshipType'])->get();

        foreach ($relationships as $relation) {
            $user = $relation->user;
            $relatedUser = $relation->relatedUser;
            $type = $relation->relationshipType;

            // Vérifier la cohérence genre/relation
            $issues = $this->checkRelationshipConsistency($user, $relatedUser, $type->code);
            
            if (!empty($issues)) {
                $this->line("   ⚠️  {$user->name} → {$relatedUser->name} : {$type->name_fr}");
                foreach ($issues as $issue) {
                    $this->line("      - {$issue}");
                }
            }
        }
    }

    private function checkRelationshipConsistency(User $user, User $relatedUser, string $relationCode): array
    {
        $issues = [];
        $userGender = $user->profile?->gender;
        $relatedGender = $relatedUser->profile?->gender;

        // Vérifications de cohérence
        switch ($relationCode) {
            case 'wife':
                if ($relatedGender !== 'female') {
                    $issues[] = "Une épouse doit être de genre féminin";
                }
                break;
            case 'husband':
                if ($relatedGender !== 'male') {
                    $issues[] = "Un mari doit être de genre masculin";
                }
                break;
            case 'father':
                if ($relatedGender !== 'male') {
                    $issues[] = "Un père doit être de genre masculin";
                }
                break;
            case 'mother':
                if ($relatedGender !== 'female') {
                    $issues[] = "Une mère doit être de genre féminin";
                }
                break;
            case 'son':
                if ($relatedGender !== 'male') {
                    $issues[] = "Un fils doit être de genre masculin";
                }
                break;
            case 'daughter':
                if ($relatedGender !== 'female') {
                    $issues[] = "Une fille doit être de genre féminin";
                }
                break;
        }

        return $issues;
    }

    private function proposeCorrections(): void
    {
        $this->info('💡 PROPOSITIONS DE CORRECTIONS :');

        $relationships = FamilyRelationship::with(['user.profile', 'relatedUser.profile', 'relationshipType'])->get();

        foreach ($relationships as $relation) {
            $user = $relation->user;
            $relatedUser = $relation->relatedUser;
            $type = $relation->relationshipType;

            $correction = $this->suggestCorrection($user, $relatedUser, $type->code);
            
            if ($correction) {
                $this->line("   🔄 {$user->name} → {$relatedUser->name} :");
                $this->line("      Actuel : {$type->name_fr}");
                $this->line("      Suggéré : {$correction}");
            }
        }
    }

    private function suggestCorrection(User $user, User $relatedUser, string $relationCode): ?string
    {
        $userGender = $user->profile?->gender;
        $relatedGender = $relatedUser->profile?->gender;

        // Suggestions basées sur les genres
        if ($relationCode === 'wife' && $relatedGender === 'male') {
            return 'Mari';
        }
        
        if ($relationCode === 'husband' && $relatedGender === 'female') {
            return 'Épouse';
        }

        if ($relationCode === 'son' && $relatedGender === 'female') {
            return 'Fille';
        }

        if ($relationCode === 'daughter' && $relatedGender === 'male') {
            return 'Fils';
        }

        if ($relationCode === 'father' && $relatedGender === 'female') {
            return 'Mère';
        }

        if ($relationCode === 'mother' && $relatedGender === 'male') {
            return 'Père';
        }

        return null;
    }
}
