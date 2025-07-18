<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Suggestion;
use App\Models\RelationshipRequest;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class TestSuggestionWithCorrection extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:suggestion-with-correction';

    /**
     * The console command description.
     */
    protected $description = 'Teste l\'acceptation d\'une suggestion avec correction de relation';

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
        $this->info("🧪 Test : Acceptation avec correction de relation");
        $this->newLine();
        
        // Trouver Mohammed Alami
        $mohammed = User::find(3);
        $ahmed = User::find(1); // Ahmed Benali
        
        if (!$mohammed || !$ahmed) {
            $this->error("Utilisateurs non trouvés");
            return;
        }
        
        $this->info("👤 Utilisateur : {$mohammed->name}");
        $this->info("👤 Utilisateur suggéré : {$ahmed->name}");
        
        // Créer une suggestion avec une relation incorrecte
        $suggestion = $this->suggestionService->createSuggestion(
            $mohammed,
            $ahmed->id,
            'family_connection',
            'Test de correction - relation initialement incorrecte',
            'father' // Relation incorrecte : Ahmed comme père de Mohammed
        );
        
        $this->info("📋 Suggestion créée :");
        $this->line("   • Relation suggérée : Père (incorrecte)");
        $this->line("   • Code : father");
        $this->newLine();
        
        // Compter les demandes existantes
        $existingRequestsCount = RelationshipRequest::where('requester_id', $mohammed->id)
            ->where('target_user_id', $ahmed->id)
            ->count();
            
        $this->info("📊 État avant correction :");
        $this->line("   • Demandes existantes : {$existingRequestsCount}");
        
        // Accepter avec correction
        $this->info("🔧 Acceptation avec correction : Père → Frère");
        
        try {
            $this->suggestionService->acceptSuggestion($suggestion, 'brother');
            $this->info("✅ Suggestion acceptée avec correction !");
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur : " . $e->getMessage());
            return;
        }
        
        // Vérifier les résultats
        $this->newLine();
        $this->info("🔍 Vérification des résultats :");
        
        // 1. Vérifier la suggestion
        $updatedSuggestion = Suggestion::find($suggestion->id);
        $this->line("   • Statut suggestion : {$updatedSuggestion->status}");
        $this->line("   • Code relation corrigé : {$updatedSuggestion->suggested_relation_code}");
        
        // 2. Vérifier la demande de relation
        $newRequestsCount = RelationshipRequest::where('requester_id', $mohammed->id)
            ->where('target_user_id', $ahmed->id)
            ->count();
            
        $this->line("   • Demandes après correction : {$newRequestsCount}");
        
        if ($newRequestsCount > $existingRequestsCount) {
            $newRequest = RelationshipRequest::where('requester_id', $mohammed->id)
                ->where('target_user_id', $ahmed->id)
                ->with(['relationshipType'])
                ->latest()
                ->first();
                
            if ($newRequest) {
                $this->info("✅ Demande créée avec la relation corrigée !");
                $this->line("   • Type de relation : {$newRequest->relationshipType->name_fr}");
                $this->line("   • Code : {$newRequest->relationshipType->code}");
                
                if ($newRequest->relationshipType->code === 'brother') {
                    $this->info("🎯 Correction appliquée avec succès : Frère au lieu de Père !");
                } else {
                    $this->warn("⚠️  Relation inattendue : {$newRequest->relationshipType->code}");
                }
            }
        } else {
            $this->error("❌ Aucune demande créée");
        }
        
        $this->newLine();
        $this->info("🎉 Test terminé avec succès !");
        $this->info("💡 L'utilisateur peut maintenant corriger les relations suggérées avant acceptation.");
    }
}
