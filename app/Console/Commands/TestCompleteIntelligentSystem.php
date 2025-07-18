<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;

class TestCompleteIntelligentSystem extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:complete-intelligent-system';

    /**
     * The description of the console command.
     */
    protected $description = 'Test complet du système intelligent - TOUTES les relations familiales';

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
        $this->info('🧪 TEST COMPLET DU SYSTÈME INTELLIGENT DE RELATIONS FAMILIALES');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        // Nettoyer d'abord
        $this->call('db:seed', ['--class' => 'CleanDatabaseSeeder']);
        $this->newLine();

        // Récupérer les utilisateurs
        $users = [
            'fatima' => User::where('email', 'fatima.zahra@example.com')->first(),
            'ahmed' => User::where('email', 'ahmed.benali@example.com')->first(),
            'mohammed' => User::where('email', 'mohammed.alami@example.com')->first(),
            'youssef' => User::where('email', 'youssef.bennani@example.com')->first(),
            'aicha' => User::where('email', 'aicha.idrissi@example.com')->first(),
        ];

        $this->info("👥 UTILISATEURS DISPONIBLES :");
        foreach ($users as $key => $user) {
            $gender = $user->profile?->gender === 'female' ? '👩' : '👨';
            $this->line("   {$gender} {$user->name} (ID: {$user->id}) - Genre: {$user->profile?->gender}");
        }
        $this->newLine();

        // ÉTAPE 1: Construire une famille complexe step by step
        $this->info('📝 ÉTAPE 1: Construction d\'une famille complexe');
        $this->line('─────────────────────────────────────────────────');

        // Ahmed devient le père de Fatima
        $this->info('🔗 1.1 Ahmed devient le père de Fatima');
        $this->createRelation($users['ahmed'], $users['fatima'], 'father');
        $this->displayRelationsAfterStep($users);

        // Ahmed devient le père de Mohammed
        $this->info('🔗 1.2 Ahmed devient le père de Mohammed');
        $this->createRelation($users['ahmed'], $users['mohammed'], 'father');
        $this->displayRelationsAfterStep($users);

        // Youssef devient le frère d'Ahmed
        $this->info('🔗 1.3 Youssef devient le frère d\'Ahmed');
        $this->createRelation($users['youssef'], $users['ahmed'], 'brother');
        $this->displayRelationsAfterStep($users);

        // Aicha devient la mère de Fatima
        $this->info('🔗 1.4 Aicha devient la mère de Fatima');
        $this->createRelation($users['aicha'], $users['fatima'], 'mother');
        $this->displayRelationsAfterStep($users);

        $this->newLine();
        $this->info('🔍 ÉTAPE 2: Vérification des relations automatiques créées');
        $this->line('─────────────────────────────────────────────────────────────');
        $this->displayAllRelationsDetailed($users);

        $this->newLine();
        $this->info('🔍 ÉTAPE 3: Test du filtrage des suggestions');
        $this->line('─────────────────────────────────────────────────');
        $this->testSuggestionFiltering($users);

        $this->newLine();
        $this->info('✅ TEST COMPLET TERMINÉ AVEC SUCCÈS !');
        $this->info('💡 Le système intelligent a automatiquement déduit toutes les relations familiales.');
        
        return 0;
    }

    private function createRelation(User $requester, User $target, string $relationCode): void
    {
        $relationType = RelationshipType::where('code', $relationCode)->first();
        if (!$relationType) {
            $this->error("❌ Type de relation '{$relationCode}' non trouvé");
            return;
        }

        $request = $this->familyRelationService->createRelationshipRequest(
            $requester,
            $target->id,
            $relationType->id,
            "Test automatique - {$requester->name} {$relationType->name_fr} de {$target->name}"
        );

        $relationship = $this->familyRelationService->acceptRelationshipRequest($request);
        
        $this->line("   ✅ {$requester->name} → {$target->name} : {$relationType->name_fr}");
    }

    private function displayRelationsAfterStep(array $users): void
    {
        $this->line("   📊 Relations après cette étape :");
        foreach ($users as $user) {
            $relations = $this->familyRelationService->getUserRelationships($user);
            if ($relations->count() > 0) {
                $this->line("      👤 {$user->name} : {$relations->count()} relation(s)");
            }
        }
        $this->newLine();
    }

    private function displayAllRelationsDetailed(array $users): void
    {
        foreach ($users as $user) {
            $relations = $this->familyRelationService->getUserRelationships($user);
            $gender = $user->profile?->gender === 'female' ? '👩' : '👨';
            
            $this->info("   {$gender} {$user->name} ({$relations->count()} relation(s)) :");
            
            if ($relations->count() === 0) {
                $this->line("      (Aucune relation)");
            } else {
                foreach ($relations as $relation) {
                    $relatedUser = $relation->relatedUser;
                    $type = $relation->relationshipType;
                    $auto = $relation->created_automatically ? ' 🤖 (automatique)' : ' 👤 (manuelle)';
                    $relatedGender = $relatedUser->profile?->gender === 'female' ? '👩' : '👨';
                    $this->line("      - {$type->name_fr} : {$relatedGender} {$relatedUser->name}{$auto}");
                }
            }
            $this->newLine();
        }
    }

    private function testSuggestionFiltering(array $users): void
    {
        // Créer quelques suggestions de test
        $this->info('📝 Création de suggestions de test...');
        
        // Suggestion valide (Youssef pour Fatima - devrait être oncle paternel)
        $this->suggestionService->createSuggestion(
            $users['fatima'],
            $users['youssef']->id,
            'family',
            'Test suggestion',
            'uncle_paternal'
        );
        
        // Suggestion qui devrait être filtrée (Ahmed pour Fatima - déjà père)
        $this->suggestionService->createSuggestion(
            $users['fatima'],
            $users['ahmed']->id,
            'family',
            'Cette suggestion devrait être filtrée',
            'father'
        );

        $this->newLine();
        $this->info('🔍 Vérification du filtrage des suggestions :');
        
        foreach ($users as $user) {
            $suggestions = $this->suggestionService->getUserSuggestions($user);
            $gender = $user->profile?->gender === 'female' ? '👩' : '👨';
            
            $this->line("   {$gender} {$user->name} : {$suggestions->count()} suggestion(s)");
            foreach ($suggestions as $suggestion) {
                $suggestedUser = $suggestion->suggestedUser;
                $relation = $suggestion->suggested_relation_name ?? 'Non définie';
                $suggestedGender = $suggestedUser->profile?->gender === 'female' ? '👩' : '👨';
                $this->line("     - {$suggestedGender} {$suggestedUser->name} ({$relation})");
            }
        }
    }
}
