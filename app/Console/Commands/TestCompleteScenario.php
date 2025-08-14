<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\Suggestion;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;

class TestCompleteScenario extends Command
{
    protected $signature = 'test:complete-scenario';
    protected $description = 'Tester le scénario complet décrit par l\'utilisateur';

    public function handle()
    {
        $this->info('🎯 TEST DU SCÉNARIO COMPLET');
        $this->info('============================');
        $this->newLine();

        // Fresh seed pour commencer proprement
        $this->info('🔄 Réinitialisation de la base de données...');
        $this->call('migrate:fresh', ['--seed' => true]);
        $this->newLine();

        $familyService = app(FamilyRelationService::class);
        $suggestionService = app(SuggestionService::class);

        // Récupérer les utilisateurs
        $ahmed = User::where('name', 'Ahmed Benali')->first();
        $fatima = User::where('name', 'Fatima Zahra')->first();
        $mohammed = User::where('name', 'Mohammed Alami')->first();
        $amina = User::where('name', 'Amina Tazi')->first();

        $this->info('👥 Scénario:');
        $this->line("   Ahmed Benali (ID: {$ahmed->id}) - Créateur des demandes");
        $this->line("   Fatima Zahra (ID: {$fatima->id}) - Épouse");
        $this->line("   Mohammed Alami (ID: {$mohammed->id}) - Fils");
        $this->line("   Amina Tazi (ID: {$amina->id}) - Fille");
        $this->newLine();

        // ÉTAPE 1: Ahmed crée les demandes de relation
        $this->info('📝 ÉTAPE 1: Ahmed crée les demandes de relation');
        
        $this->createRelationshipRequest($familyService, $ahmed, $fatima, 'wife', 'Ahmed demande Fatima comme épouse');
        $this->createRelationshipRequest($familyService, $ahmed, $mohammed, 'son', 'Ahmed demande Mohammed comme fils');
        $this->createRelationshipRequest($familyService, $ahmed, $amina, 'daughter', 'Ahmed demande Amina comme fille');
        
        $this->newLine();

        // ÉTAPE 2: Fatima accepte la demande et on vérifie ses suggestions
        $this->info('📝 ÉTAPE 2: Fatima se connecte et accepte la demande');
        $this->acceptPendingRequestsForUser($familyService, $fatima);
        $this->generateAndAnalyzeSuggestions($suggestionService, $fatima, 'Fatima Zahra');
        $this->newLine();

        // ÉTAPE 3: Mohammed accepte la demande et on vérifie ses suggestions
        $this->info('📝 ÉTAPE 3: Mohammed se connecte et accepte la demande');
        $this->acceptPendingRequestsForUser($familyService, $mohammed);
        $this->generateAndAnalyzeSuggestions($suggestionService, $mohammed, 'Mohammed Alami');
        $this->newLine();

        // ÉTAPE 4: Amina accepte la demande et on vérifie ses suggestions
        $this->info('📝 ÉTAPE 4: Amina se connecte et accepte la demande');
        $this->acceptPendingRequestsForUser($familyService, $amina);
        $this->generateAndAnalyzeSuggestions($suggestionService, $amina, 'Amina Tazi');
        $this->newLine();

        // ÉTAPE 5: Vérification finale des suggestions pour tous
        $this->info('📝 ÉTAPE 5: Vérification finale des suggestions pour tous');
        $this->info('Régénération des suggestions après toutes les acceptations...');
        
        // Nettoyer toutes les suggestions
        Suggestion::truncate();
        
        $this->generateAndAnalyzeSuggestions($suggestionService, $ahmed, 'Ahmed Benali');
        $this->generateAndAnalyzeSuggestions($suggestionService, $fatima, 'Fatima Zahra');
        $this->generateAndAnalyzeSuggestions($suggestionService, $mohammed, 'Mohammed Alami');
        $this->generateAndAnalyzeSuggestions($suggestionService, $amina, 'Amina Tazi');

        $this->newLine();
        $this->info('✅ TEST TERMINÉ');

        return 0;
    }

    private function createRelationshipRequest(FamilyRelationService $service, User $requester, User $target, string $relationTypeName, string $description)
    {
        try {
            $relationType = RelationshipType::where('name', $relationTypeName)->first();
            if (!$relationType) {
                $this->error("   ❌ Type de relation '{$relationTypeName}' non trouvé");
                return;
            }

            $request = $service->createRelationshipRequest(
                $requester,
                $target->id,
                $relationType->id,
                $description
            );

            $this->info("   ✅ {$description} - Demande créée");

        } catch (\Exception $e) {
            $this->error("   ❌ {$description} - Erreur: {$e->getMessage()}");
        }
    }

    private function acceptPendingRequestsForUser(FamilyRelationService $service, User $user)
    {
        $pendingRequests = RelationshipRequest::where('target_user_id', $user->id)
            ->where('status', 'pending')
            ->with(['requester', 'relationshipType'])
            ->get();

        if ($pendingRequests->isEmpty()) {
            $this->line("   ⚪ Aucune demande en attente pour {$user->name}");
            return;
        }

        foreach ($pendingRequests as $request) {
            try {
                $relation = $service->acceptRelationshipRequest($request);
                $this->info("   ✅ {$user->name} accepte: {$request->requester->name} → {$request->relationshipType->display_name_fr}");
            } catch (\Exception $e) {
                $this->error("   ❌ Erreur acceptation: {$e->getMessage()}");
            }
        }
    }

    private function generateAndAnalyzeSuggestions(SuggestionService $service, User $user, string $userName)
    {
        $this->info("💡 Suggestions pour {$userName}:");
        
        try {
            // Nettoyer les anciennes suggestions de cet utilisateur
            Suggestion::where('user_id', $user->id)->delete();
            
            $suggestions = $service->generateSuggestions($user);
            $this->line("   ✅ {$suggestions->count()} suggestions générées");
            
            // Récupérer les suggestions de la base de données
            $dbSuggestions = Suggestion::where('user_id', $user->id)
                ->with(['suggestedUser'])
                ->get();

            if ($dbSuggestions->isEmpty()) {
                $this->line("   ⚪ Aucune suggestion");
                return;
            }

            foreach ($dbSuggestions as $suggestion) {
                $suggestedUserName = $suggestion->suggestedUser->name;
                $relationName = $suggestion->suggested_relation_name ?? 'Non défini';
                $reason = $suggestion->reason ?? 'Aucune raison';
                
                $this->line("   - {$suggestedUserName} : {$relationName}");
                $this->line("     Raison: {$reason}");
                
                // Analyser si la suggestion est correcte
                $this->validateSuggestionForScenario($user, $suggestion->suggestedUser, $relationName, $userName);
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur: {$e->getMessage()}");
        }
    }

    private function validateSuggestionForScenario(User $user, User $suggestedUser, string $relationName, string $userName)
    {
        // Validation basée sur le scénario attendu
        if ($userName === 'Fatima Zahra') {
            if ($suggestedUser->name === 'Mohammed Alami') {
                if (in_array($relationName, ['Fils', 'son'])) {
                    $this->info("     ✅ CORRECT: Fatima → Mohammed comme fils");
                } else {
                    $this->error("     ❌ INCORRECT: Fatima → Mohammed comme {$relationName} (devrait être fils)");
                }
            } elseif ($suggestedUser->name === 'Amina Tazi') {
                if (in_array($relationName, ['Fille', 'Belle-fille', 'daughter'])) {
                    $this->info("     ✅ CORRECT: Fatima → Amina comme fille/belle-fille");
                } else {
                    $this->error("     ❌ INCORRECT: Fatima → Amina comme {$relationName} (devrait être fille/belle-fille)");
                }
            }
        } elseif ($userName === 'Mohammed Alami') {
            if ($suggestedUser->name === 'Fatima Zahra') {
                if (in_array($relationName, ['Mère', 'mother'])) {
                    $this->info("     ✅ CORRECT: Mohammed → Fatima comme mère");
                } else {
                    $this->error("     ❌ INCORRECT: Mohammed → Fatima comme {$relationName} (devrait être mère)");
                }
            } elseif ($suggestedUser->name === 'Amina Tazi') {
                if (in_array($relationName, ['Sœur', 'sister'])) {
                    $this->info("     ✅ CORRECT: Mohammed → Amina comme sœur");
                } else {
                    $this->error("     ❌ INCORRECT: Mohammed → Amina comme {$relationName} (devrait être sœur)");
                }
            }
        } elseif ($userName === 'Amina Tazi') {
            if ($suggestedUser->name === 'Fatima Zahra') {
                if (in_array($relationName, ['Mère', 'Belle-mère', 'mother'])) {
                    $this->info("     ✅ CORRECT: Amina → Fatima comme mère/belle-mère");
                } else {
                    $this->error("     ❌ INCORRECT: Amina → Fatima comme {$relationName} (devrait être mère/belle-mère)");
                }
            } elseif ($suggestedUser->name === 'Mohammed Alami') {
                if (in_array($relationName, ['Frère', 'brother'])) {
                    $this->info("     ✅ CORRECT: Amina → Mohammed comme frère");
                } else {
                    $this->error("     ❌ INCORRECT: Amina → Mohammed comme {$relationName} (devrait être frère)");
                }
            }
        }
    }
}
