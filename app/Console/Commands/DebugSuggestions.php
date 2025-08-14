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
    protected $signature = 'debug:suggestions {user-name? : Nom de l\'utilisateur à déboguer}';

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
        $userName = $this->argument('user-name') ?? 'Amina';

        $user = User::where('name', 'like', "%{$userName}%")->first();

        if (!$user) {
            $this->error("❌ Utilisateur '{$userName}' non trouvé");
            return;
        }

        $this->info("🔍 DEBUG COMPLET POUR : {$user->name}");
        $this->info(str_repeat("=", 60));

        // Debug spécifique pour le problème parent/enfant
        $this->debugParentChildProblem($user);

        // 1. Afficher les relations existantes
        $this->debugExistingRelations($user);

        // 2. Afficher les utilisateurs exclus
        $this->debugExcludedUsers($user);

        // 3. Tester chaque type de suggestion
        $this->debugFamilySuggestions($user);

        // 4. Générer les suggestions finales
        $this->debugFinalSuggestions($user);
    }

    private function debugParentChildProblem(User $user)
    {
        $this->info("🎯 DEBUG SPÉCIFIQUE PROBLÈME PARENT/ENFANT:");

        // Charger tous les utilisateurs clés
        $amina = User::where('name', 'like', '%Amina%')->first();
        $fatima = User::where('name', 'like', '%Fatima%')->first();
        $ahmed = User::where('name', 'like', '%Ahmed%')->first();
        $mohamed = User::where('name', 'like', '%Mohammed%')->first();

        $this->info("Utilisateurs trouvés:");
        if ($amina) $this->info("   ✅ Amina: {$amina->name} (ID: {$amina->id})");
        if ($fatima) $this->info("   ✅ Fatima: {$fatima->name} (ID: {$fatima->id})");
        if ($ahmed) $this->info("   ✅ Ahmed: {$ahmed->name} (ID: {$ahmed->id})");
        if ($mohamed) $this->info("   ✅ Mohamed: {$mohamed->name} (ID: {$mohamed->id})");

        // Analyser TOUTES les relations
        $this->info("\n🔗 TOUTES LES RELATIONS EXISTANTES:");
        $allRelations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
        foreach ($allRelations as $rel) {
            $this->info("   {$rel->user->name} → {$rel->relatedUser->name} : {$rel->relationshipType->name} ({$rel->relationshipType->code})");
        }

        // Vérifications spécifiques si c'est Amina
        if (stripos($user->name, 'Amina') !== false && $ahmed && $fatima) {
            $this->info("\n🔍 VÉRIFICATIONS SPÉCIFIQUES AMINA:");

            // Relation Ahmed ↔ Fatima
            $ahmedFatimaRelation = FamilyRelationship::where(function($query) use ($ahmed, $fatima) {
                $query->where('user_id', $ahmed->id)->where('related_user_id', $fatima->id);
            })->orWhere(function($query) use ($ahmed, $fatima) {
                $query->where('user_id', $fatima->id)->where('related_user_id', $ahmed->id);
            })->with('relationshipType')->first();

            if ($ahmedFatimaRelation) {
                $this->info("   ✅ Ahmed ↔ Fatima: {$ahmedFatimaRelation->user->name} → {$ahmedFatimaRelation->relatedUser->name} : {$ahmedFatimaRelation->relationshipType->code}");
            } else {
                $this->error("   ❌ AUCUNE RELATION AHMED ↔ FATIMA TROUVÉE!");
            }

            // Relation Amina ↔ Ahmed
            $aminaAhmedRelation = FamilyRelationship::where(function($query) use ($user, $ahmed) {
                $query->where('user_id', $user->id)->where('related_user_id', $ahmed->id);
            })->orWhere(function($query) use ($user, $ahmed) {
                $query->where('user_id', $ahmed->id)->where('related_user_id', $user->id);
            })->with('relationshipType')->first();

            if ($aminaAhmedRelation) {
                $this->info("   ✅ Amina ↔ Ahmed: {$aminaAhmedRelation->user->name} → {$aminaAhmedRelation->relatedUser->name} : {$aminaAhmedRelation->relationshipType->code}");
            } else {
                $this->error("   ❌ AUCUNE RELATION AMINA ↔ AHMED TROUVÉE!");
            }

            $this->info("\n🧠 LOGIQUE ATTENDUE:");
            $this->info("   1. Amina → Ahmed : daughter (fille)");
            $this->info("   2. Ahmed → Fatima : husband (mari)");
            $this->info("   3. DÉDUCTION: Amina (enfant) + Fatima (conjoint d'Ahmed) = Fatima est mère");
            $this->info("   4. CAS 1: enfant + conjoint → parent");
            $this->info("   5. RÉSULTAT ATTENDU: mother");
        }

        $this->newLine();
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
