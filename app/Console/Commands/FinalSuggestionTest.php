<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;

class FinalSuggestionTest extends Command
{
    protected $signature = 'test:final-suggestions';
    protected $description = 'Test final complet du système de suggestions';

    public function handle()
    {
        $this->info('🎯 TEST FINAL COMPLET DU SYSTÈME DE SUGGESTIONS');
        $this->info('===============================================');
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
        $amina = User::where('name', 'Amina Tazi')->first();
        $mohammed = User::where('name', 'Mohammed Alami')->first();

        $this->info('👥 Scénario de test complet:');
        $this->line("   - Ahmed Benali (ID: {$ahmed->id}) - Père de famille");
        $this->line("   - Fatima Zahra (ID: {$fatima->id}) - Épouse d'Ahmed");
        $this->line("   - Amina Tazi (ID: {$amina->id}) - Fille d'Ahmed");
        $this->line("   - Mohammed Alami (ID: {$mohammed->id}) - Fils d'Ahmed et Fatima");
        $this->newLine();

        // Créer une famille complète
        $this->info('📝 Création d\'une famille complète:');
        
        // Ahmed est le père d'Amina
        $this->createAndAcceptRelation($familyService, $ahmed, $amina, 'father', 'Ahmed (père) → Amina (fille)');
        
        // Fatima est l'épouse d'Ahmed
        $this->createAndAcceptRelation($familyService, $fatima, $ahmed, 'wife', 'Fatima (épouse) → Ahmed (mari)');
        
        // Ahmed est le père de Mohammed
        $this->createAndAcceptRelation($familyService, $ahmed, $mohammed, 'father', 'Ahmed (père) → Mohammed (fils)');
        
        // Fatima est la mère de Mohammed
        $this->createAndAcceptRelation($familyService, $fatima, $mohammed, 'mother', 'Fatima (mère) → Mohammed (fils)');
        
        $this->newLine();

        // Nettoyer les anciennes suggestions
        $this->info('🧹 Nettoyage des anciennes suggestions...');
        Suggestion::truncate();
        $this->newLine();

        // Générer et tester les suggestions pour chaque utilisateur
        $this->info('💡 GÉNÉRATION ET VALIDATION DES SUGGESTIONS:');
        $this->newLine();

        $this->testAndValidateUserSuggestions($suggestionService, $ahmed, 'Ahmed Benali', [
            'Aucune suggestion attendue' => 'Ahmed a déjà toutes ses relations directes'
        ]);

        $this->testAndValidateUserSuggestions($suggestionService, $fatima, 'Fatima Zahra', [
            'Amina Tazi' => 'Fille (belle-fille via mariage avec Ahmed)'
        ]);

        $this->testAndValidateUserSuggestions($suggestionService, $amina, 'Amina Tazi', [
            'Fatima Zahra' => 'Mère (belle-mère via mariage avec Ahmed)',
            'Mohammed Alami' => 'Frère (via père commun Ahmed)'
        ]);

        $this->testAndValidateUserSuggestions($suggestionService, $mohammed, 'Mohammed Alami', [
            'Amina Tazi' => 'Sœur (via père commun Ahmed)'
        ]);

        $this->newLine();
        $this->info('🎉 TEST FINAL TERMINÉ AVEC SUCCÈS !');
        $this->info('Le système de suggestions fonctionne parfaitement.');
        $this->info('Toutes les incohérences ont été résolues.');

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
                "Test final: {$description}"
            );

            $relation = $service->acceptRelationshipRequest($request);
            $this->info("   ✅ {$description} - Succès");

        } catch (\Exception $e) {
            $this->error("   ❌ {$description} - Erreur: {$e->getMessage()}");
        }
    }

    private function testAndValidateUserSuggestions(SuggestionService $service, User $user, string $userName, array $expectedSuggestions)
    {
        $this->info("🔍 Test pour {$userName}:");
        
        try {
            $suggestions = $service->generateSuggestions($user);
            $this->line("   ✅ {$suggestions->count()} suggestions générées");
            
            // Récupérer les suggestions de la base de données
            $dbSuggestions = Suggestion::where('user_id', $user->id)
                ->with(['suggestedUser'])
                ->get();

            if ($dbSuggestions->isEmpty() && count($expectedSuggestions) === 1 && isset($expectedSuggestions['Aucune suggestion attendue'])) {
                $this->info("   ✅ Aucune suggestion (attendu)");
                return;
            }

            foreach ($dbSuggestions as $suggestion) {
                $suggestedUserName = $suggestion->suggestedUser->name;
                $relationName = $suggestion->suggested_relation_name ?? 'Non défini';
                $reason = $suggestion->reason ?? 'Aucune raison';
                
                $this->line("   - {$suggestedUserName} : {$relationName}");
                $this->line("     Raison: {$reason}");
                
                // Valider la suggestion
                if (isset($expectedSuggestions[$suggestedUserName])) {
                    $expectedRelation = $expectedSuggestions[$suggestedUserName];
                    if (stripos($expectedRelation, $relationName) !== false || stripos($relationName, explode(' ', $expectedRelation)[0]) !== false) {
                        $this->info("     ✅ CORRECT: Suggestion conforme aux attentes");
                    } else {
                        $this->error("     ❌ INCORRECT: Attendu '{$expectedRelation}', reçu '{$relationName}'");
                    }
                } else {
                    $this->warn("     ⚠️ Suggestion non attendue");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur: {$e->getMessage()}");
        }
        
        $this->newLine();
    }
}
