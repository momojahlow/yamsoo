<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Services\SimpleRelationshipInferenceService;
use App\Services\FamilyRelationService;
use Illuminate\Console\Command;

class ForceDeductions extends Command
{
    protected $signature = 'family:force-deductions';
    protected $description = 'Force les déductions manquantes pour toutes les relations existantes';

    public function __construct(
        private SimpleRelationshipInferenceService $simpleRelationshipInferenceService,
        private FamilyRelationService $familyRelationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🔄 Forçage des déductions manquantes');
        
        // Obtenir toutes les relations existantes
        $allRelationships = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
        
        $totalDeductions = 0;
        
        foreach ($allRelationships as $relationship) {
            $user = $relationship->user;
            $relatedUser = $relationship->relatedUser;
            $relationshipType = $relationship->relationshipType;
            
            if (!$user || !$relatedUser || !$relationshipType) {
                continue;
            }
            
            $this->info("🔍 Analyse relation: {$user->name} → {$relatedUser->name} ({$relationshipType->display_name_fr})");
            
            // Déduire les relations pour l'utilisateur
            $deducedForUser = $this->simpleRelationshipInferenceService->deduceRelationships(
                $user,
                $relatedUser,
                $relationshipType->name
            );
            
            if ($deducedForUser->isNotEmpty()) {
                $this->info("  📝 {$deducedForUser->count()} déductions trouvées pour {$user->name}");
                $created = $this->createDeducedRelationships($deducedForUser);
                $totalDeductions += $created;
            }
            
            // Déduire les relations pour l'utilisateur lié
            $deducedForRelated = $this->simpleRelationshipInferenceService->deduceRelationships(
                $relatedUser,
                $user,
                $this->getInverseRelationCode($relationshipType->name, $user, $relatedUser)
            );
            
            if ($deducedForRelated->isNotEmpty()) {
                $this->info("  📝 {$deducedForRelated->count()} déductions trouvées pour {$relatedUser->name}");
                $created = $this->createDeducedRelationships($deducedForRelated);
                $totalDeductions += $created;
            }
        }
        
        $this->info("\n🎉 Déductions terminées ! {$totalDeductions} nouvelles relations créées.");
    }

    private function createDeducedRelationships($deducedRelations): int
    {
        $created = 0;

        foreach ($deducedRelations as $relation) {
            try {
                // Vérifier que la relation n'existe pas déjà
                $exists = FamilyRelationship::where('user_id', $relation['user_id'])
                    ->where('related_user_id', $relation['related_user_id'])
                    ->exists();

                if (!$exists) {
                    FamilyRelationship::create([
                        'user_id' => $relation['user_id'],
                        'related_user_id' => $relation['related_user_id'],
                        'relationship_type_id' => $relation['relationship_type_id'],
                        'status' => 'accepted',
                        'created_automatically' => true
                    ]);

                    $created++;
                    $this->info("    ✅ " . ($relation['reason'] ?? 'Déduction automatique'));
                }
            } catch (\Exception $e) {
                $this->error("    ❌ Erreur: " . $e->getMessage());
            }
        }

        return $created;
    }

    private function getInverseRelationCode(string $relationCode, User $user1, User $user2): string
    {
        $inverseMap = [
            'husband' => 'wife',
            'wife' => 'husband',
            'father' => $user2->profile?->gender === 'male' ? 'son' : 'daughter',
            'mother' => $user2->profile?->gender === 'male' ? 'son' : 'daughter',
            'son' => $user1->profile?->gender === 'male' ? 'father' : 'mother',
            'daughter' => $user1->profile?->gender === 'male' ? 'father' : 'mother',
            'brother' => $user2->profile?->gender === 'male' ? 'brother' : 'sister',
            'sister' => $user2->profile?->gender === 'male' ? 'brother' : 'sister',
        ];

        return $inverseMap[$relationCode] ?? $relationCode;
    }
}
