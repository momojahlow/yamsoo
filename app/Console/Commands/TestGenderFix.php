<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Suggestion;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Services\SuggestionService;
use App\Services\FamilyRelationService;
use Illuminate\Console\Command;

class TestGenderFix extends Command
{
    protected $signature = 'test:gender-fix';
    protected $description = 'Test spécifique pour le problème de genre de Fatima';

    public function __construct(
        private SuggestionService $suggestionService,
        private FamilyRelationService $familyRelationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🔍 Test spécifique du problème de genre de Fatima');
        
        // Nettoyer les données de test
        $this->cleanupTestData();

        // Créer les utilisateurs
        $users = $this->createTestUsers();
        
        // Test 1: Fatima demande Mohammed comme fils
        $this->testFatimaAsksMohammedAsSon($users);
        
        // Test 2: Fatima demande Amina comme fille
        $this->testFatimaAsksAminaAsDaughter($users);
        
        // Test 3: Vérifier les suggestions vs demandes
        $this->testSuggestionWorkflow($users);
        
        $this->info("\n🎉 Test terminé !");
    }

    private function createTestUsers()
    {
        $this->info("\n📋 Création des utilisateurs de test");
        
        $usersData = [
            'fatima.test@example.com' => ['Fatima Zahra', 'female'],
            'mohammed.test@example.com' => ['Mohammed Alami', 'male'],
            'amina.test@example.com' => ['Amina Benali', 'female'],
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

    private function testFatimaAsksMohammedAsSon($users)
    {
        $this->info("\n📋 Test 1: Fatima (femme) demande Mohammed comme fils");
        
        $fatima = $users['fatima.test@example.com'];
        $mohammed = $users['mohammed.test@example.com'];
        
        // Créer une demande de relation directe
        $sonType = RelationshipType::where('name', 'son')->first();
        
        try {
            $request = $this->familyRelationService->createRelationshipRequest(
                $fatima,
                $mohammed->id,
                $sonType->id,
                'Test Fatima → Mohammed comme fils'
            );
            
            $this->info("✅ Demande créée: Fatima → Mohammed (fils)");
            
            // Accepter la demande
            $relationship = $this->familyRelationService->acceptRelationshipRequest($request);
            $this->info("✅ Demande acceptée");
            
            // Vérifier les relations créées
            $this->info("\n🔍 Vérification des relations:");
            
            // Fatima → Mohammed
            $fatimaToMohammed = FamilyRelationship::where('user_id', $fatima->id)
                ->where('related_user_id', $mohammed->id)
                ->with('relationshipType')
                ->first();
                
            if ($fatimaToMohammed) {
                $this->info("✅ Fatima → Mohammed: {$fatimaToMohammed->relationshipType->display_name_fr} ({$fatimaToMohammed->relationshipType->name})");
            } else {
                $this->error("❌ Aucune relation Fatima → Mohammed");
            }
            
            // Mohammed → Fatima (relation inverse)
            $mohammedToFatima = FamilyRelationship::where('user_id', $mohammed->id)
                ->where('related_user_id', $fatima->id)
                ->with('relationshipType')
                ->first();
                
            if ($mohammedToFatima) {
                $this->info("✅ Mohammed → Fatima: {$mohammedToFatima->relationshipType->display_name_fr} ({$mohammedToFatima->relationshipType->name})");
                
                // Vérifier que c'est bien "mère" et pas "père"
                if ($mohammedToFatima->relationshipType->name === 'mother') {
                    $this->info("✅ CORRECT: Fatima est bien la mère de Mohammed");
                } else {
                    $this->error("❌ ERREUR: Fatima devrait être 'mère' mais est '{$mohammedToFatima->relationshipType->name}'");
                }
            } else {
                $this->error("❌ Aucune relation Mohammed → Fatima");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
        }
    }

    private function testFatimaAsksAminaAsDaughter($users)
    {
        $this->info("\n📋 Test 2: Fatima (femme) demande Amina comme fille");
        
        $fatima = $users['fatima.test@example.com'];
        $amina = $users['amina.test@example.com'];
        
        $daughterType = RelationshipType::where('name', 'daughter')->first();
        
        try {
            $request = $this->familyRelationService->createRelationshipRequest(
                $fatima,
                $amina->id,
                $daughterType->id,
                'Test Fatima → Amina comme fille'
            );
            
            $this->info("✅ Demande créée: Fatima → Amina (fille)");
            
            // Accepter la demande
            $relationship = $this->familyRelationService->acceptRelationshipRequest($request);
            $this->info("✅ Demande acceptée");
            
            // Vérifier la relation inverse
            $aminaToFatima = FamilyRelationship::where('user_id', $amina->id)
                ->where('related_user_id', $fatima->id)
                ->with('relationshipType')
                ->first();
                
            if ($aminaToFatima) {
                $this->info("✅ Amina → Fatima: {$aminaToFatima->relationshipType->display_name_fr} ({$aminaToFatima->relationshipType->name})");
                
                if ($aminaToFatima->relationshipType->name === 'mother') {
                    $this->info("✅ CORRECT: Fatima est bien la mère d'Amina");
                } else {
                    $this->error("❌ ERREUR: Fatima devrait être 'mère' mais est '{$aminaToFatima->relationshipType->name}'");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
        }
    }

    private function testSuggestionWorkflow($users)
    {
        $this->info("\n📋 Test 3: Workflow des suggestions (doit créer des demandes)");
        
        $fatima = $users['fatima.test@example.com'];
        $mohammed = $users['mohammed.test@example.com'];
        
        // Nettoyer les relations existantes pour ce test
        FamilyRelationship::where('user_id', $fatima->id)->where('related_user_id', $mohammed->id)->delete();
        FamilyRelationship::where('user_id', $mohammed->id)->where('related_user_id', $fatima->id)->delete();
        RelationshipRequest::where('requester_id', $fatima->id)->where('target_user_id', $mohammed->id)->delete();
        
        try {
            // Créer une suggestion
            $suggestion = $this->suggestionService->createSuggestion(
                $fatima,
                $mohammed->id,
                'family_relation',
                'Test suggestion fils',
                'son'
            );
            $this->info("✅ Suggestion créée");
            
            // Accepter la suggestion
            $this->suggestionService->acceptSuggestion($suggestion, 'son');
            $this->info("✅ Suggestion acceptée");
            
            // Vérifier qu'une DEMANDE a été créée (pas une relation directe)
            $pendingRequest = RelationshipRequest::where('requester_id', $fatima->id)
                ->where('target_user_id', $mohammed->id)
                ->where('status', 'pending')
                ->first();
                
            if ($pendingRequest) {
                $this->info("✅ CORRECT: Une demande de relation a été créée (status: pending)");
            } else {
                $this->error("❌ ERREUR: Aucune demande de relation créée");
            }
            
            // Vérifier qu'aucune relation directe n'a été créée
            $directRelation = FamilyRelationship::where('user_id', $fatima->id)
                ->where('related_user_id', $mohammed->id)
                ->first();
                
            if (!$directRelation) {
                $this->info("✅ CORRECT: Aucune relation directe créée");
            } else {
                $this->error("❌ ERREUR: Une relation directe a été créée au lieu d'une demande");
            }
            
            $suggestion->delete();
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
        }
    }

    private function cleanupTestData()
    {
        $testEmails = ['fatima.test@example.com', 'mohammed.test@example.com', 'amina.test@example.com'];
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
        
        Suggestion::where('message', 'like', '%Test%')->delete();
    }
}
