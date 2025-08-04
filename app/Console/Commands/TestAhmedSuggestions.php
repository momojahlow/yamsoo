<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\SuggestionService;

class TestAhmedSuggestions extends Command
{
    protected $signature = 'test:ahmed-suggestions';
    protected $description = 'Test pourquoi Ahmed ne reçoit pas de suggestions';

    public function handle()
    {
        $this->info('🔍 TEST: Pourquoi Ahmed ne reçoit pas de suggestions');
        $this->info('=======================================================');

        // Récupérer Ahmed
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        if (!$ahmed) {
            $this->error('❌ Ahmed non trouvé');
            return;
        }

        $this->info("✅ Ahmed trouvé (ID: {$ahmed->id})");

        // Récupérer ses relations familiales
        $familyRelations = $ahmed->familyRelations()->with(['relatedUser', 'relationshipType'])->get();
        
        $this->info("\n🔗 Relations familiales d'Ahmed:");
        foreach ($familyRelations as $relation) {
            $this->info("   Ahmed → {$relation->relatedUser->name} : {$relation->relationshipType->name} ({$relation->relationshipType->display_name_fr})");
        }

        // Récupérer les relations où Ahmed est la cible
        $familyRelationsAsTarget = $ahmed->familyRelationsAsTarget()->with(['user', 'relationshipType'])->get();
        
        $this->info("\n🔗 Relations familiales vers Ahmed:");
        foreach ($familyRelationsAsTarget as $relation) {
            $this->info("   {$relation->user->name} → Ahmed : {$relation->relationshipType->name} ({$relation->relationshipType->display_name_fr})");
        }

        // Vérifier les suggestions existantes dans la base de données
        $this->info("\n🎯 Suggestions existantes dans la base de données pour Ahmed:");

        $existingSuggestions = \App\Models\Suggestion::where('user_id', $ahmed->id)
            ->with(['suggestedUser'])
            ->get();

        $this->info("📊 Nombre de suggestions en base: " . count($existingSuggestions));

        foreach ($existingSuggestions as $suggestion) {
            $this->info("   Ahmed → {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code} ({$suggestion->suggested_relation_name})");
        }

        // Tester manuellement la génération de suggestions pour Ahmed
        $this->info("\n🎯 Test manuel de génération de suggestions pour Ahmed:");

        $suggestionService = app(SuggestionService::class);

        try {
            $suggestions = $suggestionService->generateSuggestions($ahmed);

            $this->info("📊 Nombre de suggestions générées: " . count($suggestions));

            foreach ($suggestions as $suggestion) {
                $suggestedUser = $suggestion['suggested_user'] ?? null;
                $code = $suggestion['code'] ?? 'unknown';
                $description = $suggestion['description'] ?? 'unknown';

                if ($suggestedUser) {
                    $this->info("   Ahmed → {$suggestedUser->name} : {$code} ({$description})");
                } else {
                    $this->info("   Ahmed → [User manquant] : {$code} ({$description})");
                }
            }
            
            if (empty($suggestions)) {
                $this->warn("⚠️ Aucune suggestion générée pour Ahmed");
                
                // Analyser pourquoi
                $this->info("\n🔍 Analyse détaillée:");
                
                // Vérifier les membres de la famille
                $familyMembers = $ahmed->familyRelations()
                    ->where('status', 'accepted')
                    ->with(['relatedUser.profile', 'relationshipType'])
                    ->get();
                
                $this->info("📊 Membres de famille d'Ahmed: " . count($familyMembers));
                
                foreach ($familyMembers as $familyMember) {
                    $this->info("   Membre: {$familyMember->relatedUser->name} (relation: {$familyMember->relationshipType->name})");
                    
                    // Pour chaque membre, vérifier ses relations
                    $memberRelations = $familyMember->relatedUser->familyRelations()
                        ->where('status', 'accepted')
                        ->with(['relatedUser.profile', 'relationshipType'])
                        ->get();
                    
                    $this->info("     Ses relations: " . count($memberRelations));
                    foreach ($memberRelations as $memberRelation) {
                        if ($memberRelation->related_user_id !== $ahmed->id) {
                            $this->info("       → {$memberRelation->relatedUser->name} : {$memberRelation->relationshipType->name}");
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la génération de suggestions: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }

        $this->info("\n🎉 Test terminé !");
    }
}
