<?php

namespace App\Listeners;

use App\Events\RelationshipAccepted;
use App\Services\SuggestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateFamilySuggestions implements ShouldQueue
{
    use InteractsWithQueue;

    protected SuggestionService $suggestionService;

    /**
     * Create the event listener.
     */
    public function __construct(SuggestionService $suggestionService)
    {
        $this->suggestionService = $suggestionService;
    }

    /**
     * Handle the event.
     */
    public function handle(RelationshipAccepted $event): void
    {
        try {
            // Générer des suggestions pour les deux utilisateurs impliqués
            $this->generateSuggestionsForUser($event->requester);
            $this->generateSuggestionsForUser($event->target);
            
            Log::info("Suggestions familiales générées après acceptation de relation", [
                'requester' => $event->requester->name,
                'target' => $event->target->name,
                'relationship_type' => $event->relationshipRequest->relationshipType->name_fr ?? 'Unknown'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de la génération des suggestions familiales", [
                'error' => $e->getMessage(),
                'requester_id' => $event->requester->id,
                'target_id' => $event->target->id
            ]);
        }
    }

    /**
     * Génère des suggestions pour un utilisateur spécifique
     */
    private function generateSuggestionsForUser($user): void
    {
        // Supprimer les anciennes suggestions pour éviter les doublons
        $this->suggestionService->clearOldSuggestions($user);
        
        // Générer de nouvelles suggestions basées sur les relations actuelles
        $suggestions = $this->suggestionService->generateSuggestions($user);
        
        Log::info("Suggestions générées pour {$user->name}", [
            'user_id' => $user->id,
            'suggestions_count' => $suggestions->count()
        ]);
    }
}
