<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class TestSuggestionSystem extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:suggestions {--user-id= : ID de l\'utilisateur à tester}';

    /**
     * The console command description.
     */
    protected $description = 'Teste le système de suggestions avec des données de démonstration';

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
        $userId = $this->option('user-id') ?? 1;
        
        $user = User::with('profile')->find($userId);
        
        if (!$user) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");
            return;
        }
        
        $this->info("🧪 Test du système de suggestions pour : {$user->name}");
        $this->newLine();
        
        // Afficher l'état actuel
        $this->showCurrentState($user);
        
        // Tester les différents types de suggestions
        $this->testNameBasedSuggestions($user);
        $this->testRegionBasedSuggestions($user);
        $this->testFamilyBasedSuggestions($user);
        
        // Test de validation finale
        $this->validateNoExistingRelations($user);
    }
    
    private function showCurrentState(User $user)
    {
        $this->info("📊 État actuel de {$user->name} :");
        
        // Relations acceptées
        $acceptedRelations = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)->orWhere('related_user_id', $user->id);
        })->where('status', 'accepted')->with(['user.profile', 'relatedUser.profile', 'relationshipType'])->get();
        
        $this->line("   • Relations acceptées : " . $acceptedRelations->count());
        foreach ($acceptedRelations as $relation) {
            $relatedUser = $relation->user_id === $user->id ? $relation->relatedUser : $relation->user;
            $this->line("     - {$relatedUser->name} ({$relation->relationshipType->name_fr})");
        }
        
        // Demandes en attente
        $pendingRequests = RelationshipRequest::where(function($query) use ($user) {
            $query->where('requester_id', $user->id)->orWhere('target_user_id', $user->id);
        })->where('status', 'pending')->with(['requester', 'targetUser'])->get();
        
        $this->line("   • Demandes en attente : " . $pendingRequests->count());
        foreach ($pendingRequests as $request) {
            $otherUser = $request->requester_id === $user->id ? $request->targetUser : $request->requester;
            $direction = $request->requester_id === $user->id ? 'envoyée à' : 'reçue de';
            $this->line("     - {$direction} {$otherUser->name}");
        }
        
        $this->newLine();
    }
    
    private function testNameBasedSuggestions(User $user)
    {
        $this->info("🔤 Test des suggestions basées sur les noms :");
        
        $userProfile = $user->profile;
        if (!$userProfile || !$userProfile->last_name) {
            $this->warn("   ⚠️  Pas de nom de famille défini pour cet utilisateur");
            return;
        }
        
        $lastName = $userProfile->last_name;
        $this->line("   • Recherche d'utilisateurs avec le nom '{$lastName}'...");
        
        // Récupérer les IDs exclus
        $excludedIds = $this->getExcludedUserIds($user);
        
        $similarUsers = User::where('id', '!=', $user->id)
            ->whereNotIn('id', $excludedIds)
            ->whereHas('profile', function($query) use ($lastName) {
                $query->where('last_name', 'like', "%{$lastName}%");
            })
            ->with('profile')
            ->get();
            
        $this->line("   • {$similarUsers->count()} utilisateur(s) trouvé(s) avec nom similaire");
        foreach ($similarUsers->take(3) as $similarUser) {
            $gender = $similarUser->profile?->gender_label ?? 'Non défini';
            $this->line("     - {$similarUser->name} ({$gender})");
        }
        
        $this->newLine();
    }
    
    private function testRegionBasedSuggestions(User $user)
    {
        $this->info("🌍 Test des suggestions basées sur la région :");
        
        $userProfile = $user->profile;
        if (!$userProfile || !$userProfile->address) {
            $this->warn("   ⚠️  Pas d'adresse définie pour cet utilisateur");
            return;
        }
        
        $address = $userProfile->address;
        $city = explode(',', $address)[0] ?? '';
        $this->line("   • Recherche d'utilisateurs dans '{$city}'...");
        
        // Récupérer les IDs exclus
        $excludedIds = $this->getExcludedUserIds($user);
        
        $sameRegionUsers = User::where('id', '!=', $user->id)
            ->whereNotIn('id', $excludedIds)
            ->whereHas('profile', function($query) use ($city) {
                $query->where('address', 'like', "%{$city}%");
            })
            ->with('profile')
            ->get();
            
        $this->line("   • {$sameRegionUsers->count()} utilisateur(s) trouvé(s) dans la même région");
        foreach ($sameRegionUsers->take(3) as $regionUser) {
            $gender = $regionUser->profile?->gender_label ?? 'Non défini';
            $this->line("     - {$regionUser->name} ({$gender})");
        }
        
        $this->newLine();
    }
    
    private function testFamilyBasedSuggestions(User $user)
    {
        $this->info("👨‍👩‍👧‍👦 Test des suggestions basées sur la famille :");
        
        $existingRelations = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)->orWhere('related_user_id', $user->id);
        })->where('status', 'accepted')->with(['user.profile', 'relatedUser.profile', 'relationshipType'])->get();
        
        if ($existingRelations->isEmpty()) {
            $this->warn("   ⚠️  Aucune relation familiale existante pour analyser");
            return;
        }
        
        $this->line("   • Analyse des connexions via {$existingRelations->count()} relation(s) existante(s)...");
        
        $potentialConnections = 0;
        foreach ($existingRelations as $relation) {
            $relatedUser = $relation->user_id === $user->id ? $relation->relatedUser : $relation->user;
            
            // Chercher les relations de cette personne
            $secondDegreeRelations = FamilyRelationship::where(function($query) use ($relatedUser) {
                $query->where('user_id', $relatedUser->id)->orWhere('related_user_id', $relatedUser->id);
            })->where('status', 'accepted')->count();
            
            $this->line("     - Via {$relatedUser->name} : {$secondDegreeRelations} connexion(s) potentielle(s)");
            $potentialConnections += $secondDegreeRelations;
        }
        
        $this->line("   • Total : {$potentialConnections} connexion(s) de second degré à analyser");
        $this->newLine();
    }
    
    private function validateNoExistingRelations(User $user)
    {
        $this->info("✅ Validation finale :");
        
        $suggestions = $this->suggestionService->generateSuggestions($user);
        $this->line("   • {$suggestions->count()} suggestion(s) générée(s)");
        
        if ($suggestions->isEmpty()) {
            $this->warn("   ⚠️  Aucune suggestion générée (normal si l'utilisateur a beaucoup de relations)");
            return;
        }
        
        // Vérifier qu'aucune suggestion n'est une relation existante
        $excludedIds = $this->getExcludedUserIds($user);
        $violations = 0;
        
        foreach ($suggestions as $suggestion) {
            if (in_array($suggestion->suggested_user_id, $excludedIds)) {
                $violations++;
                $this->error("     ❌ VIOLATION : {$suggestion->suggestedUser->name} est déjà lié !");
            }
        }
        
        if ($violations === 0) {
            $this->info("   ✅ Toutes les suggestions sont valides !");
            $this->line("   • Aucune relation existante n'a été suggérée");
        } else {
            $this->error("   ❌ {$violations} violation(s) détectée(s) !");
        }
    }
    
    private function getExcludedUserIds(User $user): array
    {
        $excludedIds = collect();
        
        // Relations acceptées
        $acceptedRelations = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)->orWhere('related_user_id', $user->id);
        })->get();
        
        foreach ($acceptedRelations as $relation) {
            if ($relation->user_id === $user->id) {
                $excludedIds->push($relation->related_user_id);
            } else {
                $excludedIds->push($relation->user_id);
            }
        }
        
        // Demandes en attente
        $pendingRequests = RelationshipRequest::where(function($query) use ($user) {
            $query->where('requester_id', $user->id)->orWhere('target_user_id', $user->id);
        })->get();
        
        foreach ($pendingRequests as $request) {
            if ($request->requester_id === $user->id) {
                $excludedIds->push($request->target_user_id);
            } else {
                $excludedIds->push($request->requester_id);
            }
        }
        
        return $excludedIds->unique()->toArray();
    }
}
