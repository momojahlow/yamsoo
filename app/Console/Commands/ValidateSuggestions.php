<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class ValidateSuggestions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'validate:suggestions {--user-id= : ID de l\'utilisateur à tester}';

    /**
     * The console command description.
     */
    protected $description = 'Valide que les suggestions n\'incluent pas les relations existantes';

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
            $this->validateUserSuggestions($userId);
        } else {
            $this->validateAllUsersSuggestions();
        }
    }
    
    private function validateUserSuggestions(int $userId)
    {
        $user = User::with('profile')->find($userId);
        
        if (!$user) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");
            return;
        }
        
        $this->info("🔍 Validation des suggestions pour : {$user->name}");
        $this->newLine();
        
        // Récupérer les relations existantes
        $existingRelations = $this->getExistingRelations($user);
        $pendingRequests = $this->getPendingRequests($user);
        
        $this->info("📊 État actuel :");
        $this->line("   • Relations acceptées : " . $existingRelations->count());
        $this->line("   • Demandes en attente : " . $pendingRequests->count());
        $this->newLine();
        
        // Générer les suggestions
        $suggestions = $this->suggestionService->generateSuggestions($user);
        
        $this->info("💡 Suggestions générées : " . $suggestions->count());
        $this->newLine();
        
        // Valider qu'aucune suggestion n'est une relation existante
        $violations = $this->checkForViolations($user, $suggestions, $existingRelations, $pendingRequests);
        
        if ($violations->isEmpty()) {
            $this->info("✅ Toutes les suggestions sont valides !");
            $this->info("   Aucune relation existante n'a été suggérée.");
        } else {
            $this->error("❌ {$violations->count()} violation(s) détectée(s) :");
            $this->table(
                ['Utilisateur suggéré', 'Type de violation', 'Détails'],
                $violations->toArray()
            );
        }
        
        // Afficher quelques suggestions pour vérification
        if ($suggestions->isNotEmpty()) {
            $this->newLine();
            $this->info("📋 Aperçu des suggestions :");
            $suggestions->take(5)->each(function($suggestion) {
                $suggestedUser = $suggestion->suggestedUser;
                $this->line("   • {$suggestedUser->name} ({$suggestion->type})");
            });
        }
    }
    
    private function validateAllUsersSuggestions()
    {
        $this->info("🔍 Validation des suggestions pour tous les utilisateurs...");
        $this->newLine();
        
        $users = User::with('profile')->get();
        $totalViolations = 0;
        $usersWithViolations = 0;
        
        foreach ($users as $user) {
            $existingRelations = $this->getExistingRelations($user);
            $pendingRequests = $this->getPendingRequests($user);
            $suggestions = $this->suggestionService->generateSuggestions($user);
            
            $violations = $this->checkForViolations($user, $suggestions, $existingRelations, $pendingRequests);
            
            if ($violations->isNotEmpty()) {
                $usersWithViolations++;
                $totalViolations += $violations->count();
                $this->warn("⚠️  {$user->name} : {$violations->count()} violation(s)");
            }
        }
        
        $this->newLine();
        if ($totalViolations === 0) {
            $this->info("✅ Aucune violation détectée pour tous les utilisateurs !");
        } else {
            $this->error("❌ {$totalViolations} violation(s) détectée(s) pour {$usersWithViolations} utilisateur(s)");
            $this->warn("💡 Exécutez avec --user-id=X pour voir les détails d'un utilisateur spécifique");
        }
    }
    
    private function getExistingRelations(User $user)
    {
        return FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })->where('status', 'accepted')->with('relatedUser', 'user')->get();
    }
    
    private function getPendingRequests(User $user)
    {
        return RelationshipRequest::where(function($query) use ($user) {
            $query->where('requester_id', $user->id)
                  ->orWhere('target_user_id', $user->id);
        })->where('status', 'pending')->with('requester', 'targetUser')->get();
    }
    
    private function checkForViolations(User $user, $suggestions, $existingRelations, $pendingRequests)
    {
        $violations = collect();
        
        // IDs des utilisateurs avec relations existantes
        $existingUserIds = collect();
        foreach ($existingRelations as $relation) {
            if ($relation->user_id === $user->id) {
                $existingUserIds->push($relation->related_user_id);
            } else {
                $existingUserIds->push($relation->user_id);
            }
        }
        
        // IDs des utilisateurs avec demandes en attente
        $pendingUserIds = collect();
        foreach ($pendingRequests as $request) {
            if ($request->requester_id === $user->id) {
                $pendingUserIds->push($request->target_user_id);
            } else {
                $pendingUserIds->push($request->requester_id);
            }
        }
        
        // Vérifier chaque suggestion
        foreach ($suggestions as $suggestion) {
            $suggestedUserId = $suggestion->suggested_user_id;
            $suggestedUser = $suggestion->suggestedUser;
            
            if ($existingUserIds->contains($suggestedUserId)) {
                $violations->push([
                    'user' => $suggestedUser->name,
                    'type' => 'Relation existante',
                    'details' => 'Une relation familiale acceptée existe déjà'
                ]);
            }
            
            if ($pendingUserIds->contains($suggestedUserId)) {
                $violations->push([
                    'user' => $suggestedUser->name,
                    'type' => 'Demande en attente',
                    'details' => 'Une demande de relation est déjà en attente'
                ]);
            }
        }
        
        return $violations;
    }
}
