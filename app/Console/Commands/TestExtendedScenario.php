<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;

class TestExtendedScenario extends Command
{
    protected $signature = 'test:extended-scenario';
    protected $description = 'Test le scénario étendu avec toutes les relations par alliance';

    private $familyRelationService;
    private $suggestionService;

    public function __construct()
    {
        parent::__construct();
        $this->familyRelationService = app(FamilyRelationService::class);
        $this->suggestionService = app(SuggestionService::class);
    }

    public function handle()
    {
        $this->info('🎯 TEST DU SCÉNARIO ÉTENDU');
        $this->info('============================');
        $this->info('');

        // Réinitialiser la base de données
        $this->info('🔄 Réinitialisation de la base de données...');
        $this->call('migrate:fresh', ['--seed' => true]);
        $this->info('');

        // Récupérer les utilisateurs
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
        $amina = User::where('email', 'amina.tazi@example.com')->first();
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();
        $karim = User::where('email', 'karim.elfassi@example.com')->first();
        $leila = User::where('email', 'leila.mansouri@example.com')->first();

        $this->info('👥 Scénario étendu:');
        $this->info("   Ahmed Benali (ID: {$ahmed->id}) - Patriarche");
        $this->info("   Fatima Zahra (ID: {$fatima->id}) - Épouse d'Ahmed");
        $this->info("   Mohammed Alami (ID: {$mohammed->id}) - Fils d'Ahmed");
        $this->info("   Amina Tazi (ID: {$amina->id}) - Fille d'Ahmed");
        $this->info("   Youssef Bennani (ID: {$youssef->id}) - Mari d'Amina");
        $this->info("   Karim El Fassi (ID: {$karim->id}) - Fils d'Amina");
        $this->info("   Leila Mansouri (ID: {$leila->id}) - Sœur d'Amina");
        $this->info('');

        // ÉTAPE 1: Ahmed crée les relations de base
        $this->info('📝 ÉTAPE 1: Ahmed crée les relations de base');
        $this->createRelationship($ahmed, $fatima, 'wife', 'Ahmed → Fatima (épouse)');
        $this->createRelationship($ahmed, $mohammed, 'son', 'Ahmed → Mohammed (fils)');
        $this->createRelationship($ahmed, $amina, 'daughter', 'Ahmed → Amina (fille)');

        // ÉTAPE 2: Acceptation des relations de base
        $this->info('📝 ÉTAPE 2: Acceptation des relations de base');
        $this->acceptAndCheckSuggestions($fatima, 'Fatima Zahra');
        $this->acceptAndCheckSuggestions($mohammed, 'Mohammed Alami');
        $this->acceptAndCheckSuggestions($amina, 'Amina Tazi');

        // ÉTAPE 3: Amina crée ses relations
        $this->info('📝 ÉTAPE 3: Amina crée ses relations');
        $this->createRelationship($amina, $youssef, 'husband', 'Amina → Youssef (mari)');
        $this->createRelationship($amina, $karim, 'son', 'Amina → Karim (fils)');
        $this->createRelationship($amina, $leila, 'sister', 'Amina → Leila (sœur)');

        // ÉTAPE 4: Acceptation des nouvelles relations
        $this->info('📝 ÉTAPE 4: Acceptation des nouvelles relations');
        $this->acceptAndCheckSuggestions($youssef, 'Youssef Bennani');
        $this->acceptAndCheckSuggestions($karim, 'Karim El Fassi');
        $this->acceptAndCheckSuggestions($leila, 'Leila Mansouri');

        // ÉTAPE 5: Vérification finale des suggestions
        $this->info('📝 ÉTAPE 5: Vérification finale des suggestions pour tous');
        $this->info('Régénération des suggestions après toutes les acceptations...');
        $this->checkFinalSuggestions($ahmed, 'Ahmed Benali');
        $this->checkFinalSuggestions($fatima, 'Fatima Zahra');
        $this->checkFinalSuggestions($mohammed, 'Mohammed Alami');
        $this->checkFinalSuggestions($amina, 'Amina Tazi');
        $this->checkFinalSuggestions($youssef, 'Youssef Bennani');
        $this->checkFinalSuggestions($karim, 'Karim El Fassi');
        $this->checkFinalSuggestions($leila, 'Leila Mansouri');

        $this->info('✅ TEST TERMINÉ');
    }

    private function createRelationship(User $requester, User $target, string $relationType, string $description)
    {
        try {
            $relationshipType = RelationshipType::where('name', $relationType)->first();
            if (!$relationshipType) {
                $this->error("   ❌ Type de relation '{$relationType}' non trouvé");
                return;
            }

            $this->familyRelationService->createRelationshipRequest(
                $requester,
                $target->id,
                $relationshipType->id,
                "Demande automatique pour test"
            );
            $this->info("   ✅ {$description} - Demande créée");
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur lors de la création: " . $e->getMessage());
        }
    }

    private function acceptAndCheckSuggestions(User $user, string $userName)
    {
        // Accepter toutes les demandes en attente pour cet utilisateur
        $pendingRequests = RelationshipRequest::where('target_user_id', $user->id)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingRequests as $request) {
            try {
                $this->familyRelationService->acceptRelationshipRequest($request);
                $requester = User::find($request->requester_user_id);
                $relationshipType = RelationshipType::find($request->relationship_type_id);
                $this->info("   ✅ {$userName} accepte: {$requester->name} → {$relationshipType->display_name_fr}");
            } catch (\Exception $e) {
                $this->error("   ❌ Erreur lors de l'acceptation: " . $e->getMessage());
            }
        }

        // Générer et vérifier les suggestions
        $this->checkSuggestions($user, $userName);
    }

    private function checkSuggestions(User $user, string $userName)
    {
        $this->info("💡 Suggestions pour {$userName}:");
        
        try {
            $suggestions = $this->suggestionService->generateSuggestions($user);
            
            if ($suggestions->isEmpty()) {
                $this->info("   ✅ 0 suggestions générées");
                $this->info("   ⚪ Aucune suggestion");
            } else {
                $this->info("   ✅ {$suggestions->count()} suggestions générées");
                foreach ($suggestions as $suggestion) {
                    $suggestedUser = User::find($suggestion->suggested_user_id);
                    $this->info("   - {$suggestedUser->name} : {$suggestion->suggested_relation_name}");
                    $this->info("     Raison: {$suggestion->reason}");
                    
                    // Vérifier si la suggestion est correcte selon les attentes
                    $this->validateSuggestion($user, $suggestedUser, $suggestion);
                }
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur lors de la génération des suggestions: " . $e->getMessage());
        }
        
        $this->info('');
    }

    private function checkFinalSuggestions(User $user, string $userName)
    {
        $this->checkSuggestions($user, $userName);
    }

    private function validateSuggestion(User $user, User $suggestedUser, $suggestion)
    {
        // Définir les attentes pour chaque utilisateur
        $expectedSuggestions = $this->getExpectedSuggestions();
        
        $userKey = $user->email;
        $suggestedKey = $suggestedUser->email;
        
        if (isset($expectedSuggestions[$userKey][$suggestedKey])) {
            $expected = $expectedSuggestions[$userKey][$suggestedKey];
            $actual = $suggestion->suggested_relation_code ?? $suggestion->type;
            
            if ($actual === $expected) {
                $this->info("     ✅ CORRECT: {$user->name} → {$suggestedUser->name} comme {$suggestion->suggested_relation_name}");
            } else {
                $this->error("     ❌ INCORRECT: {$user->name} → {$suggestedUser->name} comme {$suggestion->suggested_relation_name} (devrait être {$expected})");
            }
        }
    }

    private function getExpectedSuggestions(): array
    {
        return [
            // Youssef Bennani (mari d'Amina) devrait voir:
            'youssef.bennani@example.com' => [
                'ahmed.benali@example.com' => 'father_in_law',    // Ahmed = beau-père
                'fatima.zahra@example.com' => 'mother_in_law',    // Fatima = belle-mère
                'mohammed.alami@example.com' => 'brother_in_law', // Mohammed = beau-frère
            ],
            
            // Leila Mansouri (sœur d'Amina) devrait voir:
            'leila.mansouri@example.com' => [
                'ahmed.benali@example.com' => 'father',           // Ahmed = père
                'fatima.zahra@example.com' => 'mother',           // Fatima = mère
                'mohammed.alami@example.com' => 'brother',        // Mohammed = frère
                'youssef.bennani@example.com' => 'brother_in_law', // Youssef = beau-frère
            ],
            
            // Karim El Fassi (fils d'Amina) devrait voir:
            'karim.elfassi@example.com' => [
                'ahmed.benali@example.com' => 'grandfather',      // Ahmed = grand-père
                'fatima.zahra@example.com' => 'grandmother',      // Fatima = grand-mère
                'mohammed.alami@example.com' => 'uncle',          // Mohammed = oncle
                'youssef.bennani@example.com' => 'father',        // Youssef = père (beau-père)
                'leila.mansouri@example.com' => 'aunt',           // Leila = tante
            ],
        ];
    }
}
