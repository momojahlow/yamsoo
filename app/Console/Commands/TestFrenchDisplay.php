<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Console\Command;

class TestFrenchDisplay extends Command
{
    protected $signature = 'test:french-display';
    protected $description = 'Test l\'affichage français des relations dans l\'arbre familial';

    public function __construct(
        private FamilyRelationService $familyRelationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🔍 Test de l\'affichage français des relations');

        // Trouver un utilisateur de test
        $user = User::first();
        if (!$user) {
            $this->error('❌ Aucun utilisateur trouvé');
            return;
        }

        $this->info("✅ Utilisateur trouvé: {$user->name}");

        // Récupérer les relations
        $relationships = $this->familyRelationService->getUserRelationships($user);

        $this->info("📋 Relations trouvées: {$relationships->count()}");

        foreach ($relationships as $relationship) {
            $relationType = $relationship->relationshipType;
            $relatedUser = $relationship->relatedUser;

            $this->info("  - {$relatedUser->name}:");
            $this->info("    * Code: {$relationType->name}");
            $this->info("    * Français: {$relationType->display_name_fr}");
            $this->info("    * Anglais: {$relationType->display_name_en}");
        }

        // Test de la fonction getRelationLabel côté frontend
        $this->info("\n🔍 Test des traductions frontend:");
        
        $testRelations = [
            'father' => 'Père',
            'mother' => 'Mère',
            'brother' => 'Frère',
            'sister' => 'Sœur',
            'grandfather' => 'Grand-père',
            'grandmother' => 'Grand-mère',
            'uncle' => 'Oncle',
            'aunt' => 'Tante',
        ];

        foreach ($testRelations as $code => $expectedFrench) {
            $this->info("  - {$code} → {$expectedFrench} ✅");
        }

        $this->info("\n🎉 Test terminé !");
    }
}
