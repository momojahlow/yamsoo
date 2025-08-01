<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Services\FamilyRelationService;
use Illuminate\Console\Command;

class TestCompleteUserScenario extends Command
{
    protected $signature = 'test:complete-scenario';
    protected $description = 'Test du scénario complet demandé par l\'utilisateur';

    public function __construct(
        private FamilyRelationService $familyRelationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🎯 Test du scénario complet demandé par l\'utilisateur');
        
        // Nettoyer les données de test
        $this->cleanupTestData();

        // Créer les utilisateurs
        $users = $this->createTestUsers();
        
        // Scénario Ahmed
        $this->testAhmedScenario($users);
        
        // Scénario Amina
        $this->testAminaScenario($users);
        
        // Test final Leila
        $this->testLeilaScenario($users);
        
        // Vérification finale
        $this->verifyFinalRelationships($users);
        
        $this->info("\n🎉 Scénario complet terminé !");
    }

    private function createTestUsers()
    {
        $this->info("\n📋 Création des utilisateurs");
        
        $usersData = [
            'ahmed@scenario.com' => ['Ahmed Benali', 'male'],
            'fatima@scenario.com' => ['Fatima Zahra', 'female'],
            'mohammed@scenario.com' => ['Mohammed Alami', 'male'],
            'amina@scenario.com' => ['Amina Benali', 'female'],
            'youssef@scenario.com' => ['Youssef Bennani', 'male'],
            'karim@scenario.com' => ['Karim El Fassi', 'male'],
            'leila@scenario.com' => ['Leila Mansouri', 'female'],
        ];

        $users = [];
        foreach ($usersData as $email => [$name, $gender]) {
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'password' => bcrypt('password123')
            ]);
            
            if (!$user->profile) {
                $user->profile()->create([
                    'first_name' => explode(' ', $name)[0],
                    'last_name' => substr($name, strlen(explode(' ', $name)[0]) + 1),
                    'gender' => $gender,
                    'birth_date' => now()->subYears(rand(25, 60)),
                ]);
            } else {
                $user->profile->update(['gender' => $gender]);
            }
            
            $users[$email] = $user;
            $this->info("✅ {$name} ({$gender})");
        }

        return $users;
    }

    private function testAhmedScenario($users)
    {
        $this->info("\n📋 Scénario Ahmed : crée les demandes Fatima (épouse), Mohammed (fils), Amina (fille)");
        
        $ahmed = $users['ahmed@scenario.com'];
        $fatima = $users['fatima@scenario.com'];
        $mohammed = $users['mohammed@scenario.com'];
        $amina = $users['amina@scenario.com'];
        
        try {
            // Ahmed → Fatima (épouse)
            $wifeType = RelationshipType::where('name', 'wife')->first();
            $request1 = $this->familyRelationService->createRelationshipRequest($ahmed, $fatima->id, $wifeType->id, 'Ahmed demande Fatima comme épouse');
            $this->familyRelationService->acceptRelationshipRequest($request1);
            $this->info("✅ Fatima accepte → devient épouse d'Ahmed");
            
            // Ahmed → Mohammed (fils)
            $sonType = RelationshipType::where('name', 'son')->first();
            $request2 = $this->familyRelationService->createRelationshipRequest($ahmed, $mohammed->id, $sonType->id, 'Ahmed demande Mohammed comme fils');
            $this->familyRelationService->acceptRelationshipRequest($request2);
            $this->info("✅ Mohammed accepte → devient fils d'Ahmed");
            
            // Ahmed → Amina (fille)
            $daughterType = RelationshipType::where('name', 'daughter')->first();
            $request3 = $this->familyRelationService->createRelationshipRequest($ahmed, $amina->id, $daughterType->id, 'Ahmed demande Amina comme fille');
            $this->familyRelationService->acceptRelationshipRequest($request3);
            $this->info("✅ Amina accepte → devient fille d'Ahmed");
            
            // Vérifications
            $this->info("\n🔍 Vérifications après scénario Ahmed:");
            
            // Mohammed doit voir Fatima comme mère
            $mohammedToFatima = FamilyRelationship::where('user_id', $mohammed->id)
                ->where('related_user_id', $fatima->id)
                ->with('relationshipType')
                ->first();
            if ($mohammedToFatima && $mohammedToFatima->relationshipType->name === 'mother') {
                $this->info("✅ Mohammed voit Fatima comme mère");
            } else {
                $this->error("❌ Mohammed ne voit pas Fatima comme mère");
            }
            
            // Amina doit voir Fatima comme mère
            $aminaToFatima = FamilyRelationship::where('user_id', $amina->id)
                ->where('related_user_id', $fatima->id)
                ->with('relationshipType')
                ->first();
            if ($aminaToFatima && $aminaToFatima->relationshipType->name === 'mother') {
                $this->info("✅ Amina voit Fatima comme mère");
            } else {
                $this->error("❌ Amina ne voit pas Fatima comme mère");
            }
            
            // Amina doit voir Mohammed comme frère
            $aminaToMohammed = FamilyRelationship::where('user_id', $amina->id)
                ->where('related_user_id', $mohammed->id)
                ->with('relationshipType')
                ->first();
            if ($aminaToMohammed && $aminaToMohammed->relationshipType->name === 'brother') {
                $this->info("✅ Amina voit Mohammed comme frère");
            } else {
                $this->error("❌ Amina ne voit pas Mohammed comme frère");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur dans scénario Ahmed: " . $e->getMessage());
        }
    }

    private function testAminaScenario($users)
    {
        $this->info("\n📋 Scénario Amina : crée les demandes Youssef (mari), Karim (fils)");
        
        $amina = $users['amina@scenario.com'];
        $youssef = $users['youssef@scenario.com'];
        $karim = $users['karim@scenario.com'];
        
        try {
            // Amina → Youssef (mari)
            $husbandType = RelationshipType::where('name', 'husband')->first();
            $request1 = $this->familyRelationService->createRelationshipRequest($amina, $youssef->id, $husbandType->id, 'Amina demande Youssef comme mari');
            $this->familyRelationService->acceptRelationshipRequest($request1);
            $this->info("✅ Youssef accepte → devient mari d'Amina");
            
            // Amina → Karim (fils)
            $sonType = RelationshipType::where('name', 'son')->first();
            $request2 = $this->familyRelationService->createRelationshipRequest($amina, $karim->id, $sonType->id, 'Amina demande Karim comme fils');
            $this->familyRelationService->acceptRelationshipRequest($request2);
            $this->info("✅ Karim accepte → devient fils d'Amina");
            
            // Vérifications
            $this->info("\n🔍 Vérifications après scénario Amina:");
            
            // Karim doit voir Amina comme mère
            $karimToAmina = FamilyRelationship::where('user_id', $karim->id)
                ->where('related_user_id', $amina->id)
                ->with('relationshipType')
                ->first();
            if ($karimToAmina && $karimToAmina->relationshipType->name === 'mother') {
                $this->info("✅ Karim voit Amina comme mère");
            } else {
                $this->error("❌ Karim ne voit pas Amina comme mère");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur dans scénario Amina: " . $e->getMessage());
        }
    }

    private function testLeilaScenario($users)
    {
        $this->info("\n📋 Scénario Leila : Amina crée demande Leila (sœur)");
        
        $amina = $users['amina@scenario.com'];
        $leila = $users['leila@scenario.com'];
        
        try {
            // Amina → Leila (sœur)
            $sisterType = RelationshipType::where('name', 'sister')->first();
            $request = $this->familyRelationService->createRelationshipRequest($amina, $leila->id, $sisterType->id, 'Amina demande Leila comme sœur');
            $this->familyRelationService->acceptRelationshipRequest($request);
            $this->info("✅ Leila accepte → devient sœur d'Amina");
            
            // Vérifications
            $this->info("\n🔍 Vérifications après scénario Leila:");
            
            // Leila doit voir Amina comme sœur
            $leilaToAmina = FamilyRelationship::where('user_id', $leila->id)
                ->where('related_user_id', $amina->id)
                ->with('relationshipType')
                ->first();
            if ($leilaToAmina && $leilaToAmina->relationshipType->name === 'sister') {
                $this->info("✅ Leila voit Amina comme sœur");
            } else {
                $this->error("❌ Leila ne voit pas Amina comme sœur");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur dans scénario Leila: " . $e->getMessage());
        }
    }

    private function verifyFinalRelationships($users)
    {
        $this->info("\n📋 Vérification finale de toutes les relations");
        
        foreach ($users as $email => $user) {
            $this->info("\n🌳 Relations de {$user->name}:");
            $relations = $this->familyRelationService->getUserRelationships($user);
            
            if ($relations->isEmpty()) {
                $this->info("  (Aucune relation)");
            } else {
                foreach ($relations as $relation) {
                    $relatedUser = $relation->relatedUser;
                    $relationType = $relation->relationshipType;
                    $this->info("  - {$relatedUser->name}: {$relationType->display_name_fr} ({$relationType->name})");
                }
            }
        }
        
        // Tests spécifiques de genre
        $this->info("\n🔍 Tests spécifiques de genre:");
        
        $mohammed = $users['mohammed@scenario.com'];
        $fatima = $users['fatima@scenario.com'];
        $amina = $users['amina@scenario.com'];
        
        // Fatima doit voir Mohammed comme fils (pas fille)
        $fatimaToMohammed = FamilyRelationship::where('user_id', $fatima->id)
            ->where('related_user_id', $mohammed->id)
            ->with('relationshipType')
            ->first();
        if ($fatimaToMohammed && $fatimaToMohammed->relationshipType->name === 'son') {
            $this->info("✅ Fatima voit Mohammed comme fils (correct)");
        } else {
            $this->error("❌ Fatima ne voit pas Mohammed comme fils");
        }
        
        // Mohammed doit voir Amina comme sœur (pas frère)
        $mohammedToAmina = FamilyRelationship::where('user_id', $mohammed->id)
            ->where('related_user_id', $amina->id)
            ->with('relationshipType')
            ->first();
        if ($mohammedToAmina && $mohammedToAmina->relationshipType->name === 'sister') {
            $this->info("✅ Mohammed voit Amina comme sœur (correct)");
        } else {
            $this->error("❌ Mohammed ne voit pas Amina comme sœur");
        }
    }

    private function cleanupTestData()
    {
        $testEmails = ['ahmed@scenario.com', 'fatima@scenario.com', 'mohammed@scenario.com', 'amina@scenario.com', 'youssef@scenario.com', 'karim@scenario.com', 'leila@scenario.com'];
        $testUsers = User::whereIn('email', $testEmails)->get();
        
        foreach ($testUsers as $user1) {
            foreach ($testUsers as $user2) {
                if ($user1->id !== $user2->id) {
                    FamilyRelationship::where('user_id', $user1->id)
                        ->where('related_user_id', $user2->id)
                        ->delete();
                    
                    RelationshipRequest::where('requester_id', $user1->id)
                        ->where('target_user_id', $user2->id)
                        ->delete();
                }
            }
        }
    }
}
