<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\FamilyTreeController;
use Illuminate\Http\Request;

class TestFamilyTreeAPI extends Command
{
    protected $signature = 'test:family-tree-api';
    protected $description = 'Test l\'API de l\'arbre familial';

    public function handle()
    {
        $this->info("🔍 Test de l'API de l'arbre familial");
        
        try {
            // Récupérer un utilisateur avec des relations
            $user = User::where('email', 'test@example.com')->first();
            
            if (!$user) {
                $this->error("❌ Utilisateur de test non trouvé");
                return;
            }
            
            $this->info("✅ Utilisateur trouvé: {$user->name}");
            
            // Créer une fausse requête
            $request = new Request();
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            
            // Tester l'API
            $controller = app(FamilyTreeController::class);
            $response = $controller->getFamilyRelations($request);
            
            $data = $response->getData(true);
            
            $this->info("📋 Réponse API:");
            $this->info("- Utilisateur ID: " . $data['userId']);
            $this->info("- Nombre de relations: " . count($data['relations']));
            
            foreach ($data['relations'] as $relation) {
                $relatedUser = $relation['related_user'];
                $this->line("  - {$relatedUser['name']} : {$relation['relation_type']} (statut: {$relation['status']})");
            }
            
            $this->info("✅ API fonctionne correctement !");
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            $this->error("📍 Fichier: " . $e->getFile() . ":" . $e->getLine());
        }
    }
}
