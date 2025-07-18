<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class TestFamilySuggestions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:family-suggestions {--user-id= : ID de l\'utilisateur à tester}';

    /**
     * The console command description.
     */
    protected $description = 'Teste le système de suggestions familiales intelligentes';

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
        $userId = $this->option('user-id');
        
        if ($userId) {
            $this->testSpecificUser($userId);
        } else {
            $this->testScenarioMohammedAlami();
        }
    }
    
    private function testSpecificUser(int $userId)
    {
        $user = User::with('profile')->find($userId);
        
        if (!$user) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");
            return;
        }
        
        $this->info("🧪 Test des suggestions familiales pour : {$user->name}");
        $this->newLine();
        
        $this->showUserFamilyContext($user);
        $this->generateAndShowSuggestions($user);
    }
    
    private function testScenarioMohammedAlami()
    {
        $this->info("🧪 Test du scénario : Mohammed Alami accepte Youssef Bennani comme père");
        $this->newLine();
        
        // Trouver Mohammed Alami et Youssef Bennani
        $mohammed = User::whereHas('profile', function($query) {
            $query->where('first_name', 'like', '%Mohammed%')
                  ->where('last_name', 'like', '%Alami%');
        })->first();
        
        $youssef = User::whereHas('profile', function($query) {
            $query->where('first_name', 'like', '%Youssef%')
                  ->where('last_name', 'like', '%Bennani%');
        })->first();
        
        if (!$mohammed || !$youssef) {
            $this->warn("⚠️  Utilisateurs Mohammed Alami ou Youssef Bennani non trouvés");
            $this->info("Utilisateurs disponibles :");
            User::with('profile')->get()->each(function($user) {
                $this->line("  - {$user->name} (ID: {$user->id})");
            });
            return;
        }
        
        $this->info("👨‍👦 Scénario : {$mohammed->name} a {$youssef->name} comme père");
        $this->newLine();
        
        // Afficher le contexte familial de Youssef (le père)
        $this->info("👨 Contexte familial de {$youssef->name} (le père) :");
        $this->showUserFamilyContext($youssef);
        
        // Afficher le contexte familial de Mohammed (le fils)
        $this->info("👦 Contexte familial de {$mohammed->name} (le fils) :");
        $this->showUserFamilyContext($mohammed);
        
        // Générer des suggestions pour Mohammed basées sur la famille de Youssef
        $this->info("💡 Suggestions pour {$mohammed->name} basées sur la famille de son père :");
        $this->generateAndShowSuggestions($mohammed);
    }
    
    private function showUserFamilyContext(User $user)
    {
        // Relations où l'utilisateur est l'initiateur
        $userRelations = FamilyRelationship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['relatedUser.profile', 'relationshipType'])
            ->get();
            
        // Relations où l'utilisateur est la cible
        $targetRelations = FamilyRelationship::where('related_user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['user.profile', 'relationshipType'])
            ->get();
        
        $totalRelations = $userRelations->count() + $targetRelations->count();
        
        if ($totalRelations === 0) {
            $this->line("   • Aucune relation familiale existante");
            $this->newLine();
            return;
        }
        
        $this->line("   • {$totalRelations} relation(s) familiale(s) :");
        
        foreach ($userRelations as $relation) {
            $relatedUser = $relation->relatedUser;
            $relationType = $relation->relationshipType->name_fr;
            $this->line("     - {$relatedUser->name} ({$relationType})");
        }
        
        foreach ($targetRelations as $relation) {
            $relatedUser = $relation->user;
            // Pour les relations inverses, nous devons calculer le type de relation correct
            $inverseType = $this->getInverseRelationName($relation->relationshipType);
            $this->line("     - {$relatedUser->name} ({$inverseType})");
        }
        
        $this->newLine();
    }
    
    private function generateAndShowSuggestions(User $user)
    {
        $suggestions = $this->suggestionService->generateSuggestions($user);
        
        if ($suggestions->isEmpty()) {
            $this->line("   • Aucune suggestion générée");
            $this->newLine();
            return;
        }
        
        $this->line("   • {$suggestions->count()} suggestion(s) générée(s) :");
        
        foreach ($suggestions->take(4) as $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $type = $suggestion->type;
            $reason = $suggestion->reason ?? 'Connexion familiale';
            
            $this->line("     - {$suggestedUser->name}");
            $this->line("       Type: {$type}");
            $this->line("       Raison: {$reason}");
            $this->line("");
        }
        
        $this->newLine();
    }
    
    private function getInverseRelationName($relationType): string
    {
        $inverseMap = [
            'father' => 'Fils/Fille',
            'mother' => 'Fils/Fille', 
            'son' => 'Père',
            'daughter' => 'Mère',
            'brother' => 'Frère/Sœur',
            'sister' => 'Frère/Sœur',
            'husband' => 'Épouse',
            'wife' => 'Mari',
        ];
        
        return $inverseMap[$relationType->code] ?? $relationType->name_fr;
    }
}
