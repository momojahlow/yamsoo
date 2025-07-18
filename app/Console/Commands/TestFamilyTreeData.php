<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\FamilyRelationService;

class TestFamilyTreeData extends Command
{
    protected $signature = 'test:family-tree-data';
    protected $description = 'Tester les données de l\'arbre familial';

    protected FamilyRelationService $familyRelationService;

    public function __construct(FamilyRelationService $familyRelationService)
    {
        parent::__construct();
        $this->familyRelationService = $familyRelationService;
    }

    public function handle()
    {
        $this->info('🌳 TEST DES DONNÉES DE L\'ARBRE FAMILIAL');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        // Tester avec Youssef qui a le plus de relations
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();
        
        if (!$youssef) {
            $this->error('❌ Utilisateur Youssef non trouvé');
            return 1;
        }

        $this->info("👨 Test avec l'utilisateur : {$youssef->name}");
        $this->newLine();

        // Obtenir les relations
        $relationships = $this->familyRelationService->getUserRelationships($youssef);
        $this->info("🔗 Relations de Youssef : {$relationships->count()}");
        
        foreach ($relationships as $relation) {
            $relatedUser = $relation->relatedUser;
            $type = $relation->relationshipType;
            $auto = $relation->created_automatically ? ' 🤖' : ' 👤';
            $gender = $relatedUser->profile?->gender === 'female' ? '👩' : '👨';
            
            $this->line("   - {$type->name_fr} : {$gender} {$relatedUser->name}{$auto}");
        }

        $this->newLine();

        // Obtenir les statistiques
        $statistics = $this->familyRelationService->getFamilyStatistics($youssef);
        $this->info("📊 STATISTIQUES :");
        $this->line("   - Total relations : {$statistics['total_relatives']}");
        $this->line("   - Relations automatiques : {$statistics['automatic_relations']}");
        $this->line("   - Relations manuelles : {$statistics['manual_relations']}");
        
        $this->newLine();
        $this->info("📈 PAR TYPE :");
        foreach ($statistics['by_type'] as $type => $count) {
            $this->line("   - {$type} : {$count}");
        }

        $this->newLine();
        $this->info("🏗️  PAR GÉNÉRATION :");
        $this->line("   - Ancêtres : {$statistics['by_generation']['ancestors']}");
        $this->line("   - Même génération : {$statistics['by_generation']['same_generation']}");
        $this->line("   - Descendants : {$statistics['by_generation']['descendants']}");

        $this->newLine();
        $this->info('✅ Test des données terminé !');
        $this->info('🌐 Visitez maintenant : https://yamsoo.test/famille/arbre');
        
        return 0;
    }
}
