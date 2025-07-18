<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class DebugSuggestions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'debug:suggestions {user-id : ID de l\'utilisateur à déboguer}';

    /**
     * The console command description.
     */
    protected $description = 'Debug le système de suggestions pour un utilisateur';

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
        $userId = $this->argument('user-id');
        
        $user = User::with('profile')->find($userId);
        
        if (!$user) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");
            return;
        }
        
        $this->info("🔍 Debug des suggestions pour : {$user->name}");
        $this->newLine();
        
        // 1. Afficher les relations existantes
        $this->debugExistingRelations($user);
        
        // 2. Afficher les utilisateurs exclus
        $this->debugExcludedUsers($user);
        
        // 3. Tester chaque type de suggestion
        $this->debugFamilySuggestions($user);
        $this->debugNameSuggestions($user);
        $this->debugRegionSuggestions($user);
        
        // 4. Générer les suggestions finales
        $this->debugFinalSuggestions($user);
    }
    
    private function debugExistingRelations(User $user)
    {
        $this->info("1️⃣ Relations existantes :");
        
        $relations = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })->with(['user.profile', 'relatedUser.profile', 'relationshipType'])->get();
        
        if ($relations->isEmpty()) {
            $this->line("   • Aucune relation existante");
        } else {
            foreach ($relations as $relation) {
                $relatedUser = $relation->user_id === $user->id ? $relation->relatedUser : $relation->user;
                $this->line("   • {$relatedUser->name} (ID: {$relatedUser->id}) - {$relation->relationshipType->name_fr}");
            }
        }
        
        $this->newLine();
    }
    
    private function debugExcludedUsers(User $user)
    {
        $this->info("2️⃣ Utilisateurs exclus des suggestions :");
        
        // Utiliser la méthode privée via réflexion pour debug
        $reflection = new \ReflectionClass($this->suggestionService);
        $method = $reflection->getMethod('getAllRelatedUserIds');
        $method->setAccessible(true);
        
        $excludedIds = $method->invoke($this->suggestionService, $user);
        
        if (empty($excludedIds)) {
            $this->line("   • Aucun utilisateur exclu");
        } else {
            $excludedUsers = User::whereIn('id', $excludedIds)->get();
            foreach ($excludedUsers as $excludedUser) {
                $this->line("   • {$excludedUser->name} (ID: {$excludedUser->id})");
            }
        }
        
        $this->newLine();
    }
    
    private function debugFamilySuggestions(User $user)
    {
        $this->info("3️⃣ Suggestions familiales :");
        
        // Récupérer les relations existantes
        $existingRelations = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })
        ->where('status', 'accepted')
        ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
        ->get();
        
        if ($existingRelations->isEmpty()) {
            $this->line("   • Pas de relations existantes pour générer des suggestions familiales");
            $this->newLine();
            return;
        }
        
        foreach ($existingRelations as $relation) {
            $relatedUser = $relation->user_id === $user->id ? $relation->relatedUser : $relation->user;
            
            $this->line("   📋 Via {$relatedUser->name} :");
            
            // Chercher les relations de cette personne
            $familyMembers = FamilyRelationship::where(function($query) use ($relatedUser) {
                $query->where('user_id', $relatedUser->id)
                      ->orWhere('related_user_id', $relatedUser->id);
            })
            ->where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get();
            
            if ($familyMembers->isEmpty()) {
                $this->line("     • Aucun membre de famille trouvé");
            } else {
                foreach ($familyMembers as $familyRelation) {
                    $suggestedUser = $familyRelation->user_id === $relatedUser->id ? $familyRelation->relatedUser : $familyRelation->user;
                    
                    if ($suggestedUser->id === $user->id) {
                        continue; // Éviter l'utilisateur actuel
                    }
                    
                    $this->line("     • {$suggestedUser->name} (ID: {$suggestedUser->id}) - {$familyRelation->relationshipType->name_fr}");
                }
            }
        }
        
        $this->newLine();
    }
    
    private function debugNameSuggestions(User $user)
    {
        $this->info("4️⃣ Suggestions par nom :");
        
        $userProfile = $user->profile;
        if (!$userProfile || !$userProfile->last_name) {
            $this->line("   • Pas de nom de famille défini");
            $this->newLine();
            return;
        }
        
        $lastName = $userProfile->last_name;
        $this->line("   • Recherche pour le nom : {$lastName}");
        
        $similarUsers = User::where('id', '!=', $user->id)
            ->whereHas('profile', function($query) use ($lastName) {
                $query->where('last_name', 'like', "%{$lastName}%");
            })
            ->with('profile')
            ->get();
            
        if ($similarUsers->isEmpty()) {
            $this->line("   • Aucun utilisateur avec nom similaire trouvé");
        } else {
            foreach ($similarUsers as $similarUser) {
                $this->line("   • {$similarUser->name} (ID: {$similarUser->id})");
            }
        }
        
        $this->newLine();
    }
    
    private function debugRegionSuggestions(User $user)
    {
        $this->info("5️⃣ Suggestions par région :");
        
        $userProfile = $user->profile;
        if (!$userProfile || !$userProfile->address) {
            $this->line("   • Pas d'adresse définie");
            $this->newLine();
            return;
        }
        
        $address = $userProfile->address;
        $city = explode(',', $address)[0] ?? '';
        $this->line("   • Recherche pour la ville : {$city}");
        
        $sameRegionUsers = User::where('id', '!=', $user->id)
            ->whereHas('profile', function($query) use ($city) {
                $query->where('address', 'like', "%{$city}%");
            })
            ->with('profile')
            ->get();
            
        if ($sameRegionUsers->isEmpty()) {
            $this->line("   • Aucun utilisateur dans la même région trouvé");
        } else {
            foreach ($sameRegionUsers as $regionUser) {
                $this->line("   • {$regionUser->name} (ID: {$regionUser->id}) - {$regionUser->profile->address}");
            }
        }
        
        $this->newLine();
    }
    
    private function debugFinalSuggestions(User $user)
    {
        $this->info("6️⃣ Suggestions finales générées :");
        
        try {
            $suggestions = $this->suggestionService->generateSuggestions($user);
            
            if ($suggestions->isEmpty()) {
                $this->warn("   • Aucune suggestion générée");
            } else {
                foreach ($suggestions as $suggestion) {
                    $suggestedUser = $suggestion->suggestedUser;
                    $this->line("   • {$suggestedUser->name} (Type: {$suggestion->type})");
                    $this->line("     Raison: {$suggestion->message}");
                }
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur : " . $e->getMessage());
        }
    }
}
