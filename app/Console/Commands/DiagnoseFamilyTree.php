<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;

class DiagnoseFamilyTree extends Command
{
    protected $signature = 'diagnose:family-tree {user_name?}';
    protected $description = 'Diagnostiquer l\'arbre familial d\'un utilisateur';

    public function handle()
    {
        $userName = $this->argument('user_name') ?? 'Nadia';
        
        $this->info('🔍 DIAGNOSTIC DE L\'ARBRE FAMILIAL');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Trouver l'utilisateur
        $user = User::where('name', 'like', "%{$userName}%")->first();
        
        if (!$user) {
            $this->error("❌ Utilisateur '{$userName}' non trouvé");
            return 1;
        }

        $this->info("👤 UTILISATEUR : {$user->name} (ID: {$user->id})");
        $this->newLine();

        // Obtenir toutes les relations
        $this->info('1️⃣ RELATIONS FAMILIALES DIRECTES :');
        $relationships = FamilyRelationship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        if ($relationships->isEmpty()) {
            $this->line("   ⚠️  Aucune relation familiale trouvée");
        } else {
            foreach ($relationships as $relation) {
                $relatedUser = $relation->relatedUser;
                $relationType = $relation->relationshipType;
                $autoFlag = $relation->created_automatically ? ' 🤖' : '';
                $this->line("   • {$relatedUser->name} → {$relationType->name_fr} ({$relationType->code}){$autoFlag}");
            }
        }
        $this->newLine();

        // Utiliser le service pour obtenir les données de l'arbre
        $this->info('2️⃣ DONNÉES DE L\'ARBRE FAMILIAL :');
        $familyService = app(FamilyRelationService::class);
        $allRelationships = $familyService->getUserRelationships($user);
        
        $this->line("   📊 Total relations via service : {$allRelationships->count()}");
        
        // Simuler la construction de l'arbre comme dans le contrôleur
        $treeData = [
            'parents' => [],
            'spouse' => null,
            'children' => [],
            'siblings' => [],
            'grandparents' => ['paternal' => [], 'maternal' => []],
            'uncles_aunts' => ['paternal' => [], 'maternal' => []],
            'grandchildren' => [],
            'cousins' => [],
        ];

        foreach ($allRelationships as $relationship) {
            $relationCode = $relationship->relationshipType->code;
            $relatedUser = $relationship->relatedUser;

            switch ($relationCode) {
                case 'father':
                case 'mother':
                case 'father_in_law':
                case 'mother_in_law':
                    $treeData['parents'][] = $relatedUser->name . " ({$relationCode})";
                    break;

                case 'husband':
                case 'wife':
                    $treeData['spouse'] = $relatedUser->name . " ({$relationCode})";
                    break;

                case 'son':
                case 'daughter':
                case 'stepson':
                case 'stepdaughter':
                    $treeData['children'][] = $relatedUser->name . " ({$relationCode})";
                    break;

                case 'brother':
                case 'sister':
                case 'brother_in_law':
                case 'sister_in_law':
                    $treeData['siblings'][] = $relatedUser->name . " ({$relationCode})";
                    break;
            }
        }

        // Afficher les catégories
        $this->line("   👨‍👩 Parents : " . (empty($treeData['parents']) ? 'Aucun' : implode(', ', $treeData['parents'])));
        $this->line("   💑 Conjoint : " . ($treeData['spouse'] ?: 'Aucun'));
        $this->line("   👶 Enfants : " . (empty($treeData['children']) ? 'Aucun' : implode(', ', $treeData['children'])));
        $this->line("   👫 Frères/Sœurs : " . (empty($treeData['siblings']) ? 'Aucun' : implode(', ', $treeData['siblings'])));
        $this->newLine();

        // Vérifier spécifiquement les relations par alliance
        $this->info('3️⃣ RELATIONS PAR ALLIANCE (BELLE-FAMILLE) :');
        $inLawRelations = $allRelationships->filter(function($rel) {
            return str_contains($rel->relationshipType->code, '_in_law') || 
                   str_starts_with($rel->relationshipType->code, 'step');
        });

        if ($inLawRelations->isEmpty()) {
            $this->line("   ⚠️  Aucune relation par alliance trouvée");
        } else {
            foreach ($inLawRelations as $relation) {
                $relatedUser = $relation->relatedUser;
                $relationType = $relation->relationshipType;
                $autoFlag = $relation->created_automatically ? ' 🤖' : '';
                $this->line("   💍 {$relatedUser->name} → {$relationType->name_fr} ({$relationType->code}){$autoFlag}");
            }
        }
        $this->newLine();

        // Statistiques
        $this->info('4️⃣ STATISTIQUES :');
        $stats = $familyService->getFamilyStatistics($user);
        $this->line("   📊 Total relations : {$stats['total_relatives']}");
        
        if (isset($stats['by_type'])) {
            foreach ($stats['by_type'] as $type => $count) {
                $this->line("      • {$type} : {$count}");
            }
        }

        $this->newLine();
        $this->info('🎯 DIAGNOSTIC TERMINÉ !');

        // Recommandations
        if ($inLawRelations->isEmpty() && $allRelationships->isNotEmpty()) {
            $this->newLine();
            $this->warn('💡 RECOMMANDATION :');
            $this->line('   Les relations par alliance ne sont pas visibles dans l\'arbre.');
            $this->line('   Vérifiez que le contrôleur FamilyTreeController inclut bien ces relations.');
        }

        return 0;
    }
}
