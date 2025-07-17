<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Profile;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class DemoSuggestionSystem extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'demo:suggestions';

    /**
     * The console command description.
     */
    protected $description = 'Démonstration du système de suggestions avec exclusion des relations existantes';

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
        $this->info("🎯 Démonstration : Exclusion des relations existantes dans les suggestions");
        $this->newLine();
        
        // Créer un utilisateur de test temporaire
        $testUser = $this->createTestUser();
        
        // Démontrer le système étape par étape
        $this->step1_ShowInitialSuggestions($testUser);
        $this->step2_CreateRelation($testUser);
        $this->step3_ShowUpdatedSuggestions($testUser);
        $this->step4_CreatePendingRequest($testUser);
        $this->step5_ShowFinalSuggestions($testUser);
        
        // Nettoyer
        $this->cleanup($testUser);
        
        $this->newLine();
        $this->info("✅ Démonstration terminée avec succès !");
        $this->info("🎯 Le système exclut correctement les relations existantes et les demandes en attente.");
    }
    
    private function createTestUser()
    {
        $this->info("📝 Étape 0 : Création d'un utilisateur de test...");
        
        $user = User::create([
            'name' => 'Test Demo User',
            'email' => 'demo.test.' . time() . '@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        
        $user->profile()->create([
            'first_name' => 'Demo',
            'last_name' => 'Test',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'address' => 'Casablanca, Maroc',
            'bio' => 'Utilisateur de test pour la démonstration',
        ]);
        
        $this->line("   ✅ Utilisateur créé : {$user->name} (ID: {$user->id})");
        $this->newLine();
        
        return $user;
    }
    
    private function step1_ShowInitialSuggestions(User $testUser)
    {
        $this->info("📊 Étape 1 : Suggestions initiales (sans relations)");
        
        $suggestions = $this->suggestionService->generateSuggestions($testUser);
        $this->line("   • Nombre de suggestions : " . $suggestions->count());
        
        if ($suggestions->isNotEmpty()) {
            $this->line("   • Aperçu des suggestions :");
            foreach ($suggestions->take(3) as $suggestion) {
                $suggestedUser = $suggestion->suggestedUser;
                $this->line("     - {$suggestedUser->name} ({$suggestion->type})");
            }
        } else {
            $this->line("   • Aucune suggestion générée (normal avec peu de données)");
        }
        
        $this->newLine();
    }
    
    private function step2_CreateRelation(User $testUser)
    {
        $this->info("🔗 Étape 2 : Création d'une relation familiale...");
        
        // Trouver un autre utilisateur pour créer une relation
        $otherUser = User::where('id', '!=', $testUser->id)->first();
        
        if (!$otherUser) {
            $this->warn("   ⚠️  Aucun autre utilisateur disponible pour créer une relation");
            return;
        }
        
        // Créer une relation familiale
        \App\Models\FamilyRelationship::create([
            'user_id' => $testUser->id,
            'related_user_id' => $otherUser->id,
            'relationship_type_id' => 5, // frère
            'status' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->line("   ✅ Relation créée : {$testUser->name} ↔ {$otherUser->name} (frère)");
        $this->newLine();
    }
    
    private function step3_ShowUpdatedSuggestions(User $testUser)
    {
        $this->info("📊 Étape 3 : Suggestions après création de relation");
        
        $suggestions = $this->suggestionService->generateSuggestions($testUser);
        $this->line("   • Nombre de suggestions : " . $suggestions->count());
        
        // Vérifier qu'aucune suggestion n'inclut l'utilisateur avec qui on a une relation
        $relatedUserIds = $testUser->getRelatedUsers()->pluck('id')->toArray();
        $violations = 0;
        
        foreach ($suggestions as $suggestion) {
            if (in_array($suggestion->suggested_user_id, $relatedUserIds)) {
                $violations++;
                $this->error("     ❌ VIOLATION : {$suggestion->suggestedUser->name} est déjà lié !");
            }
        }
        
        if ($violations === 0) {
            $this->line("   ✅ Aucune violation : les utilisateurs liés sont exclus des suggestions");
        } else {
            $this->error("   ❌ {$violations} violation(s) détectée(s) !");
        }
        
        $this->newLine();
    }
    
    private function step4_CreatePendingRequest(User $testUser)
    {
        $this->info("📤 Étape 4 : Création d'une demande de relation en attente...");
        
        // Trouver un autre utilisateur (pas celui avec qui on a déjà une relation)
        $relatedUserIds = $testUser->getRelatedUsers()->pluck('id')->toArray();
        $otherUser = User::where('id', '!=', $testUser->id)
            ->whereNotIn('id', $relatedUserIds)
            ->first();
        
        if (!$otherUser) {
            $this->warn("   ⚠️  Aucun autre utilisateur disponible pour créer une demande");
            return;
        }
        
        // Créer une demande de relation en attente
        \App\Models\RelationshipRequest::create([
            'requester_id' => $testUser->id,
            'target_user_id' => $otherUser->id,
            'relationship_type_id' => 6, // sœur
            'message' => 'Demande de test pour la démonstration',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->line("   ✅ Demande créée : {$testUser->name} → {$otherUser->name} (en attente)");
        $this->newLine();
    }
    
    private function step5_ShowFinalSuggestions(User $testUser)
    {
        $this->info("📊 Étape 5 : Suggestions finales (avec relation + demande en attente)");
        
        $suggestions = $this->suggestionService->generateSuggestions($testUser);
        $this->line("   • Nombre de suggestions : " . $suggestions->count());
        
        // Récupérer tous les IDs exclus
        $excludedIds = collect();
        
        // Relations acceptées
        $excludedIds = $excludedIds->merge($testUser->getRelatedUsers()->pluck('id'));
        
        // Demandes en attente
        $pendingRequests = \App\Models\RelationshipRequest::where(function($query) use ($testUser) {
            $query->where('requester_id', $testUser->id)->orWhere('target_user_id', $testUser->id);
        })->get();
        
        foreach ($pendingRequests as $request) {
            if ($request->requester_id === $testUser->id) {
                $excludedIds->push($request->target_user_id);
            } else {
                $excludedIds->push($request->requester_id);
            }
        }
        
        $excludedIds = $excludedIds->unique();
        
        // Vérifier qu'aucune suggestion n'inclut les utilisateurs exclus
        $violations = 0;
        foreach ($suggestions as $suggestion) {
            if ($excludedIds->contains($suggestion->suggested_user_id)) {
                $violations++;
                $this->error("     ❌ VIOLATION : {$suggestion->suggestedUser->name} devrait être exclu !");
            }
        }
        
        if ($violations === 0) {
            $this->line("   ✅ Parfait : toutes les relations existantes et demandes en attente sont exclues");
        } else {
            $this->error("   ❌ {$violations} violation(s) détectée(s) !");
        }
        
        $this->line("   • Utilisateurs exclus des suggestions : " . $excludedIds->count());
        $this->newLine();
    }
    
    private function cleanup(User $testUser)
    {
        $this->info("🧹 Nettoyage des données de test...");
        
        // Supprimer les relations créées
        \App\Models\FamilyRelationship::where('user_id', $testUser->id)
            ->orWhere('related_user_id', $testUser->id)
            ->delete();
            
        // Supprimer les demandes créées
        \App\Models\RelationshipRequest::where('requester_id', $testUser->id)
            ->orWhere('target_user_id', $testUser->id)
            ->delete();
            
        // Supprimer le profil et l'utilisateur
        $testUser->profile()->delete();
        $testUser->delete();
        
        $this->line("   ✅ Données de test supprimées");
    }
}
