<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SuggestionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SimulateLoginSuggestions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'simulate:login-suggestions {user-id : ID de l\'utilisateur qui se connecte}';

    /**
     * The console command description.
     */
    protected $description = 'Simule la connexion d\'un utilisateur et génère des suggestions automatiques';

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
        
        $this->info("🔐 Simulation de connexion pour : {$user->name}");
        $this->newLine();
        
        // Vérifier si des suggestions ont déjà été générées aujourd'hui
        $cacheKey = "suggestions_generated_for_user_{$user->id}";
        $lastGenerated = Cache::get($cacheKey);
        
        if ($lastGenerated && $lastGenerated >= now()->startOfDay()) {
            $this->warn("⚠️  Des suggestions ont déjà été générées aujourd'hui à {$lastGenerated->format('H:i:s')}");
            $this->line("Voulez-vous forcer la régénération ? (Tapez 'oui' pour continuer)");
            
            if (trim(fgets(STDIN)) !== 'oui') {
                $this->info("Génération annulée.");
                return;
            }
        }
        
        // Afficher le contexte familial actuel
        $this->showFamilyContext($user);
        
        // Générer des suggestions automatiques
        $this->info("💡 Génération de suggestions automatiques...");
        
        try {
            // Nettoyer les anciennes suggestions
            $this->suggestionService->clearOldSuggestions($user);
            
            // Générer de nouvelles suggestions
            $suggestions = $this->suggestionService->generateAutomaticSuggestions($user);
            
            // Marquer comme généré
            Cache::put($cacheKey, now(), now()->endOfDay());
            
            $this->displaySuggestions($suggestions);
            
            $this->newLine();
            $this->info("✅ Suggestions générées avec succès !");
            $this->info("📊 {$suggestions->count()} suggestion(s) disponible(s) pour {$user->name}");
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la génération des suggestions : " . $e->getMessage());
        }
    }
    
    private function showFamilyContext(User $user)
    {
        $this->info("👨‍👩‍👧‍👦 Contexte familial actuel :");
        
        $familyRelations = \App\Models\FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })
        ->where('status', 'accepted')
        ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
        ->get();
        
        if ($familyRelations->isEmpty()) {
            $this->line("   • Aucune relation familiale existante");
        } else {
            $this->line("   • {$familyRelations->count()} relation(s) familiale(s) :");
            
            foreach ($familyRelations as $relation) {
                if ($relation->user_id === $user->id) {
                    $relatedUser = $relation->relatedUser;
                    $relationType = $relation->relationshipType->name_fr;
                } else {
                    $relatedUser = $relation->user;
                    $relationType = $this->getInverseRelationName($relation->relationshipType);
                }
                
                $this->line("     - {$relatedUser->name} ({$relationType})");
            }
        }
        
        $this->newLine();
    }
    
    private function displaySuggestions($suggestions)
    {
        if ($suggestions->isEmpty()) {
            $this->warn("⚠️  Aucune nouvelle suggestion générée");
            $this->line("   Cela peut arriver si :");
            $this->line("   • L'utilisateur a déjà des relations avec tous les contacts potentiels");
            $this->line("   • Il n'y a pas assez de données pour générer des suggestions");
            return;
        }
        
        $this->info("🎯 Suggestions générées :");
        
        foreach ($suggestions as $index => $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $type = $suggestion->type;
            $message = $suggestion->message ?? 'Connexion suggérée';
            $relationCode = $suggestion->suggested_relation_code;
            
            $this->line("");
            $this->line("   " . ($index + 1) . ". {$suggestedUser->name}");
            $this->line("      Genre: " . ($suggestedUser->profile?->gender_label ?? 'Non défini'));
            $this->line("      Type: {$type}");
            $this->line("      Relation suggérée: " . $this->getRelationNameFromCode($relationCode));
            $this->line("      Raison: {$message}");
        }
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
    
    private function getRelationNameFromCode(?string $code): string
    {
        if (!$code) {
            return 'Non spécifiée';
        }
        
        $relationNames = [
            'father' => 'Père',
            'mother' => 'Mère',
            'son' => 'Fils',
            'daughter' => 'Fille',
            'brother' => 'Frère',
            'sister' => 'Sœur',
            'husband' => 'Mari',
            'wife' => 'Épouse',
        ];
        
        return $relationNames[$code] ?? ucfirst($code);
    }
}
