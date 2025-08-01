<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;

class TestSimpleAcceptance extends Command
{
    protected $signature = 'test:simple-acceptance';
    protected $description = 'Test simple d\'acceptation de relation pour identifier l\'erreur';

    public function handle()
    {
        $this->info("🔍 Test simple d'acceptation de relation");

        // Nettoyer d'abord
        $this->info("🧹 Nettoyage des relations existantes...");
        \App\Models\FamilyRelationship::truncate();
        \App\Models\RelationshipRequest::truncate();

        try {
            // Récupérer des utilisateurs
            $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
            $fatima = User::where('email', 'fatima.zahra@example.com')->first();

            if (!$ahmed || !$fatima) {
                $this->error("❌ Utilisateurs non trouvés");
                return;
            }

            // Récupérer le type de relation
            $wifeType = RelationshipType::where('name', 'wife')->first();
            if (!$wifeType) {
                $this->error("❌ Type de relation 'wife' non trouvé");
                return;
            }

            $this->info("✅ Utilisateurs et type trouvés");

            // Créer le service
            $service = app(FamilyRelationService::class);

            // Créer une demande
            $this->info("📝 Création de la demande...");
            $request = $service->createRelationshipRequest(
                $ahmed,
                $fatima->id,
                $wifeType->id,
                "Test simple"
            );

            $this->info("✅ Demande créée (ID: {$request->id})");

            // Accepter la demande
            $this->info("🔄 Acceptation de la demande...");
            $relation = $service->acceptRelationshipRequest($request);

            $this->info("✅ Relation acceptée avec succès (ID: {$relation->id})");

            // Maintenant tester la génération de suggestions
            $this->info("🔍 Test de génération de suggestions...");
            $suggestionService = app(\App\Services\SuggestionService::class);

            $suggestions = $suggestionService->generateSuggestions($fatima);
            $this->info("✅ Suggestions générées: " . $suggestions->count());

        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            $this->line("📍 Fichier: " . $e->getFile() . ":" . $e->getLine());
            $this->line("🔍 Trace:");
            $this->line($e->getTraceAsString());
        }
    }
}
