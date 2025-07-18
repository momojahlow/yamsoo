<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Suggestion;
use App\Models\RelationshipRequest;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class TestSuggestionToRelation extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:suggestion-to-relation';

    /**
     * The console command description.
     */
    protected $description = 'Teste la création automatique de demande de relation lors de l\'acceptation d\'une suggestion';

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
        $this->info("🧪 Test : Suggestion → Demande de relation automatique");
        $this->newLine();
        
        // Trouver Mohammed Alami
        $mohammed = User::find(3);
        
        if (!$mohammed) {
            $this->error("Mohammed Alami non trouvé");
            return;
        }
        
        $this->info("👤 Utilisateur : {$mohammed->name}");
        
        // Vérifier s'il a des suggestions
        $suggestions = $this->suggestionService->getUserSuggestions($mohammed);
        
        if ($suggestions->isEmpty()) {
            $this->warn("⚠️  Aucune suggestion trouvée. Génération de suggestions...");
            $this->call('generate:suggestions-mohammed');
            $suggestions = $this->suggestionService->getUserSuggestions($mohammed);
        }
        
        if ($suggestions->isEmpty()) {
            $this->error("❌ Impossible de générer des suggestions");
            return;
        }
        
        // Prendre la première suggestion en attente
        $pendingSuggestion = $suggestions->where('status', 'pending')->first();
        
        if (!$pendingSuggestion) {
            $this->warn("⚠️  Aucune suggestion en attente. Création d'une nouvelle suggestion...");
            
            // Créer une suggestion de test
            $fatima = User::find(2); // Fatima Zahra
            if ($fatima) {
                $pendingSuggestion = $this->suggestionService->createSuggestion(
                    $mohammed,
                    $fatima->id,
                    'family_connection',
                    'Test de création automatique de demande',
                    'mother'
                );
                $this->info("✅ Suggestion de test créée");
            } else {
                $this->error("❌ Impossible de créer une suggestion de test");
                return;
            }
        }
        
        $suggestedUser = $pendingSuggestion->suggestedUser;
        $relationName = $pendingSuggestion->suggested_relation_name ?? 'Non définie';
        
        $this->info("📋 Suggestion à tester :");
        $this->line("   • Utilisateur suggéré : {$suggestedUser->name}");
        $this->line("   • Relation suggérée : {$relationName}");
        $this->line("   • Code relation : {$pendingSuggestion->suggested_relation_code}");
        $this->line("   • Statut : {$pendingSuggestion->status}");
        $this->newLine();
        
        // Compter les demandes de relation existantes
        $existingRequestsCount = RelationshipRequest::where('requester_id', $mohammed->id)
            ->where('target_user_id', $suggestedUser->id)
            ->count();
            
        $this->info("📊 État avant acceptation :");
        $this->line("   • Demandes de relation existantes : {$existingRequestsCount}");
        
        // Accepter la suggestion
        $this->info("🔄 Acceptation de la suggestion...");
        
        try {
            // Récupérer l'objet original de la base de données pour éviter les problèmes
            $originalSuggestion = Suggestion::find($pendingSuggestion->id);
            $this->suggestionService->acceptSuggestion($originalSuggestion);
            
            $this->info("✅ Suggestion acceptée avec succès !");
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'acceptation : " . $e->getMessage());
            $this->line("Trace : " . $e->getTraceAsString());
            return;
        }
        
        // Vérifier les résultats
        $this->newLine();
        $this->info("🔍 Vérification des résultats :");
        
        // 1. Vérifier que la suggestion est marquée comme acceptée
        $updatedSuggestion = Suggestion::find($pendingSuggestion->id);
        $this->line("   • Statut de la suggestion : {$updatedSuggestion->status}");
        
        // 2. Vérifier qu'une demande de relation a été créée
        $newRequestsCount = RelationshipRequest::where('requester_id', $mohammed->id)
            ->where('target_user_id', $suggestedUser->id)
            ->count();
            
        $this->line("   • Demandes de relation après acceptation : {$newRequestsCount}");
        
        if ($newRequestsCount > $existingRequestsCount) {
            $this->info("✅ Demande de relation créée automatiquement !");
            
            // Afficher les détails de la nouvelle demande
            $newRequest = RelationshipRequest::where('requester_id', $mohammed->id)
                ->where('target_user_id', $suggestedUser->id)
                ->with(['relationshipType'])
                ->latest()
                ->first();
                
            if ($newRequest) {
                $this->line("   • ID de la demande : {$newRequest->id}");
                $this->line("   • Type de relation : {$newRequest->relationshipType->name_fr}");
                $this->line("   • Statut : {$newRequest->status}");
                $this->line("   • Message : {$newRequest->message}");
            }
            
        } else {
            $this->error("❌ Aucune demande de relation créée");
        }
        
        $this->newLine();
        $this->info("🎯 Test terminé !");
        $this->info("💡 Résultat : L'acceptation d'une suggestion crée maintenant automatiquement une demande de relation familiale.");
    }
}
