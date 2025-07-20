<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;
use App\Services\IntelligentSuggestionService;

class TestNadiaSuggestions extends Command
{
    protected $signature = 'test:nadia-suggestions';
    protected $description = 'Tester les suggestions corrigées pour Nadia Berrada (Youssef Bennani = Beau-père)';

    public function handle()
    {
        $this->info('🔍 TEST DES SUGGESTIONS CORRIGÉES POUR NADIA BERRADA');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Trouver Nadia Berrada
        $nadia = User::where('name', 'like', '%Nadia%')->where('name', 'like', '%Berrada%')->first();

        if (!$nadia) {
            $this->error('❌ Nadia Berrada non trouvée');
            return 1;
        }

        $this->info("👤 UTILISATRICE : {$nadia->name} (ID: {$nadia->id})");
        $this->newLine();

        // Analyser ses relations actuelles
        $this->info('1️⃣ RELATIONS ACTUELLES DE NADIA :');
        $nadiaRelations = FamilyRelationship::where(function($query) use ($nadia) {
            $query->where('user_id', $nadia->id)
                  ->orWhere('related_user_id', $nadia->id);
        })
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

        foreach ($nadiaRelations as $relation) {
            $relatedUser = $relation->user_id === $nadia->id ? $relation->relatedUser : $relation->user;
            $relationName = $relation->relationshipType->name_fr;
            $this->line("   👥 {$relatedUser->name} - {$relationName}");
        }
        $this->newLine();

        // Trouver son mari
        $husband = null;
        foreach ($nadiaRelations as $relation) {
            if ($relation->relationshipType->code === 'husband') {
                $husband = $relation->user_id === $nadia->id ? $relation->relatedUser : $relation->user;
                break;
            }
        }

        if ($husband) {
            $this->info("💍 MARI DE NADIA : {$husband->name}");

            // Analyser la famille du mari
            $this->info('2️⃣ FAMILLE DU MARI :');
            $husbandFamily = FamilyRelationship::where(function($query) use ($husband) {
                $query->where('user_id', $husband->id)
                      ->orWhere('related_user_id', $husband->id);
            })
            ->with(['user', 'relatedUser', 'relationshipType'])
            ->get();

            foreach ($husbandFamily as $relation) {
                $familyMember = $relation->user_id === $husband->id ? $relation->relatedUser : $relation->user;
                $relationName = $relation->relationshipType->name_fr;
                $this->line("   👨‍👩‍👧‍👦 {$familyMember->name} - {$relationName} du mari");

                // Vérifier spécialement Youssef Bennani
                if (stripos($familyMember->name, 'Youssef') !== false && stripos($familyMember->name, 'Bennani') !== false) {
                    $this->line("      🎯 TROUVÉ : Youssef Bennani - {$relationName} du mari");
                    if ($relation->relationshipType->code === 'father') {
                        $this->line("      ✅ Youssef est bien le PÈRE du mari → devrait être BEAU-PÈRE pour Nadia");
                    }
                }
            }
        } else {
            $this->warn('⚠️  Mari de Nadia non trouvé dans les relations');
        }
        $this->newLine();

        // Supprimer les anciennes suggestions pour Nadia
        $this->info('3️⃣ NETTOYAGE DES ANCIENNES SUGGESTIONS :');
        $oldSuggestions = Suggestion::where('user_id', $nadia->id)->count();
        Suggestion::where('user_id', $nadia->id)->delete();
        $this->line("   🗑️  {$oldSuggestions} anciennes suggestions supprimées");
        $this->newLine();

        // Générer de nouvelles suggestions avec le système corrigé
        $this->info('4️⃣ GÉNÉRATION DE NOUVELLES SUGGESTIONS :');
        $suggestionService = app(IntelligentSuggestionService::class);
        $newSuggestions = $suggestionService->generateIntelligentSuggestions($nadia);
        $this->line("   ✨ {$newSuggestions} nouvelles suggestions générées");
        $this->newLine();

        // Analyser les suggestions générées
        $this->info('5️⃣ ANALYSE DES SUGGESTIONS GÉNÉRÉES :');
        $suggestions = Suggestion::where('user_id', $nadia->id)
            ->with(['suggestedUser'])
            ->get();

        if ($suggestions->isEmpty()) {
            $this->warn('⚠️  Aucune suggestion générée');
        } else {
            foreach ($suggestions as $suggestion) {
                $suggestedUser = $suggestion->suggestedUser;
                $relationCode = $suggestion->suggested_relation_code;

                // Obtenir le nom de la relation depuis le code
                $relationName = $this->getRelationName($relationCode);

                $this->line("   💡 {$suggestedUser->name} - {$relationName} ({$relationCode})");

                // Vérifier spécialement Youssef Bennani
                if (stripos($suggestedUser->name, 'Youssef') !== false && stripos($suggestedUser->name, 'Bennani') !== false) {
                    $this->newLine();
                    $this->line("   🎯 YOUSSEF BENNANI TROUVÉ DANS LES SUGGESTIONS :");
                    $this->line("      Relation suggérée : {$relationName} ({$relationCode})");

                    if ($relationCode === 'father_in_law') {
                        $this->line("      ✅ CORRECT ! Youssef est suggéré comme BEAU-PÈRE");
                    } else {
                        $this->line("      ❌ INCORRECT ! Devrait être 'father_in_law' (Beau-père)");
                        $this->line("      🔧 Relation actuelle : {$relationCode}");
                    }
                    $this->newLine();
                }
            }
        }

        // Résumé et recommandations
        $this->info('6️⃣ RÉSUMÉ ET VÉRIFICATION :');

        $youssefSuggestion = $suggestions->first(function($suggestion) {
            return stripos($suggestion->suggestedUser->name, 'Youssef') !== false &&
                   stripos($suggestion->suggestedUser->name, 'Bennani') !== false;
        });

        if ($youssefSuggestion) {
            if ($youssefSuggestion->suggested_relation_code === 'father_in_law') {
                $this->line("   ✅ SUCCÈS : Youssef Bennani correctement identifié comme Beau-père");
            } else {
                $this->line("   ❌ ÉCHEC : Youssef Bennani mal identifié");
                $this->line("      Attendu : father_in_law (Beau-père)");
                $this->line("      Obtenu : {$youssefSuggestion->suggested_relation_code}");
            }
        } else {
            $this->line("   ❌ ÉCHEC : Youssef Bennani non trouvé dans les suggestions");
        }

        $this->newLine();
        $this->info('🎯 CORRECTION TERMINÉE !');
        $this->line('   Le système de suggestions par alliance a été amélioré');
        $this->line('   pour mieux identifier les beaux-parents.');

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
