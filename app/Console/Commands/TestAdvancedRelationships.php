<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;

class TestAdvancedRelationships extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:advanced-relationships';

    /**
     * The description of the console command.
     */
    protected $description = 'Test avancé du système intelligent avec déductions automatiques';

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
        $this->info('🧪 Test avancé du système intelligent de relations familiales');
        $this->newLine();

        // Nettoyer d'abord
        $this->call('db:seed', ['--class' => 'CleanDatabaseSeeder']);
        $this->newLine();

        // Récupérer les utilisateurs
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();
        $aicha = User::where('email', 'aicha.idrissi@example.com')->first();

        $this->info("👥 Utilisateurs :");
        $this->line("   - Fatima Zahra (ID: {$fatima->id})");
        $this->line("   - Ahmed Benali (ID: {$ahmed->id})");
        $this->line("   - Mohammed Alami (ID: {$mohammed->id})");
        $this->line("   - Youssef Bennani (ID: {$youssef->id})");
        $this->line("   - Aicha Idrissi (ID: {$aicha->id})");
        $this->newLine();

        // Scénario : Créer une famille complexe
        $this->info('📝 Scénario : Construction d\'une famille complexe');
        $this->newLine();

        // 1. Ahmed est le père de Fatima
        $this->createRelation($ahmed, $fatima, 'father', 'Ahmed père de Fatima');
        
        // 2. Ahmed est le père de Mohammed  
        $this->createRelation($ahmed, $mohammed, 'father', 'Ahmed père de Mohammed');
        
        // 3. Youssef est le frère d'Ahmed
        $this->createRelation($youssef, $ahmed, 'brother', 'Youssef frère d\'Ahmed');

        $this->newLine();
        $this->info('🔍 Relations après construction de la famille :');
        $this->displayAllRelations([$fatima, $ahmed, $mohammed, $youssef, $aicha]);

        $this->newLine();
        $this->info('🔍 Test des suggestions (doivent exclure les personnes déjà en relation) :');
        
        foreach ([$fatima, $ahmed, $mohammed, $youssef, $aicha] as $user) {
            $suggestions = $this->suggestionService->getUserSuggestions($user);
            $this->line("   👤 {$user->name} : {$suggestions->count()} suggestion(s)");
            foreach ($suggestions as $suggestion) {
                $suggestedUser = $suggestion->suggestedUser;
                $relation = $suggestion->suggested_relation_name ?? 'Non définie';
                $this->line("     - {$suggestedUser->name} ({$relation})");
            }
        }

        $this->newLine();
        $this->info('✅ Test avancé terminé avec succès !');
        
        return 0;
    }

    private function createRelation(User $requester, User $target, string $relationCode, string $message): void
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
            $message
        );

        $relationship = $this->familyRelationService->acceptRelationshipRequest($request);
        
        $this->info("   ✅ {$message} (Relation ID: {$relationship->id})");
    }

    private function displayAllRelations(array $users): void
    {
        foreach ($users as $user) {
            $relations = $this->familyRelationService->getUserRelationships($user);
            $this->info("   👤 {$user->name} ({$relations->count()} relation(s)) :");
            
            foreach ($relations as $relation) {
                $relatedUser = $relation->relatedUser;
                $type = $relation->relationshipType;
                $auto = $relation->created_automatically ? ' 🤖' : ' 👤';
                $this->line("     - {$type->name_fr} : {$relatedUser->name}{$auto}");
            }
            
            if ($relations->count() === 0) {
                $this->line("     (Aucune relation)");
            }
        }
    }
}
