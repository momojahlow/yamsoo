<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Services\FamilyRelationService;
use Illuminate\Console\Command;

class TestInverseGenderLogic extends Command
{
    protected $signature = 'test:inverse-gender';
    protected $description = 'Test de la logique de genre inverse corrigée';

    public function __construct(
        private FamilyRelationService $familyRelationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🔍 Test de la logique de genre inverse corrigée');
        
        // Nettoyer les données de test
        $this->cleanupTestData();

        // Créer les utilisateurs
        $users = $this->createTestUsers();
        
        // Test 1: Mohammed (homme) ajoute Fatima comme mère
        $this->testMohammedAddsFatimaAsMother($users);
        
        // Test 2: Amina (femme) ajoute Mohammed comme frère
        $this->testAminaAddsMohammedAsBrother($users);
        
        // Test 3: Scénario complet Ahmed/Fatima/Mohammed/Amina
        $this->testCompleteScenario($users);
        
        $this->info("\n🎉 Test terminé !");
    }

    private function createTestUsers()
    {
        $this->info("\n📋 Création des utilisateurs de test");
        
        $usersData = [
            'ahmed@test.com' => ['Ahmed Benali', 'male'],
            'fatima@test.com' => ['Fatima Zahra', 'female'],
            'mohammed@test.com' => ['Mohammed Alami', 'male'],
            'amina@test.com' => ['Amina Benali', 'female'],
            'youssef@test.com' => ['Youssef Bennani', 'male'],
            'karim@test.com' => ['Karim El Fassi', 'male'],
            'leila@test.com' => ['Leila Mansouri', 'female'],
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

    private function testMohammedAddsFatimaAsMother($users)
    {
        $this->info("\n📋 Test 1: Mohammed (homme) ajoute Fatima comme mère");
        
        $mohammed = $users['mohammed@test.com'];
        $fatima = $users['fatima@test.com'];
        
        // Mohammed demande Fatima comme mère
        $motherType = RelationshipType::where('name', 'mother')->first();
        
        try {
            $request = $this->familyRelationService->createRelationshipRequest(
                $mohammed,
                $fatima->id,
                $motherType->id,
                'Mohammed demande Fatima comme mère'
            );
            
            $this->info("✅ Demande créée: Mohammed → Fatima (mère)");
            
            // Fatima accepte la demande
            $relationship = $this->familyRelationService->acceptRelationshipRequest($request);
            $this->info("✅ Demande acceptée par Fatima");
            
            // Vérifier les relations créées
            $this->info("\n🔍 Vérification des relations:");
            
            // Mohammed → Fatima (mère)
            $mohammedToFatima = FamilyRelationship::where('user_id', $mohammed->id)
                ->where('related_user_id', $fatima->id)
                ->with('relationshipType')
                ->first();
                
            if ($mohammedToFatima) {
                $this->info("✅ Mohammed → Fatima: {$mohammedToFatima->relationshipType->display_name_fr} ({$mohammedToFatima->relationshipType->name})");
            }
            
            // Fatima → Mohammed (relation inverse - doit être "fils" car Mohammed est un homme)
            $fatimaToMohammed = FamilyRelationship::where('user_id', $fatima->id)
                ->where('related_user_id', $mohammed->id)
                ->with('relationshipType')
                ->first();
                
            if ($fatimaToMohammed) {
                $this->info("✅ Fatima → Mohammed: {$fatimaToMohammed->relationshipType->display_name_fr} ({$fatimaToMohammed->relationshipType->name})");
                
                // Vérifier que c'est bien "fils" et pas "fille"
                if ($fatimaToMohammed->relationshipType->name === 'son') {
                    $this->info("✅ CORRECT: Mohammed est bien le fils de Fatima");
                } else {
                    $this->error("❌ ERREUR: Mohammed devrait être 'fils' mais est '{$fatimaToMohammed->relationshipType->name}'");
                }
            } else {
                $this->error("❌ Aucune relation Fatima → Mohammed");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
        }
    }

    private function testAminaAddsMohammedAsBrother($users)
    {
        $this->info("\n📋 Test 2: Amina (femme) ajoute Mohammed comme frère");
        
        $amina = $users['amina@test.com'];
        $mohammed = $users['mohammed@test.com'];
        
        // Nettoyer les relations existantes pour ce test
        FamilyRelationship::where('user_id', $amina->id)->where('related_user_id', $mohammed->id)->delete();
        FamilyRelationship::where('user_id', $mohammed->id)->where('related_user_id', $amina->id)->delete();
        RelationshipRequest::where('requester_id', $amina->id)->where('target_user_id', $mohammed->id)->delete();
        
        $brotherType = RelationshipType::where('name', 'brother')->first();
        
        try {
            $request = $this->familyRelationService->createRelationshipRequest(
                $amina,
                $mohammed->id,
                $brotherType->id,
                'Amina demande Mohammed comme frère'
            );
            
            $this->info("✅ Demande créée: Amina → Mohammed (frère)");
            
            // Mohammed accepte la demande
            $relationship = $this->familyRelationService->acceptRelationshipRequest($request);
            $this->info("✅ Demande acceptée par Mohammed");
            
            // Vérifier les relations créées
            $this->info("\n🔍 Vérification des relations:");
            
            // Amina → Mohammed (frère)
            $aminaToMohammed = FamilyRelationship::where('user_id', $amina->id)
                ->where('related_user_id', $mohammed->id)
                ->with('relationshipType')
                ->first();
                
            if ($aminaToMohammed) {
                $this->info("✅ Amina → Mohammed: {$aminaToMohammed->relationshipType->display_name_fr} ({$aminaToMohammed->relationshipType->name})");
            }
            
            // Mohammed → Amina (relation inverse - doit être "sœur" car Amina est une femme)
            $mohammedToAmina = FamilyRelationship::where('user_id', $mohammed->id)
                ->where('related_user_id', $amina->id)
                ->with('relationshipType')
                ->first();
                
            if ($mohammedToAmina) {
                $this->info("✅ Mohammed → Amina: {$mohammedToAmina->relationshipType->display_name_fr} ({$mohammedToAmina->relationshipType->name})");
                
                // Vérifier que c'est bien "sœur" et pas "frère"
                if ($mohammedToAmina->relationshipType->name === 'sister') {
                    $this->info("✅ CORRECT: Amina est bien la sœur de Mohammed");
                } else {
                    $this->error("❌ ERREUR: Amina devrait être 'sœur' mais est '{$mohammedToAmina->relationshipType->name}'");
                }
            } else {
                $this->error("❌ Aucune relation Mohammed → Amina");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
        }
    }

    private function testCompleteScenario($users)
    {
        $this->info("\n📋 Test 3: Scénario complet Ahmed/Fatima/Mohammed/Amina");
        
        // Nettoyer toutes les relations existantes
        $this->cleanupTestData();
        
        $ahmed = $users['ahmed@test.com'];
        $fatima = $users['fatima@test.com'];
        $mohammed = $users['mohammed@test.com'];
        $amina = $users['amina@test.com'];
        $youssef = $users['youssef@test.com'];
        $karim = $users['karim@test.com'];
        $leila = $users['leila@test.com'];
        
        try {
            $this->info("\n🔄 Ahmed crée les demandes:");
            
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
            
            $this->info("\n🔄 Amina crée les demandes:");
            
            // Amina → Youssef (mari)
            $husbandType = RelationshipType::where('name', 'husband')->first();
            $request4 = $this->familyRelationService->createRelationshipRequest($amina, $youssef->id, $husbandType->id, 'Amina demande Youssef comme mari');
            $this->familyRelationService->acceptRelationshipRequest($request4);
            $this->info("✅ Youssef accepte → devient mari d'Amina");
            
            // Amina → Karim (fils)
            $request5 = $this->familyRelationService->createRelationshipRequest($amina, $karim->id, $sonType->id, 'Amina demande Karim comme fils');
            $this->familyRelationService->acceptRelationshipRequest($request5);
            $this->info("✅ Karim accepte → devient fils d'Amina");
            
            // Amina → Leila (sœur)
            $sisterType = RelationshipType::where('name', 'sister')->first();
            $request6 = $this->familyRelationService->createRelationshipRequest($amina, $leila->id, $sisterType->id, 'Amina demande Leila comme sœur');
            $this->familyRelationService->acceptRelationshipRequest($request6);
            $this->info("✅ Leila accepte → devient sœur d'Amina");
            
            $this->info("\n🔍 Vérification finale des relations:");
            
            // Vérifier quelques relations clés
            $mohammedToAhmed = FamilyRelationship::where('user_id', $mohammed->id)->where('related_user_id', $ahmed->id)->with('relationshipType')->first();
            if ($mohammedToAhmed && $mohammedToAhmed->relationshipType->name === 'father') {
                $this->info("✅ Mohammed → Ahmed: Père");
            }
            
            $karimToAmina = FamilyRelationship::where('user_id', $karim->id)->where('related_user_id', $amina->id)->with('relationshipType')->first();
            if ($karimToAmina && $karimToAmina->relationshipType->name === 'mother') {
                $this->info("✅ Karim → Amina: Mère");
            }
            
            $leilaToAmina = FamilyRelationship::where('user_id', $leila->id)->where('related_user_id', $amina->id)->with('relationshipType')->first();
            if ($leilaToAmina && $leilaToAmina->relationshipType->name === 'sister') {
                $this->info("✅ Leila → Amina: Sœur");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
        }
    }

    private function cleanupTestData()
    {
        $testEmails = ['ahmed@test.com', 'fatima@test.com', 'mohammed@test.com', 'amina@test.com', 'youssef@test.com', 'karim@test.com', 'leila@test.com'];
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
