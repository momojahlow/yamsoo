<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Console\Command;

class TestParentChildRelations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:parent-child-relations';

    /**
     * The console command description.
     */
    protected $description = 'Teste les relations parent-enfant avec le genre correct';

    protected FamilyRelationService $familyRelationService;

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
        $this->info("🧪 Test des relations parent-enfant avec genre correct");
        $this->newLine();
        
        // Scénario 1: Père masculin ajoute fille féminine
        $this->testScenario1();
        
        // Scénario 2: Mère féminine ajoute fils masculin
        $this->testScenario2();
        
        // Scénario 3: Fils masculin ajoute père masculin
        $this->testScenario3();
        
        // Scénario 4: Fille féminine ajoute mère féminine
        $this->testScenario4();
        
        $this->newLine();
        $this->info("✅ Tous les tests terminés !");
    }
    
    private function testScenario1()
    {
        $this->info("📋 Scénario 1: Père masculin ajoute fille féminine");
        
        // Trouver un utilisateur masculin et un féminin
        $father = User::whereHas('profile', function($query) {
            $query->where('gender', 'male');
        })->first();
        
        $daughter = User::whereHas('profile', function($query) {
            $query->where('gender', 'female');
        })->where('id', '!=', $father->id)->first();
        
        if (!$father || !$daughter) {
            $this->warn("   ⚠️  Pas assez d'utilisateurs pour ce test");
            return;
        }
        
        $this->line("   • Père: {$father->name} (genre: {$father->profile->gender})");
        $this->line("   • Fille: {$daughter->name} (genre: {$daughter->profile->gender})");
        
        // Simuler la création d'une demande et son acceptation
        $daughterRelationType = RelationshipType::where('code', 'daughter')->first();
        
        try {
            // Créer la demande
            $request = $this->familyRelationService->createRelationshipRequest(
                $father,
                $daughter->id,
                $daughterRelationType->id,
                'Test: père ajoute fille'
            );
            
            $this->line("   • Demande créée: {$father->name} → {$daughter->name} (fille)");
            
            // Accepter la demande
            $this->familyRelationService->acceptRelationshipRequest($request);
            
            // Vérifier les relations créées
            $fatherRelation = \App\Models\FamilyRelationship::where('user_id', $father->id)
                ->where('related_user_id', $daughter->id)
                ->with('relationshipType')
                ->first();
                
            $daughterRelation = \App\Models\FamilyRelationship::where('user_id', $daughter->id)
                ->where('related_user_id', $father->id)
                ->with('relationshipType')
                ->first();
            
            if ($fatherRelation && $daughterRelation) {
                $this->line("   ✅ Relations créées:");
                $this->line("     - {$father->name} voit {$daughter->name} comme: {$fatherRelation->relationshipType->name_fr}");
                $this->line("     - {$daughter->name} voit {$father->name} comme: {$daughterRelation->relationshipType->name_fr}");
                
                // Vérifier que c'est correct
                if ($daughterRelation->relationshipType->code === 'father') {
                    $this->info("   ✅ CORRECT: La fille voit le père comme 'Père'");
                } else {
                    $this->error("   ❌ ERREUR: La fille voit le père comme '{$daughterRelation->relationshipType->name_fr}' au lieu de 'Père'");
                }
            }
            
            // Nettoyer
            $this->cleanup($father, $daughter);
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur: " . $e->getMessage());
        }
        
        $this->newLine();
    }
    
    private function testScenario2()
    {
        $this->info("📋 Scénario 2: Mère féminine ajoute fils masculin");
        
        $mother = User::whereHas('profile', function($query) {
            $query->where('gender', 'female');
        })->first();
        
        $son = User::whereHas('profile', function($query) {
            $query->where('gender', 'male');
        })->where('id', '!=', $mother->id)->first();
        
        if (!$mother || !$son) {
            $this->warn("   ⚠️  Pas assez d'utilisateurs pour ce test");
            return;
        }
        
        $this->line("   • Mère: {$mother->name} (genre: {$mother->profile->gender})");
        $this->line("   • Fils: {$son->name} (genre: {$son->profile->gender})");
        
        $sonRelationType = RelationshipType::where('code', 'son')->first();
        
        try {
            $request = $this->familyRelationService->createRelationshipRequest(
                $mother,
                $son->id,
                $sonRelationType->id,
                'Test: mère ajoute fils'
            );
            
            $this->familyRelationService->acceptRelationshipRequest($request);
            
            $sonRelation = \App\Models\FamilyRelationship::where('user_id', $son->id)
                ->where('related_user_id', $mother->id)
                ->with('relationshipType')
                ->first();
            
            if ($sonRelation) {
                $this->line("   • {$son->name} voit {$mother->name} comme: {$sonRelation->relationshipType->name_fr}");
                
                if ($sonRelation->relationshipType->code === 'mother') {
                    $this->info("   ✅ CORRECT: Le fils voit la mère comme 'Mère'");
                } else {
                    $this->error("   ❌ ERREUR: Le fils voit la mère comme '{$sonRelation->relationshipType->name_fr}' au lieu de 'Mère'");
                }
            }
            
            $this->cleanup($mother, $son);
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur: " . $e->getMessage());
        }
        
        $this->newLine();
    }
    
    private function testScenario3()
    {
        $this->info("📋 Scénario 3: Fils masculin ajoute père masculin");
        
        $son = User::whereHas('profile', function($query) {
            $query->where('gender', 'male');
        })->first();
        
        $father = User::whereHas('profile', function($query) {
            $query->where('gender', 'male');
        })->where('id', '!=', $son->id)->first();
        
        if (!$son || !$father) {
            $this->warn("   ⚠️  Pas assez d'utilisateurs pour ce test");
            return;
        }
        
        $this->line("   • Fils: {$son->name} (genre: {$son->profile->gender})");
        $this->line("   • Père: {$father->name} (genre: {$father->profile->gender})");
        
        $fatherRelationType = RelationshipType::where('code', 'father')->first();
        
        try {
            $request = $this->familyRelationService->createRelationshipRequest(
                $son,
                $father->id,
                $fatherRelationType->id,
                'Test: fils ajoute père'
            );
            
            $this->familyRelationService->acceptRelationshipRequest($request);
            
            $fatherRelation = \App\Models\FamilyRelationship::where('user_id', $father->id)
                ->where('related_user_id', $son->id)
                ->with('relationshipType')
                ->first();
            
            if ($fatherRelation) {
                $this->line("   • {$father->name} voit {$son->name} comme: {$fatherRelation->relationshipType->name_fr}");
                
                if ($fatherRelation->relationshipType->code === 'son') {
                    $this->info("   ✅ CORRECT: Le père voit le fils comme 'Fils'");
                } else {
                    $this->error("   ❌ ERREUR: Le père voit le fils comme '{$fatherRelation->relationshipType->name_fr}' au lieu de 'Fils'");
                }
            }
            
            $this->cleanup($son, $father);
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur: " . $e->getMessage());
        }
        
        $this->newLine();
    }
    
    private function testScenario4()
    {
        $this->info("📋 Scénario 4: Fille féminine ajoute mère féminine");
        
        $daughter = User::whereHas('profile', function($query) {
            $query->where('gender', 'female');
        })->first();
        
        $mother = User::whereHas('profile', function($query) {
            $query->where('gender', 'female');
        })->where('id', '!=', $daughter->id)->first();
        
        if (!$daughter || !$mother) {
            $this->warn("   ⚠️  Pas assez d'utilisateurs pour ce test");
            return;
        }
        
        $this->line("   • Fille: {$daughter->name} (genre: {$daughter->profile->gender})");
        $this->line("   • Mère: {$mother->name} (genre: {$mother->profile->gender})");
        
        $motherRelationType = RelationshipType::where('code', 'mother')->first();
        
        try {
            $request = $this->familyRelationService->createRelationshipRequest(
                $daughter,
                $mother->id,
                $motherRelationType->id,
                'Test: fille ajoute mère'
            );
            
            $this->familyRelationService->acceptRelationshipRequest($request);
            
            $motherRelation = \App\Models\FamilyRelationship::where('user_id', $mother->id)
                ->where('related_user_id', $daughter->id)
                ->with('relationshipType')
                ->first();
            
            if ($motherRelation) {
                $this->line("   • {$mother->name} voit {$daughter->name} comme: {$motherRelation->relationshipType->name_fr}");
                
                if ($motherRelation->relationshipType->code === 'daughter') {
                    $this->info("   ✅ CORRECT: La mère voit la fille comme 'Fille'");
                } else {
                    $this->error("   ❌ ERREUR: La mère voit la fille comme '{$motherRelation->relationshipType->name_fr}' au lieu de 'Fille'");
                }
            }
            
            $this->cleanup($daughter, $mother);
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur: " . $e->getMessage());
        }
        
        $this->newLine();
    }
    
    private function cleanup(User $user1, User $user2)
    {
        // Supprimer les relations de test
        \App\Models\FamilyRelationship::where(function($query) use ($user1, $user2) {
            $query->where('user_id', $user1->id)->where('related_user_id', $user2->id);
        })->orWhere(function($query) use ($user1, $user2) {
            $query->where('user_id', $user2->id)->where('related_user_id', $user1->id);
        })->delete();
        
        // Supprimer les demandes de test
        \App\Models\RelationshipRequest::where(function($query) use ($user1, $user2) {
            $query->where('requester_id', $user1->id)->where('target_user_id', $user2->id);
        })->orWhere(function($query) use ($user1, $user2) {
            $query->where('requester_id', $user2->id)->where('target_user_id', $user1->id);
        })->delete();
    }
}
