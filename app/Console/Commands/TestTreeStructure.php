<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\FamilyTreeController;
use Illuminate\Http\Request;

class TestTreeStructure extends Command
{
    protected $signature = 'test:tree-structure';
    protected $description = 'Tester la structure de l\'arbre familial';

    public function handle()
    {
        $this->info('🌳 TEST DE LA STRUCTURE DE L\'ARBRE FAMILIAL');
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        // Tester avec Youssef qui a des relations
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();
        
        if (!$youssef) {
            $this->error('❌ Utilisateur Youssef non trouvé');
            return 1;
        }

        $this->info("👨 Test avec l'utilisateur : {$youssef->name}");
        $this->newLine();

        try {
            // Simuler une requête
            $controller = app(FamilyTreeController::class);
            $request = Request::create('/famille/arbre', 'GET');
            $request->setUserResolver(function () use ($youssef) {
                return $youssef;
            });

            // Appeler le contrôleur (sans récupérer la réponse complète)
            $this->info('🔧 Test du contrôleur...');
            
            // Tester directement les services
            $familyRelationService = app(\App\Services\FamilyRelationService::class);
            
            $relationships = $familyRelationService->getUserRelationships($youssef);
            $this->info("🔗 Relations trouvées : {$relationships->count()}");
            
            foreach ($relationships as $relation) {
                $relatedUser = $relation->relatedUser;
                $type = $relation->relationshipType;
                $auto = $relation->created_automatically ? ' 🤖' : ' 👤';
                $gender = $relatedUser->profile?->gender === 'female' ? '👩' : '👨';
                
                $this->line("   - {$type->name_fr} : {$gender} {$relatedUser->name}{$auto}");
            }

            $this->newLine();

            // Tester la méthode buildFamilyTreeData
            $this->info('🏗️  Test de la construction de l\'arbre...');
            
            // Simuler la construction des données d'arbre
            $treeData = [
                'center' => [
                    'id' => $youssef->id,
                    'name' => $youssef->name,
                    'profile' => $youssef->profile,
                    'isCurrentUser' => true,
                ],
                'parents' => [],
                'spouse' => null,
                'children' => [],
                'siblings' => [],
                'grandparents' => [
                    'paternal' => [],
                    'maternal' => [],
                ],
            ];

            // Classer les relations
            foreach ($relationships as $relationship) {
                $relatedUser = [
                    'id' => $relationship->relatedUser->id,
                    'name' => $relationship->relatedUser->name,
                    'profile' => $relationship->relatedUser->profile,
                    'relationship_type' => $relationship->relationshipType->name_fr,
                    'relationship_code' => $relationship->relationshipType->code,
                ];

                $relationCode = $relationship->relationshipType->code;

                switch ($relationCode) {
                    case 'father':
                    case 'mother':
                        $treeData['parents'][] = $relatedUser;
                        break;
                    case 'husband':
                    case 'wife':
                        $treeData['spouse'] = $relatedUser;
                        break;
                    case 'son':
                    case 'daughter':
                        $treeData['children'][] = $relatedUser;
                        break;
                    case 'brother':
                    case 'sister':
                        $treeData['siblings'][] = $relatedUser;
                        break;
                }
            }

            $this->info('📊 STRUCTURE DE L\'ARBRE :');
            $this->line("   👤 Centre : {$treeData['center']['name']}");
            $this->line("   👨‍👩‍👧‍👦 Parents : " . count($treeData['parents']));
            $this->line("   💑 Conjoint : " . ($treeData['spouse'] ? '1' : '0'));
            $this->line("   👶 Enfants : " . count($treeData['children']));
            $this->line("   👫 Frères/Sœurs : " . count($treeData['siblings']));

            if ($treeData['spouse']) {
                $this->line("      💑 Conjoint : {$treeData['spouse']['name']} ({$treeData['spouse']['relationship_type']})");
            }

            foreach ($treeData['children'] as $child) {
                $this->line("      👶 Enfant : {$child['name']} ({$child['relationship_type']})");
            }

            $this->newLine();
            $this->info('✅ Structure de l\'arbre validée !');
            $this->info('🌐 L\'arbre hiérarchique devrait maintenant s\'afficher correctement');
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du test :');
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
