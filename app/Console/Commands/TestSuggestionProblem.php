<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;

class TestSuggestionProblem extends Command
{
    protected $signature = 'test:suggestion-problem';
    protected $description = 'Analyser le problème de suggestions incohérentes';

    public function handle()
    {
        $this->info('🔍 ANALYSE DU PROBLÈME DE SUGGESTIONS INCOHÉRENTES');
        $this->info('==================================================');
        $this->newLine();

        $service = app(FamilyRelationService::class);

        // Récupérer les utilisateurs
        $ahmed = User::where('name', 'Ahmed Benali')->first();
        $fatima = User::where('name', 'Fatima Zahra')->first();
        $amina = User::where('name', 'Amina Tazi')->first();

        $this->info('👥 Utilisateurs du scénario:');
        $this->line("   - Ahmed Benali (ID: {$ahmed->id})");
        $this->line("   - Fatima Zahra (ID: {$fatima->id})");
        $this->line("   - Amina Tazi (ID: {$amina->id})");
        $this->newLine();

        // Étape 1: Créer les relations selon le scénario
        $this->info('📝 Étape 1: Création des relations selon le scénario');
        
        // Ahmed est le père d'Amina
        $this->createAndAcceptRelation($service, $ahmed, $amina, 'father', 'Ahmed (père) → Amina (fille)');
        
        // Fatima est l'épouse d'Ahmed
        $this->createAndAcceptRelation($service, $fatima, $ahmed, 'wife', 'Fatima (épouse) → Ahmed (mari)');
        
        $this->newLine();

        // Étape 2: Analyser les relations existantes
        $this->info('📋 Étape 2: Relations existantes dans la base');
        
        $this->analyzeUserRelations($ahmed, 'Ahmed Benali');
        $this->analyzeUserRelations($fatima, 'Fatima Zahra');
        $this->analyzeUserRelations($amina, 'Amina Tazi');
        
        $this->newLine();

        // Étape 3: Analyser la logique de suggestions
        $this->info('🧠 Étape 3: Analyse de la logique de suggestions');
        
        $this->info('   Scénario actuel:');
        $this->line('   - Ahmed est père d\'Amina');
        $this->line('   - Fatima est épouse d\'Ahmed');
        $this->line('   - Donc: Fatima devrait être belle-mère d\'Amina (ou mère si adoptée)');
        $this->line('   - Et: Amina devrait être belle-fille de Fatima (ou fille si adoptée)');
        $this->newLine();

        // Étape 4: Vérifier les suggestions actuelles
        $this->info('💡 Étape 4: Vérification des suggestions actuelles');
        
        // Chercher les suggestions existantes
        $suggestions = \App\Models\Suggestion::with(['user', 'suggestedUser'])->get();
        
        if ($suggestions->isEmpty()) {
            $this->warn('   ⚠️ Aucune suggestion trouvée dans la base de données');
        } else {
            $this->info('   Suggestions existantes:');
            foreach ($suggestions as $suggestion) {
                $this->line("     - {$suggestion->user->name} → {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_name} ({$suggestion->reason})");
            }
        }
        
        $this->newLine();

        // Étape 5: Analyser la logique d'inférence
        $this->info('🔄 Étape 5: Test de la logique d\'inférence');
        
        $this->testInferenceLogic($fatima, $amina, 'Fatima → Amina');
        $this->testInferenceLogic($amina, $fatima, 'Amina → Fatima');
        
        $this->newLine();

        $this->info('✅ ANALYSE TERMINÉE');
        $this->info('Vérifiez les résultats ci-dessus pour identifier les incohérences.');

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

    private function analyzeUserRelations(User $user, string $userName)
    {
        $relations = FamilyRelationship::where('user_id', $user->id)
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        $this->info("   Relations de {$userName}:");
        if ($relations->isEmpty()) {
            $this->line('     - Aucune relation');
        } else {
            foreach ($relations as $relation) {
                $this->line("     - {$userName} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr}");
            }
        }
    }

    private function testInferenceLogic(User $user1, User $user2, string $description)
    {
        $this->info("   Test d'inférence: {$description}");
        
        // Trouver les connexions communes
        $user1Relations = FamilyRelationship::where('user_id', $user1->id)
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        $user2Relations = FamilyRelationship::where('user_id', $user2->id)
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        // Chercher des connexions communes
        $commonConnections = [];
        foreach ($user1Relations as $rel1) {
            foreach ($user2Relations as $rel2) {
                if ($rel1->related_user_id === $rel2->related_user_id) {
                    $commonConnections[] = [
                        'connector' => $rel1->relatedUser->name,
                        'user1_relation' => $rel1->relationshipType->display_name_fr,
                        'user2_relation' => $rel2->relationshipType->display_name_fr
                    ];
                }
            }
        }

        if (empty($commonConnections)) {
            $this->line("     - Aucune connexion commune trouvée");
        } else {
            $this->line("     - Connexions communes:");
            foreach ($commonConnections as $connection) {
                $this->line("       * Via {$connection['connector']}: {$user1->name} ({$connection['user1_relation']}) ↔ {$user2->name} ({$connection['user2_relation']})");
                
                // Analyser la relation suggérée
                $suggestedRelation = $this->inferRelationship($connection['user1_relation'], $connection['user2_relation']);
                $this->line("         → Relation suggérée: {$suggestedRelation}");
            }
        }
    }

    private function inferRelationship(string $relation1, string $relation2): string
    {
        // Logique simplifiée d'inférence
        if (($relation1 === 'Épouse' && $relation2 === 'Fille') || ($relation1 === 'Mari' && $relation2 === 'Fille')) {
            return 'Belle-mère / Mère';
        }
        
        if (($relation1 === 'Fille' && $relation2 === 'Épouse') || ($relation1 === 'Fille' && $relation2 === 'Mari')) {
            return 'Belle-fille / Fille';
        }
        
        if ($relation1 === 'Épouse' && $relation2 === 'Épouse') {
            return 'Co-épouse (polygamie)';
        }
        
        if ($relation1 === 'Fille' && $relation2 === 'Fille') {
            return 'Sœur';
        }
        
        return 'Relation complexe à déterminer';
    }
}
