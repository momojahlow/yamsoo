<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FamilyRelationship;
use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Support\Facades\Log;

class CleanIncorrectFamilyRelations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'family:clean-incorrect-relations {--dry-run : Afficher les relations à supprimer sans les supprimer}';

    /**
     * The console command description.
     */
    protected $description = 'Nettoie les relations familiales incorrectes (comme Fatima apparaissant comme sœur incorrectement)';

    private FamilyRelationService $familyRelationService;

    public function __construct(FamilyRelationService $familyRelationService)
    {
        parent::__construct();
        $this->familyRelationService = $familyRelationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 NETTOYAGE DES RELATIONS FAMILIALES INCORRECTES');
        $this->info('================================================');

        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('⚠️  MODE DRY-RUN : Aucune suppression ne sera effectuée');
        }

        $this->newLine();

        // 1. Nettoyer les relations de fratrie suspectes
        $this->cleanSuspiciousSiblingRelations($dryRun);

        // 2. Nettoyer les relations parent-enfant suspectes
        $this->cleanSuspiciousParentChildRelations($dryRun);

        // 3. Nettoyer les relations automatiques sans justification
        $this->cleanUnjustifiedAutomaticRelations($dryRun);

        $this->newLine();
        $this->info('✅ Nettoyage terminé');
    }

    /**
     * Nettoie les relations de fratrie suspectes
     */
    private function cleanSuspiciousSiblingRelations(bool $dryRun): void
    {
        $this->info('🔍 Recherche des relations de fratrie suspectes...');

        $siblingRelations = FamilyRelationship::whereHas('relationshipType', function($query) {
            $query->whereIn('name', ['brother', 'sister']);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        $suspiciousCount = 0;

        foreach ($siblingRelations as $relation) {
            $user1 = $relation->user;
            $user2 = $relation->relatedUser;

            // Vérifier l'âge
            $age1 = $user1->profile?->birth_date ? now()->diffInYears($user1->profile->birth_date) : null;
            $age2 = $user2->profile?->birth_date ? now()->diffInYears($user2->profile->birth_date) : null;

            $isSuspicious = false;
            $reason = '';

            // Différence d'âge trop importante
            if ($age1 && $age2 && abs($age1 - $age2) > 25) {
                $isSuspicious = true;
                $reason = "Différence d'âge trop importante (" . abs($age1 - $age2) . " ans)";
            }

            // Vérifier s'ils ont des parents en commun
            if (!$isSuspicious) {
                $user1Parents = $this->getUserParents($user1);
                $user2Parents = $this->getUserParents($user2);
                $commonParents = $user1Parents->intersect($user2Parents);

                if ($commonParents->isEmpty()) {
                    $isSuspicious = true;
                    $reason = "Aucun parent en commun trouvé";
                }
            }

            if ($isSuspicious) {
                $suspiciousCount++;
                $this->warn("   ⚠️  {$user1->name} ↔ {$user2->name} : {$relation->relationshipType->display_name_fr}");
                $this->line("      Raison : {$reason}");

                if (!$dryRun) {
                    $relation->delete();
                    $this->line("      ❌ Relation supprimée");
                    
                    Log::info("Relation de fratrie suspecte supprimée", [
                        'user1' => $user1->name,
                        'user2' => $user2->name,
                        'reason' => $reason
                    ]);
                }
            }
        }

        if ($suspiciousCount === 0) {
            $this->info('   ✅ Aucune relation de fratrie suspecte trouvée');
        } else {
            $this->info("   📊 {$suspiciousCount} relation(s) de fratrie suspecte(s) " . ($dryRun ? 'trouvée(s)' : 'supprimée(s)'));
        }
    }

    /**
     * Nettoie les relations parent-enfant suspectes
     */
    private function cleanSuspiciousParentChildRelations(bool $dryRun): void
    {
        $this->info('🔍 Recherche des relations parent-enfant suspectes...');

        $parentChildRelations = FamilyRelationship::whereHas('relationshipType', function($query) {
            $query->whereIn('name', ['father', 'mother', 'son', 'daughter']);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        $suspiciousCount = 0;

        foreach ($parentChildRelations as $relation) {
            $user1 = $relation->user;
            $user2 = $relation->relatedUser;
            $relationType = $relation->relationshipType->name;

            // Déterminer qui est le parent et qui est l'enfant
            $isUser1Parent = in_array($relationType, ['father', 'mother']);
            $parent = $isUser1Parent ? $user1 : $user2;
            $child = $isUser1Parent ? $user2 : $user1;

            $parentAge = $parent->profile?->birth_date ? now()->diffInYears($parent->profile->birth_date) : null;
            $childAge = $child->profile?->birth_date ? now()->diffInYears($child->profile->birth_date) : null;

            $isSuspicious = false;
            $reason = '';

            if ($parentAge && $childAge) {
                $ageDiff = $parentAge - $childAge;
                if ($ageDiff < 15 || $ageDiff > 60) {
                    $isSuspicious = true;
                    $reason = "Différence d'âge inappropriée ({$ageDiff} ans)";
                }
            }

            if ($isSuspicious) {
                $suspiciousCount++;
                $this->warn("   ⚠️  {$user1->name} ↔ {$user2->name} : {$relation->relationshipType->display_name_fr}");
                $this->line("      Raison : {$reason}");

                if (!$dryRun) {
                    $relation->delete();
                    $this->line("      ❌ Relation supprimée");
                    
                    Log::info("Relation parent-enfant suspecte supprimée", [
                        'parent' => $parent->name,
                        'child' => $child->name,
                        'reason' => $reason
                    ]);
                }
            }
        }

        if ($suspiciousCount === 0) {
            $this->info('   ✅ Aucune relation parent-enfant suspecte trouvée');
        } else {
            $this->info("   📊 {$suspiciousCount} relation(s) parent-enfant suspecte(s) " . ($dryRun ? 'trouvée(s)' : 'supprimée(s)'));
        }
    }

    /**
     * Nettoie les relations automatiques sans justification
     */
    private function cleanUnjustifiedAutomaticRelations(bool $dryRun): void
    {
        $this->info('🔍 Recherche des relations automatiques sans justification...');

        $automaticRelations = FamilyRelationship::where('created_automatically', true)
            ->with(['user', 'relatedUser', 'relationshipType'])
            ->get();

        $cleanedCount = 0;

        foreach ($automaticRelations as $relation) {
            // Utiliser la nouvelle logique de validation
            $relationArray = [
                'user_id' => $relation->user_id,
                'related_user_id' => $relation->related_user_id,
                'relationship_type_id' => $relation->relationship_type_id
            ];

            // Utiliser la méthode de validation via réflexion (car elle est privée)
            $reflectionClass = new \ReflectionClass($this->familyRelationService);
            $validateMethod = $reflectionClass->getMethod('validateAutomaticRelation');
            $validateMethod->setAccessible(true);

            $isValid = $validateMethod->invoke($this->familyRelationService, $relationArray);

            if (!$isValid) {
                $cleanedCount++;
                $this->warn("   ⚠️  {$relation->user->name} ↔ {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr}");
                $this->line("      Raison : Validation échouée");

                if (!$dryRun) {
                    $relation->delete();
                    $this->line("      ❌ Relation supprimée");
                    
                    Log::info("Relation automatique injustifiée supprimée", [
                        'user1' => $relation->user->name,
                        'user2' => $relation->relatedUser->name,
                        'relation_type' => $relation->relationshipType->display_name_fr
                    ]);
                }
            }
        }

        if ($cleanedCount === 0) {
            $this->info('   ✅ Aucune relation automatique injustifiée trouvée');
        } else {
            $this->info("   📊 {$cleanedCount} relation(s) automatique(s) injustifiée(s) " . ($dryRun ? 'trouvée(s)' : 'supprimée(s)'));
        }
    }

    /**
     * Obtient les parents d'un utilisateur
     */
    private function getUserParents(User $user): \Illuminate\Support\Collection
    {
        return FamilyRelationship::where('user_id', $user->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('name', ['father', 'mother']);
            })
            ->with('relatedUser')
            ->get()
            ->pluck('relatedUser');
    }
}
