<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;
use App\Services\SuggestionService;

class AnalyzeDatabaseState extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'analyze:database-state';

    /**
     * The description of the console command.
     */
    protected $description = 'Analyser l\'état de la base de données et le système de suggestions';

    protected SuggestionService $suggestionService;

    public function __construct(SuggestionService $suggestionService)
    {
        parent::__construct();
        $this->suggestionService = $suggestionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 ANALYSE DE L\'ÉTAT DE LA BASE DE DONNÉES');
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        // 1. Analyser les utilisateurs
        $this->analyzeUsers();
        $this->newLine();

        // 2. Analyser les relations
        $this->analyzeRelationships();
        $this->newLine();

        // 3. Analyser les suggestions dans la base
        $this->analyzeSuggestionsInDatabase();
        $this->newLine();

        // 4. Tester le service de suggestions
        $this->testSuggestionService();
        $this->newLine();

        // 5. Identifier les problèmes
        $this->identifyIssues();

        return 0;
    }

    private function analyzeUsers(): void
    {
        $users = User::with('profile')->get();
        $this->info("👥 UTILISATEURS ({$users->count()}) :");
        
        foreach ($users->take(5) as $user) {
            $gender = $user->profile?->gender === 'female' ? '👩' : '👨';
            $this->line("   {$gender} {$user->name} (ID: {$user->id}) - {$user->email}");
        }
        
        if ($users->count() > 5) {
            $this->line("   ... et " . ($users->count() - 5) . " autres utilisateurs");
        }
    }

    private function analyzeRelationships(): void
    {
        $relationships = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
        $this->info("🔗 RELATIONS FAMILIALES ({$relationships->count()}) :");
        
        if ($relationships->count() === 0) {
            $this->line("   (Aucune relation)");
            return;
        }

        foreach ($relationships as $relation) {
            $auto = $relation->created_automatically ? ' 🤖' : ' 👤';
            $this->line("   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr}{$auto}");
        }
    }

    private function analyzeSuggestionsInDatabase(): void
    {
        $suggestions = Suggestion::with(['user', 'suggestedUser'])->get();
        $this->info("💡 SUGGESTIONS EN BASE ({$suggestions->count()}) :");
        
        if ($suggestions->count() === 0) {
            $this->line("   (Aucune suggestion)");
            return;
        }

        foreach ($suggestions as $suggestion) {
            $this->line("   - Pour {$suggestion->user->name} : {$suggestion->suggestedUser->name} ({$suggestion->suggested_relation_code}) - Status: {$suggestion->status}");
        }
    }

    private function testSuggestionService(): void
    {
        $this->info("🧪 TEST DU SERVICE DE SUGGESTIONS :");
        
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        
        if (!$fatima || !$ahmed) {
            $this->error("   ❌ Utilisateurs de test non trouvés");
            return;
        }

        // Test pour Fatima
        $this->line("   📋 Test pour Fatima Zahra :");
        $fatimasSuggestions = $this->suggestionService->getUserSuggestions($fatima);
        $this->line("      - Suggestions retournées par le service : {$fatimasSuggestions->count()}");
        
        foreach ($fatimasSuggestions as $suggestion) {
            $this->line("        • {$suggestion->suggestedUser->name} ({$suggestion->suggested_relation_name})");
        }

        // Test pour Ahmed
        $this->line("   📋 Test pour Ahmed Benali :");
        $ahmedsSuggestions = $this->suggestionService->getUserSuggestions($ahmed);
        $this->line("      - Suggestions retournées par le service : {$ahmedsSuggestions->count()}");
        
        foreach ($ahmedsSuggestions as $suggestion) {
            $this->line("        • {$suggestion->suggestedUser->name} ({$suggestion->suggested_relation_name})");
        }
    }

    private function identifyIssues(): void
    {
        $this->info("🚨 IDENTIFICATION DES PROBLÈMES :");
        
        // Vérifier les relations existantes
        $relationships = FamilyRelationship::count();
        $suggestions = Suggestion::count();
        
        $this->line("   📊 Statistiques :");
        $this->line("      - Relations en base : {$relationships}");
        $this->line("      - Suggestions en base : {$suggestions}");
        
        // Tester le filtrage
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        if ($fatima) {
            $fatimasRelations = FamilyRelationship::where(function($query) use ($fatima) {
                $query->where('user_id', $fatima->id)
                      ->orWhere('related_user_id', $fatima->id);
            })->count();
            
            $fatimasSuggestions = $this->suggestionService->getUserSuggestions($fatima);
            
            $this->line("   🔍 Analyse pour Fatima :");
            $this->line("      - Relations existantes : {$fatimasRelations}");
            $this->line("      - Suggestions filtrées : {$fatimasSuggestions->count()}");
            
            if ($fatimasRelations > 0 && $fatimasSuggestions->count() > 0) {
                $this->line("   ⚠️  PROBLÈME POTENTIEL : Des suggestions existent malgré les relations");
                
                // Vérifier si les suggestions incluent des personnes déjà en relation
                foreach ($fatimasSuggestions as $suggestion) {
                    $hasRelation = FamilyRelationship::where(function($query) use ($fatima, $suggestion) {
                        $query->where('user_id', $fatima->id)->where('related_user_id', $suggestion->suggested_user_id);
                    })->orWhere(function($query) use ($fatima, $suggestion) {
                        $query->where('user_id', $suggestion->suggested_user_id)->where('related_user_id', $fatima->id);
                    })->exists();
                    
                    if ($hasRelation) {
                        $this->error("      ❌ ERREUR : {$suggestion->suggestedUser->name} est suggéré mais a déjà une relation avec Fatima");
                    }
                }
            }
        }
    }
}
