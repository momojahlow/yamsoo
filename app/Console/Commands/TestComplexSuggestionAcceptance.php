<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Suggestion;
use App\Models\RelationshipType;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class TestComplexSuggestionAcceptance extends Command
{
    protected $signature = 'test:complex-suggestion-acceptance';
    protected $description = 'Test l\'acceptation des suggestions complexes (Grand-père, Tante, Oncle, Grand-mère)';

    public function __construct(
        private SuggestionService $suggestionService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🔍 Test de l\'acceptation des suggestions complexes');

        // Trouver des utilisateurs de test
        $user1 = User::first();
        $user2 = User::skip(1)->first();

        if (!$user1 || !$user2) {
            $this->error('❌ Utilisateurs de test non trouvés');
            return;
        }

        $this->info("✅ Utilisateurs trouvés: {$user1->name} et {$user2->name}");

        // Test des relations complexes
        $complexRelations = ['grandfather', 'grandmother', 'uncle', 'aunt'];

        foreach ($complexRelations as $relationCode) {
            $this->info("\n📋 Test de la relation: {$relationCode}");

            // Vérifier que le type de relation existe
            $relationType = RelationshipType::where('name', $relationCode)->first();
            if (!$relationType) {
                $this->error("❌ Type de relation '{$relationCode}' non trouvé dans la base");
                continue;
            }

            $this->info("✅ Type de relation trouvé: {$relationType->display_name_fr}");

            // Créer une suggestion de test
            $suggestion = Suggestion::create([
                'user_id' => $user1->id,
                'suggested_user_id' => $user2->id,
                'type' => 'family_relation',
                'message' => "Test suggestion pour {$relationCode}",
                'status' => 'pending',
                'suggested_relation_code' => $relationCode,
                'suggested_relation_name' => $relationType->display_name_fr,
            ]);

            $this->info("✅ Suggestion créée avec ID: {$suggestion->id}");

            try {
                // Tester l'acceptation
                $this->suggestionService->acceptSuggestion($suggestion, $relationCode);
                $this->info("✅ Suggestion acceptée avec succès !");

                // Vérifier qu'une demande de relation a été créée
                $relationshipRequest = \App\Models\RelationshipRequest::where('requester_id', $user1->id)
                    ->where('target_user_id', $user2->id)
                    ->where('relationship_type_id', $relationType->id)
                    ->latest()
                    ->first();

                if ($relationshipRequest) {
                    $this->info("✅ Demande de relation créée avec ID: {$relationshipRequest->id}");
                } else {
                    $this->warn("⚠️ Aucune demande de relation trouvée");
                }

            } catch (\Exception $e) {
                $this->error("❌ Erreur lors de l'acceptation: " . $e->getMessage());
                $this->error("Trace: " . $e->getTraceAsString());
            }

            // Nettoyer
            $suggestion->delete();
            if (isset($relationshipRequest)) {
                $relationshipRequest->delete();
            }
        }

        $this->info("\n🎉 Test terminé !");
    }
}
