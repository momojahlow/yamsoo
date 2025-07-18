<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\FamilyTreeController;
use Illuminate\Http\Request;

class TestFamilyTree extends Command
{
    protected $signature = 'test:family-tree';
    protected $description = 'Tester le contrôleur de l\'arbre familial';

    public function handle()
    {
        $this->info('🌳 TEST DU CONTRÔLEUR ARBRE FAMILIAL');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        // Récupérer un utilisateur de test
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        
        if (!$fatima) {
            $this->error('❌ Utilisateur Fatima non trouvé');
            return 1;
        }

        $this->info("👩 Test avec l'utilisateur : {$fatima->name}");
        $this->newLine();

        try {
            // Créer une instance du contrôleur
            $controller = app(FamilyTreeController::class);
            
            // Créer une requête mock
            $request = Request::create('/famille/arbre', 'GET');
            $request->setUserResolver(function () use ($fatima) {
                return $fatima;
            });

            // Appeler la méthode index
            $response = $controller->index($request);
            
            $this->info('✅ Contrôleur appelé avec succès');
            
            // Vérifier les données retournées
            $props = $response->toResponse($request)->getData()['props'] ?? [];
            
            $this->info('📊 DONNÉES RETOURNÉES :');
            
            if (isset($props['user'])) {
                $this->line("   👤 Utilisateur : {$props['user']['name']}");
            }
            
            if (isset($props['treeData'])) {
                $treeData = $props['treeData'];
                $this->line("   🌳 Données de l'arbre :");
                $this->line("      - Parents : " . count($treeData['parents'] ?? []));
                $this->line("      - Conjoint : " . ($treeData['spouse'] ? '1' : '0'));
                $this->line("      - Enfants : " . count($treeData['children'] ?? []));
                $this->line("      - Frères/Sœurs : " . count($treeData['siblings'] ?? []));
                $this->line("      - Grands-parents paternels : " . count($treeData['grandparents']['paternal'] ?? []));
                $this->line("      - Grands-parents maternels : " . count($treeData['grandparents']['maternal'] ?? []));
            }
            
            if (isset($props['relationships'])) {
                $relationships = $props['relationships'];
                $this->line("   🔗 Relations : " . count($relationships));
                
                foreach ($relationships as $relation) {
                    $auto = $relation['created_automatically'] ? ' 🤖' : ' 👤';
                    $this->line("      - {$relation['type']} : {$relation['related_user']['name']}{$auto}");
                }
            }
            
            if (isset($props['statistics'])) {
                $stats = $props['statistics'];
                $this->line("   📈 Statistiques :");
                $this->line("      - Total relations : {$stats['total_relatives']}");
            }
            
            $this->newLine();
            $this->info('✅ Test du contrôleur réussi !');
            $this->info('🌐 Vous pouvez maintenant visiter : https://yamsoo.test/famille/arbre');
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du test du contrôleur :');
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
