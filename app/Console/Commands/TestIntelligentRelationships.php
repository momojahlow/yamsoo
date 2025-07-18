<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;

class TestIntelligentRelationships extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:intelligent-relationships';

    /**
     * The description of the console command.
     */
    protected $description = 'Test le système intelligent de gestion des relations familiales';

    protected FamilyRelationService $familyRelationService;
    protected SuggestionService $suggestionService;

    public function __construct(
        FamilyRelationService $familyRelationService,
        SuggestionService $suggestionService
    ) {
        parent::__construct();
        $this->familyRelationService = $familyRelationService;
        $this->suggestionService = $suggestionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Test du système intelligent de relations familiales');
        $this->newLine();

        // Récupérer les utilisateurs
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();

        if (!$fatima || !$ahmed || !$mohammed || !$youssef) {
            $this->error('❌ Utilisateurs non trouvés. Exécutez d\'abord le seeder.');
            return 1;
        }

        $this->info("👥 Utilisateurs trouvés :");
        $this->line("   - Fatima Zahra (ID: {$fatima->id})");
        $this->line("   - Ahmed Benali (ID: {$ahmed->id})");
        $this->line("   - Mohammed Alami (ID: {$mohammed->id})");
        $this->line("   - Youssef Bennani (ID: {$youssef->id})");
        $this->newLine();

        // Test 1: Créer une relation père-fille entre Ahmed et Fatima
        $this->info('📝 Test 1: Ahmed devient le père de Fatima');

        $fatherType = RelationshipType::where('code', 'father')->first();
        if (!$fatherType) {
            $this->error('❌ Type de relation "father" non trouvé');
            return 1;
        }

        // Créer une demande de relation
        $request = $this->familyRelationService->createRelationshipRequest(
            $ahmed,
            $fatima->id,
            $fatherType->id,
            'Test automatique - Ahmed père de Fatima'
        );

        $this->info("   ✅ Demande créée (ID: {$request->id})");

        // Accepter la demande
        $relationship = $this->familyRelationService->acceptRelationshipRequest($request);
        $this->info("   ✅ Relation acceptée (ID: {$relationship->id})");
        $this->newLine();

        // Test 2: Créer une relation frère-sœur entre Mohammed et Fatima
        $this->info('📝 Test 2: Mohammed devient le frère de Fatima');

        $brotherType = RelationshipType::where('code', 'brother')->first();
        if (!$brotherType) {
            $this->error('❌ Type de relation "brother" non trouvé');
            return 1;
        }

        $request2 = $this->familyRelationService->createRelationshipRequest(
            $mohammed,
            $fatima->id,
            $brotherType->id,
            'Test automatique - Mohammed frère de Fatima'
        );

        $this->info("   ✅ Demande créée (ID: {$request2->id})");

        $relationship2 = $this->familyRelationService->acceptRelationshipRequest($request2);
        $this->info("   ✅ Relation acceptée (ID: {$relationship2->id})");
        $this->newLine();

        // Vérifier les relations automatiques créées
        $this->info('🔍 Vérification des relations automatiques créées :');

        $ahmedRelations = $this->familyRelationService->getUserRelationships($ahmed);
        $this->info("   👨 Ahmed a {$ahmedRelations->count()} relation(s) :");
        foreach ($ahmedRelations as $relation) {
            $relatedUser = $relation->relatedUser;
            $type = $relation->relationshipType;
            $auto = $relation->created_automatically ? ' (automatique)' : '';
            $this->line("     - {$type->name_fr} : {$relatedUser->name}{$auto}");
        }

        $fatimaRelations = $this->familyRelationService->getUserRelationships($fatima);
        $this->info("   👩 Fatima a {$fatimaRelations->count()} relation(s) :");
        foreach ($fatimaRelations as $relation) {
            $relatedUser = $relation->relatedUser;
            $type = $relation->relationshipType;
            $auto = $relation->created_automatically ? ' (automatique)' : '';
            $this->line("     - {$type->name_fr} : {$relatedUser->name}{$auto}");
        }

        $mohammedRelations = $this->familyRelationService->getUserRelationships($mohammed);
        $this->info("   👨 Mohammed a {$mohammedRelations->count()} relation(s) :");
        foreach ($mohammedRelations as $relation) {
            $relatedUser = $relation->relatedUser;
            $type = $relation->relationshipType;
            $auto = $relation->created_automatically ? ' (automatique)' : '';
            $this->line("     - {$type->name_fr} : {$relatedUser->name}{$auto}");
        }

        $this->newLine();

        // Test 3: Vérifier les suggestions (doivent exclure les personnes déjà en relation)
        $this->info('🔍 Test 3: Vérification des suggestions pour Fatima');
        $suggestions = $this->suggestionService->getUserSuggestions($fatima);
        $this->info("   📋 Fatima a {$suggestions->count()} suggestion(s) :");
        foreach ($suggestions as $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $this->line("     - {$suggestedUser->name} ({$suggestion->suggested_relation_name})");
        }

        $this->newLine();
        $this->info('✅ Tests terminés avec succès !');

        return 0;
    }
}
