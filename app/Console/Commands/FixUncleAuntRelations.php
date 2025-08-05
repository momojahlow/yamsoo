<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use App\Services\IntelligentRelationshipService;

class FixUncleAuntRelations extends Command
{
    protected $signature = 'family:fix-uncle-aunt-relations';
    protected $description = 'Corrige les relations oncle/tante manquantes';

    private FamilyRelationService $familyRelationService;
    private IntelligentRelationshipService $intelligentService;

    public function __construct(
        FamilyRelationService $familyRelationService,
        IntelligentRelationshipService $intelligentService
    ) {
        parent::__construct();
        $this->familyRelationService = $familyRelationService;
        $this->intelligentService = $intelligentService;
    }

    public function handle()
    {
        $this->info('🔧 Correction des relations oncle/tante...');

        $fixedCount = 0;

        // Trouver toutes les relations parent-enfant
        $parentChildRelations = FamilyRelationship::whereHas('relationshipType', function ($query) {
            $query->whereIn('name', ['father', 'mother', 'son', 'daughter']);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        $this->info("📊 Trouvé {$parentChildRelations->count()} relations parent-enfant");

        foreach ($parentChildRelations as $relation) {
            $parent = null;
            $child = null;

            // Déterminer qui est le parent et qui est l'enfant
            if (in_array($relation->relationshipType->name, ['father', 'mother'])) {
                $parent = $relation->user;
                $child = $relation->relatedUser;
                $parentType = $relation->relationshipType->name;
            } elseif (in_array($relation->relationshipType->name, ['son', 'daughter'])) {
                $parent = $relation->relatedUser;
                $child = $relation->user;
                // Déterminer le type de parent en regardant la relation inverse
                $parentRelation = FamilyRelationship::where('user_id', $parent->id)
                    ->where('related_user_id', $child->id)
                    ->whereHas('relationshipType', function ($query) {
                        $query->whereIn('name', ['father', 'mother']);
                    })
                    ->with('relationshipType')
                    ->first();
                $parentType = $parentRelation ? $parentRelation->relationshipType->name : null;
            }

            if (!$parent || !$child || !$parentType) continue;

            // Chercher les frères et sœurs du parent (oncles/tantes potentiels)
            $siblings = FamilyRelationship::where('related_user_id', $parent->id)
                ->whereHas('relationshipType', function ($query) {
                    $query->whereIn('name', ['brother', 'sister']);
                })
                ->with(['user', 'relationshipType'])
                ->get();

            foreach ($siblings as $siblingRelation) {
                $sibling = $siblingRelation->user;
                $siblingType = $siblingRelation->relationshipType->name;
                
                // Vérifier si la relation oncle/tante existe déjà
                $existingUncleAuntRelation = FamilyRelationship::where(function ($query) use ($sibling, $child) {
                    $query->where('user_id', $sibling->id)
                          ->where('related_user_id', $child->id);
                })->orWhere(function ($query) use ($sibling, $child) {
                    $query->where('user_id', $child->id)
                          ->where('related_user_id', $sibling->id);
                })->whereHas('relationshipType', function ($query) {
                    $query->whereIn('name', ['uncle', 'aunt', 'uncle_paternal', 'aunt_paternal', 'uncle_maternal', 'aunt_maternal', 'nephew', 'niece']);
                })->exists();

                if (!$existingUncleAuntRelation) {
                    $this->createUncleAuntRelation($sibling, $child, $siblingType, $parentType);
                    $fixedCount++;
                }
            }
        }

        $this->info("✅ {$fixedCount} relations oncle/tante créées");
        
        // Relancer les déductions pour s'assurer que tout est cohérent
        $this->info('🔄 Relancement des déductions intelligentes...');
        $this->runIntelligentDeductions();
        
        $this->info('🎉 Correction terminée !');
    }

    private function createUncleAuntRelation(User $sibling, User $child, string $siblingType, string $parentType)
    {
        // Déterminer le type de relation oncle/tante
        $uncleAuntType = null;
        $nephewNieceType = null;

        // Déterminer si c'est paternel ou maternel
        $side = ($parentType === 'father') ? 'paternal' : 'maternal';

        if ($siblingType === 'brother') {
            $uncleAuntType = RelationshipType::where('name', "uncle_{$side}")->first() 
                ?? RelationshipType::where('name', 'uncle')->first();
        } elseif ($siblingType === 'sister') {
            $uncleAuntType = RelationshipType::where('name', "aunt_{$side}")->first()
                ?? RelationshipType::where('name', 'aunt')->first();
        }

        $nephewNieceType = RelationshipType::where('name', 
            $child->profile?->gender === 'female' ? 'niece' : 'nephew'
        )->first();

        if ($uncleAuntType && $nephewNieceType) {
            // Créer la relation oncle/tante → neveu/nièce
            FamilyRelationship::create([
                'user_id' => $sibling->id,
                'related_user_id' => $child->id,
                'relationship_type_id' => $uncleAuntType->id,
                'status' => 'accepted',
                'accepted_at' => now(),
                'created_automatically' => true,
            ]);

            // Créer la relation neveu/nièce → oncle/tante
            FamilyRelationship::create([
                'user_id' => $child->id,
                'related_user_id' => $sibling->id,
                'relationship_type_id' => $nephewNieceType->id,
                'status' => 'accepted',
                'accepted_at' => now(),
                'created_automatically' => true,
            ]);

            $relationLabel = $uncleAuntType->name;
            $this->line("   ✅ {$sibling->name} ↔ {$child->name} ({$relationLabel}/neveu-nièce)");
        }
    }

    private function runIntelligentDeductions()
    {
        $users = User::whereHas('familyRelationships')->get();
        
        foreach ($users as $user) {
            $relations = FamilyRelationship::where('user_id', $user->id)
                ->where('status', 'accepted')
                ->with(['relationshipType', 'relatedUser'])
                ->get();

            foreach ($relations as $relation) {
                try {
                    $deducedRelations = $this->intelligentService->deduceRelationships(
                        $user,
                        $relation->relatedUser,
                        $relation->relationshipType->name
                    );

                    if ($deducedRelations->isNotEmpty()) {
                        $this->intelligentService->createDeducedRelationships($deducedRelations);
                    }
                } catch (\Exception $e) {
                    // Ignorer les erreurs de déduction pour continuer le processus
                    $this->warn("Erreur de déduction pour {$user->name}: " . $e->getMessage());
                }
            }
        }
    }
}
