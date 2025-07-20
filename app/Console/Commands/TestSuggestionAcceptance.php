<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Suggestion;
use App\Models\FamilyRelationship;
use App\Services\SuggestionService;

class TestSuggestionAcceptance extends Command
{
    protected $signature = 'test:suggestion-acceptance';
    protected $description = 'Tester l\'acceptation des suggestions et la création automatique de relations';

    public function handle()
    {
        $this->info('🔧 TEST DE L\'ACCEPTATION DES SUGGESTIONS');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Trouver Nadia Berrada
        $nadia = User::where('name', 'like', '%Nadia%')->where('name', 'like', '%Berrada%')->first();

        if (!$nadia) {
            $this->error('❌ Nadia Berrada non trouvée');
            return 1;
        }

        $this->info("👤 UTILISATRICE DE TEST : {$nadia->name} (ID: {$nadia->id})");
        $this->newLine();

        // Vérifier les suggestions en attente
        $this->info('1️⃣ SUGGESTIONS EN ATTENTE POUR NADIA :');
        $pendingSuggestions = Suggestion::where('user_id', $nadia->id)
            ->where('status', 'pending')
            ->with(['suggestedUser'])
            ->get();

        if ($pendingSuggestions->isEmpty()) {
            $this->warn('⚠️  Aucune suggestion en attente. Génération de nouvelles suggestions...');

            // Générer de nouvelles suggestions
            $suggestionService = app(\App\Services\IntelligentSuggestionService::class);
            $newSuggestions = $suggestionService->generateIntelligentSuggestions($nadia);
            $this->line("   ✨ {$newSuggestions} nouvelles suggestions générées");

            // Recharger les suggestions
            $pendingSuggestions = Suggestion::where('user_id', $nadia->id)
                ->where('status', 'pending')
                ->with(['suggestedUser'])
                ->get();
        }

        foreach ($pendingSuggestions as $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $relationCode = $suggestion->suggested_relation_code;
            $relationName = $this->getRelationName($relationCode);

            $this->line("   💡 {$suggestedUser->name} - {$relationName} ({$relationCode})");
        }
        $this->newLine();

        // Tester l'acceptation d'une suggestion
        if ($pendingSuggestions->isNotEmpty()) {
            $this->info('2️⃣ TEST D\'ACCEPTATION D\'UNE SUGGESTION :');

            // Prendre la première suggestion (idéalement Youssef Bennani comme beau-père)
            $testSuggestion = $pendingSuggestions->first();
            $suggestedUser = $testSuggestion->suggestedUser;
            $relationCode = $testSuggestion->suggested_relation_code;
            $relationName = $this->getRelationName($relationCode);

            $this->line("   🎯 Test avec : {$suggestedUser->name} - {$relationName}");

            // Vérifier les relations existantes AVANT acceptation
            $existingRelationsBefore = FamilyRelationship::where(function($query) use ($nadia, $suggestedUser) {
                $query->where('user_id', $nadia->id)->where('related_user_id', $suggestedUser->id);
            })->orWhere(function($query) use ($nadia, $suggestedUser) {
                $query->where('user_id', $suggestedUser->id)->where('related_user_id', $nadia->id);
            })->count();

            $this->line("   📊 Relations existantes AVANT : {$existingRelationsBefore}");

            // Accepter la suggestion
            try {
                $suggestionServiceTest = app(SuggestionService::class);
                $suggestionServiceTest->acceptSuggestion($testSuggestion);

                $this->line("   ✅ Suggestion acceptée avec succès !");

                // Vérifier les relations créées APRÈS acceptation
                $existingRelationsAfter = FamilyRelationship::where(function($query) use ($nadia, $suggestedUser) {
                    $query->where('user_id', $nadia->id)->where('related_user_id', $suggestedUser->id);
                })->orWhere(function($query) use ($nadia, $suggestedUser) {
                    $query->where('user_id', $suggestedUser->id)->where('related_user_id', $nadia->id);
                })->with(['relationshipType'])->get();

                $this->line("   📊 Relations créées APRÈS : {$existingRelationsAfter->count()}");

                if ($existingRelationsAfter->isNotEmpty()) {
                    $this->line("   🎉 RELATIONS CRÉÉES :");
                    foreach ($existingRelationsAfter as $relation) {
                        $from = $relation->user;
                        $to = $relation->relatedUser;
                        $relationType = $relation->relationshipType;

                        $this->line("      • {$from->name} → {$to->name} : {$relationType->name_fr} ({$relationType->code})");
                    }
                } else {
                    $this->line("   ❌ AUCUNE RELATION CRÉÉE - PROBLÈME DÉTECTÉ !");
                }

                // Vérifier le statut de la suggestion
                $testSuggestion->refresh();
                $this->line("   📋 Statut de la suggestion : {$testSuggestion->status}");

            } catch (\Exception $e) {
                $this->line("   ❌ ERREUR lors de l'acceptation : {$e->getMessage()}");
                $this->line("   🔍 Trace : " . $e->getTraceAsString());
            }

        } else {
            $this->warn('⚠️  Aucune suggestion disponible pour les tests');
        }

        $this->newLine();

        // Statistiques finales
        $this->info('3️⃣ STATISTIQUES FINALES :');

        $totalSuggestions = Suggestion::where('user_id', $nadia->id)->count();
        $acceptedSuggestions = Suggestion::where('user_id', $nadia->id)->where('status', 'accepted')->count();
        $pendingSuggestionsCount = Suggestion::where('user_id', $nadia->id)->where('status', 'pending')->count();
        $totalRelations = FamilyRelationship::where('user_id', $nadia->id)->count();

        $this->line("   📊 Total suggestions : {$totalSuggestions}");
        $this->line("   ✅ Suggestions acceptées : {$acceptedSuggestions}");
        $this->line("   ⏳ Suggestions en attente : {$pendingSuggestionsCount}");
        $this->line("   👥 Relations familiales : {$totalRelations}");

        $this->newLine();
        $this->info('🎯 TEST TERMINÉ !');

        if (($existingRelationsAfter ?? collect())->isNotEmpty()) {
            $this->line('   ✅ Le système d\'acceptation des suggestions fonctionne correctement.');
        } else {
            $this->line('   ❌ Problème détecté avec l\'acceptation des suggestions.');
            $this->line('   🔧 Vérifiez les logs et la méthode createDirectRelationship.');
        }

        return 0;
    }

    /**
     * Obtenir le nom de la relation depuis le code
     */
    private function getRelationName(string $relationCode): string
    {
        $relationNames = [
            'father_in_law' => 'Beau-père',
            'mother_in_law' => 'Belle-mère',
            'brother_in_law' => 'Beau-frère',
            'sister_in_law' => 'Belle-sœur',
            'stepson' => 'Beau-fils',
            'stepdaughter' => 'Belle-fille',
            'father' => 'Père',
            'mother' => 'Mère',
            'brother' => 'Frère',
            'sister' => 'Sœur',
            'son' => 'Fils',
            'daughter' => 'Fille',
            'husband' => 'Mari',
            'wife' => 'Épouse',
            'uncle_paternal' => 'Oncle paternel',
            'aunt_paternal' => 'Tante paternelle',
            'uncle_maternal' => 'Oncle maternel',
            'aunt_maternal' => 'Tante maternelle',
            'nephew' => 'Neveu',
            'niece' => 'Nièce',
            'cousin_paternal_m' => 'Cousin paternel',
            'cousin_paternal_f' => 'Cousine paternelle',
            'cousin_maternal_m' => 'Cousin maternel',
            'cousin_maternal_f' => 'Cousine maternelle',
        ];

        return $relationNames[$relationCode] ?? $relationCode;
    }
}
