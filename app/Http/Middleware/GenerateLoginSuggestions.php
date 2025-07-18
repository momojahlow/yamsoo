<?php

namespace App\Http\Middleware;

use App\Services\SuggestionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class GenerateLoginSuggestions
{
    protected SuggestionService $suggestionService;

    public function __construct(SuggestionService $suggestionService)
    {
        $this->suggestionService = $suggestionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Vérifier si l'utilisateur est connecté
        $user = $request->user();
        if (!$user) {
            return $response;
        }

        // Vérifier si des suggestions ont déjà été générées récemment
        $cacheKey = "suggestions_generated_for_user_{$user->id}";
        $lastGenerated = Cache::get($cacheKey);

        // Générer des suggestions seulement si :
        // 1. Aucune suggestion n'a été générée aujourd'hui
        // 2. Ou si c'est la première connexion de la journée
        if (!$lastGenerated || $lastGenerated < now()->startOfDay()) {
            
            try {
                // Générer des suggestions automatiques
                $suggestions = $this->suggestionService->generateAutomaticSuggestions($user);
                
                // Marquer comme généré pour éviter les répétitions
                Cache::put($cacheKey, now(), now()->endOfDay());
                
                // Log pour debug (optionnel)
                \Log::info("Suggestions générées à la connexion", [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'suggestions_count' => $suggestions->count(),
                    'timestamp' => now()
                ]);
                
            } catch (\Exception $e) {
                // Ne pas bloquer la navigation en cas d'erreur
                \Log::error("Erreur lors de la génération des suggestions à la connexion", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $response;
    }
}
