<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use App\Services\IntelligentRelationshipService;

class FixGrandparentRelations extends Command
{
    protected $signature = 'family:fix-grandparent-relations';
    protected $description = 'Corrige les relations grand-parent/petit-enfant manquantes';

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
        $this->info('🔧 Correction des relations grand-parent/petit-enfant...');

        // Trouver toutes les relations parent-enfant
        $parentChildRelations = FamilyRelationship::whereHas('relationshipType', function ($query) {
            $query->whereIn('name', ['father', 'mother', 'son', 'daughter']);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        $this->info("📊 Trouvé {$parentChildRelations->count()} relations parent-enfant");

        $fixedCount = 0;

        foreach ($parentChildRelations as $relation) {
            $parent = null;
            $child = null;

            // Déterminer qui est le parent et qui est l'enfant
            if (in_array($relation->relationshipType->name, ['father', 'mother'])) {
                $parent = $relation->user;
                $child = $relation->relatedUser;
            } elseif (in_array($relation->relationshipType->name, ['son', 'daughter'])) {
                $parent = $relation->relatedUser;
                $child = $relation->user;
            }

            if (!$parent || !$child) continue;

            // Chercher les parents du parent (grands-parents potentiels)
            $grandparents = FamilyRelationship::where('related_user_id', $parent->id)
                ->whereHas('relationshipType', function ($query) {
                    $query->whereIn('name', ['father', 'mother']);
                })
                ->with(['user', 'relationshipType'])
                ->get();

            foreach ($grandparents as $grandparentRelation) {
                $grandparent = $grandparentRelation->user;
                
                // Vérifier si la relation grand-parent/petit-enfant existe déjà
                $existingGrandparentRelation = FamilyRelationship::where(function ($query) use ($grandparent, $child) {
                    $query->where('user_id', $grandparent->id)
                          ->where('related_user_id', $child->id);
                })->orWhere(function ($query) use ($grandparent, $child) {
                    $query->where('user_id', $child->id)
                          ->where('related_user_id', $grandparent->id);
                })->exists();

                if (!$existingGrandparentRelation) {
                    $this->createGrandparentRelation($grandparent, $child, $grandparentRelation->relationshipType->name);
                    $fixedCount++;
                }
            }
        }

        $this->info("✅ {$fixedCount} relations grand-parent/petit-enfant créées");
        
        // Relancer les déductions pour s'assurer que tout est cohérent
        $this->info('🔄 Relancement des déductions intelligentes...');
        $this->runIntelligentDeductions();
        
        $this->info('🎉 Correction terminée !');
    }

    private function createGrandparentRelation(User $grandparent, User $grandchild, string $grandparentType)
    {
        // Déterminer le type de relation grand-parent/petit-enfant
        $grandparentRelationType = null;
        $grandchildRelationType = null;

        if ($grandparentType === 'father') {
            $grandparentRelationType = RelationshipType::where('name', 'grandfather_paternal')->first();
            $grandchildRelationType = RelationshipType::where('name', 
                $grandchild->profile?->gender === 'female' ? 'granddaughter' : 'grandson'
            )->first();
        } elseif ($grandparentType === 'mother') {
            $grandparentRelationType = RelationshipType::where('name', 'grandmother_paternal')->first();
            $grandchildRelationType = RelationshipType::where('name', 
                $grandchild->profile?->gender === 'female' ? 'granddaughter' : 'grandson'
            )->first();
        }

        if ($grandparentRelationType && $grandchildRelationType) {
            // Créer la relation grand-parent → petit-enfant
            FamilyRelationship::create([
                'user_id' => $grandparent->id,
                'related_user_id' => $grandchild->id,
                'relationship_type_id' => $grandparentRelationType->id,
                'status' => 'accepted',
                'accepted_at' => now(),
                'created_automatically' => true,
            ]);

            // Créer la relation petit-enfant → grand-parent
            FamilyRelationship::create([
                'user_id' => $grandchild->id,
                'related_user_id' => $grandparent->id,
                'relationship_type_id' => $grandchildRelationType->id,
                'status' => 'accepted',
                'accepted_at' => now(),
                'created_automatically' => true,
            ]);

            $this->line("   ✅ {$grandparent->name} ↔ {$grandchild->name} (grand-parent/petit-enfant)");
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
                $deducedRelations = $this->intelligentService->deduceRelationships(
                    $user,
                    $relation->relatedUser,
                    $relation->relationshipType->name
                );

                if ($deducedRelations->isNotEmpty()) {
                    $this->intelligentService->createDeducedRelationships($deducedRelations);
                }
            }
        }
    }
}
