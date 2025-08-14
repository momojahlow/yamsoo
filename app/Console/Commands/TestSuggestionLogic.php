<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;

class TestSuggestionLogic extends Command
{
    protected $signature = 'test:suggestion-logic';
    protected $description = 'Tester la logique de suggestions avec le scénario spécifique';

    public function handle()
    {
        $this->info('🧠 TEST DE LA LOGIQUE DE SUGGESTIONS');
        $this->info('====================================');
        $this->newLine();

        $familyService = app(FamilyRelationService::class);
        $suggestionService = app(SuggestionService::class);

        // Récupérer les utilisateurs
        $ahmed = User::where('name', 'Ahmed Benali')->first();
        $fatima = User::where('name', 'Fatima Zahra')->first();
        $amina = User::where('name', 'Amina Tazi')->first();

        $this->info('👥 Scénario de test:');
        $this->line("   - Ahmed Benali (ID: {$ahmed->id})");
        $this->line("   - Fatima Zahra (ID: {$fatima->id})");
        $this->line("   - Amina Tazi (ID: {$amina->id})");
        $this->newLine();

        // Créer les relations selon le scénario
        $this->info('📝 Création des relations:');
        
        // Ahmed est le père d'Amina
        $this->createAndAcceptRelation($familyService, $ahmed, $amina, 'father', 'Ahmed (père) → Amina (fille)');
        
        // Fatima est l'épouse d'Ahmed
        $this->createAndAcceptRelation($familyService, $fatima, $ahmed, 'wife', 'Fatima (épouse) → Ahmed (mari)');
        
        $this->newLine();

        // Nettoyer les anciennes suggestions
        $this->info('🧹 Nettoyage des anciennes suggestions...');
        Suggestion::whereIn('user_id', [$ahmed->id, $fatima->id, $amina->id])->delete();
        $this->newLine();

        // Générer les suggestions pour chaque utilisateur
        $this->info('💡 Génération des suggestions:');
        
        $this->testUserSuggestions($suggestionService, $ahmed, 'Ahmed Benali');
        $this->testUserSuggestions($suggestionService, $fatima, 'Fatima Zahra');
        $this->testUserSuggestions($suggestionService, $amina, 'Amina Tazi');
        
        $this->newLine();

        // Analyser les suggestions générées
        $this->info('🔍 ANALYSE DES SUGGESTIONS GÉNÉRÉES:');
        $this->newLine();

        $this->analyzeSuggestions($ahmed, 'Ahmed Benali');
        $this->analyzeSuggestions($fatima, 'Fatima Zahra');
        $this->analyzeSuggestions($amina, 'Amina Tazi');

        $this->newLine();
        $this->info('✅ TEST TERMINÉ');

        return 0;
    }

    private function createAndAcceptRelation(FamilyRelationService $service, User $requester, User $target, string $relationTypeName, string $description)
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
                "Test: {$description}"
            );

            $relation = $service->acceptRelationshipRequest($request);
            $this->info("   ✅ {$description} - Succès");

        } catch (\Exception $e) {
            $this->error("   ❌ {$description} - Erreur: {$e->getMessage()}");
        }
    }

    private function testUserSuggestions(SuggestionService $service, User $user, string $userName)
    {
        $this->info("   Génération pour {$userName}:");
        
        try {
            $suggestions = $service->generateSuggestions($user);
            $this->line("     ✅ {$suggestions->count()} suggestions générées");
        } catch (\Exception $e) {
            $this->error("     ❌ Erreur: {$e->getMessage()}");
        }
    }

    private function analyzeSuggestions(User $user, string $userName)
    {
        $suggestions = Suggestion::where('user_id', $user->id)
            ->with(['suggestedUser'])
            ->get();

        $this->info("📋 Suggestions pour {$userName}:");
        
        if ($suggestions->isEmpty()) {
            $this->warn("   ⚠️ Aucune suggestion trouvée");
            return;
        }

        foreach ($suggestions as $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $relationName = $suggestion->suggested_relation_name ?? 'Non défini';
            $reason = $suggestion->reason ?? 'Aucune raison';
            
            $this->line("   - {$suggestedUser->name} : {$relationName}");
            $this->line("     Raison: {$reason}");
            
            // Analyser si la suggestion est correcte
            $this->validateSuggestion($user, $suggestedUser, $relationName, $userName);
        }
        
        $this->newLine();
    }

    private function validateSuggestion(User $user, User $suggestedUser, string $relationName, string $userName)
    {
        // Logique de validation basée sur notre scénario
        if ($userName === 'Ahmed Benali') {
            // Ahmed devrait voir Fatima comme épouse (déjà en relation)
            // Ahmed ne devrait pas avoir de suggestion pour Amina (déjà en relation)
            $this->line("     ✅ Suggestion analysée pour Ahmed");
            
        } elseif ($userName === 'Fatima Zahra') {
            // Fatima devrait voir Amina comme fille (belle-fille)
            if ($suggestedUser->name === 'Amina Tazi') {
                if (in_array($relationName, ['Fille', 'Belle-fille', 'daughter'])) {
                    $this->line("     ✅ CORRECT: Fatima → Amina comme fille/belle-fille");
                } else {
                    $this->error("     ❌ INCORRECT: Fatima → Amina comme {$relationName} (devrait être fille/belle-fille)");
                }
            }
            
        } elseif ($userName === 'Amina Tazi') {
            // Amina devrait voir Fatima comme mère (belle-mère)
            if ($suggestedUser->name === 'Fatima Zahra') {
                if (in_array($relationName, ['Mère', 'Belle-mère', 'mother'])) {
                    $this->line("     ✅ CORRECT: Amina → Fatima comme mère/belle-mère");
                } else {
                    $this->error("     ❌ INCORRECT: Amina → Fatima comme {$relationName} (devrait être mère/belle-mère)");
                }
            }
        }
    }
}
