<?php

/**
 * Script pour tester les suggestions correctes d'Amina
 * Logique attendue:
 * - Ahmed = père d'Amina ET mari de Fatima
 * - Fatima = mère d'Amina (via mariage avec Ahmed) ET mère de Mohammed
 * - Mohammed = frère d'Amina (masculin)
 */

// Charger Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Models\Suggestion;
use App\Services\SuggestionService;
use Illuminate\Support\Facades\DB;

try {
    echo "🧪 Test des suggestions correctes pour Amina...\n\n";

    // 1. Trouver tous les utilisateurs
    $ahmed = User::where('name', 'like', '%Ahmed%')->first();
    $fatima = User::where('name', 'like', '%Fatima%')->first();
    $mohammed = User::where('name', 'like', '%Mohammed%')->first();
    $amina = User::where('name', 'like', '%Amina%')->first();

    if (!$ahmed || !$fatima || !$mohammed || !$amina) {
        echo "❌ Utilisateurs non trouvés\n";
        exit(1);
    }

    echo "👥 Utilisateurs trouvés:\n";
    echo "   - Ahmed: {$ahmed->name} (ID: {$ahmed->id}) - PÈRE\n";
    echo "   - Fatima: {$fatima->name} (ID: {$fatima->id}) - MÈRE\n";
    echo "   - Mohammed: {$mohammed->name} (ID: {$mohammed->id}) - FRÈRE\n";
    echo "   - Amina: {$amina->name} (ID: {$amina->id}) - UTILISATEUR ACTUEL\n\n";

    // 2. Vérifier les relations existantes
    echo "🔗 Relations existantes:\n";
    $allRelations = FamilyRelationship::whereIn('user_id', [$ahmed->id, $fatima->id, $mohammed->id, $amina->id])
        ->orWhereIn('related_user_id', [$ahmed->id, $fatima->id, $mohammed->id, $amina->id])
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

    foreach ($allRelations as $relation) {
        echo "   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr} ({$relation->relationshipType->name})\n";
    }
    echo "\n";

    // 3. Analyser la logique attendue
    echo "🎯 Logique familiale attendue:\n";
    echo "   - Ahmed = père d'Amina ET mari de Fatima ✅\n";
    echo "   - Fatima = épouse d'Ahmed ET mère de Mohammed ✅\n";
    echo "   - DONC: Fatima devrait être mère d'Amina ✅\n";
    echo "   - DONC: Mohammed devrait être frère d'Amina (masculin) ✅\n\n";

    // 4. Supprimer les anciennes suggestions d'Amina
    echo "🗑️ Suppression des anciennes suggestions d'Amina...\n";
    $deletedCount = Suggestion::where('user_id', $amina->id)->delete();
    echo "✅ {$deletedCount} suggestions supprimées\n\n";

    // 5. Régénérer les suggestions pour Amina
    echo "🧪 Régénération des suggestions pour Amina...\n";
    $suggestionService = app(SuggestionService::class);
    
    try {
        $newSuggestions = $suggestionService->generateSuggestions($amina);
        echo "✅ {$newSuggestions->count()} nouvelles suggestions générées\n\n";
        
        echo "💡 Suggestions pour Amina:\n";
        $correctSuggestions = 0;
        $incorrectSuggestions = 0;
        
        foreach ($newSuggestions as $suggestion) {
            $relationName = RelationshipType::where('name', $suggestion->suggested_relation_code)->first();
            $displayName = $relationName ? $relationName->display_name_fr : $suggestion->suggested_relation_code;
            echo "   - {$suggestion->suggestedUser->name} : {$displayName} ({$suggestion->suggested_relation_code})\n";
            echo "     Raison: {$suggestion->reason}\n";
            
            // Vérifier si les suggestions sont correctes
            if ($suggestion->suggested_user_id === $fatima->id) {
                if ($suggestion->suggested_relation_code === 'mother') {
                    echo "     ✅ CORRECT: Fatima suggérée comme mère!\n";
                    $correctSuggestions++;
                } else {
                    echo "     ❌ INCORRECT: Fatima suggérée comme {$suggestion->suggested_relation_code} au lieu de mother!\n";
                    $incorrectSuggestions++;
                }
            }
            
            if ($suggestion->suggested_user_id === $mohammed->id) {
                if ($suggestion->suggested_relation_code === 'brother') {
                    echo "     ✅ CORRECT: Mohammed suggéré comme frère (masculin)!\n";
                    $correctSuggestions++;
                } else {
                    echo "     ❌ INCORRECT: Mohammed suggéré comme {$suggestion->suggested_relation_code} au lieu de brother!\n";
                    $incorrectSuggestions++;
                }
            }
            echo "\n";
        }
        
        // 6. Résumé des résultats
        echo "📊 Résumé des suggestions:\n";
        echo "   ✅ Suggestions correctes: {$correctSuggestions}\n";
        echo "   ❌ Suggestions incorrectes: {$incorrectSuggestions}\n\n";
        
        if ($incorrectSuggestions === 0 && $correctSuggestions >= 2) {
            echo "🎉 SUCCÈS: Toutes les suggestions sont correctes!\n";
            echo "   ✅ Fatima suggérée comme mère\n";
            echo "   ✅ Mohammed suggéré comme frère\n";
        } else {
            echo "❌ ÉCHEC: Des suggestions sont encore incorrectes\n";
            
            // Vérifications spécifiques
            $fatimaAsMother = Suggestion::where('user_id', $amina->id)
                ->where('suggested_user_id', $fatima->id)
                ->where('suggested_relation_code', 'mother')
                ->exists();
                
            $mohammedAsBrother = Suggestion::where('user_id', $amina->id)
                ->where('suggested_user_id', $mohammed->id)
                ->where('suggested_relation_code', 'brother')
                ->exists();
                
            echo "\n🔍 Vérifications spécifiques:\n";
            echo "   - Fatima comme mère: " . ($fatimaAsMother ? "✅ OUI" : "❌ NON") . "\n";
            echo "   - Mohammed comme frère: " . ($mohammedAsBrother ? "✅ OUI" : "❌ NON") . "\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Erreur lors de la génération: {$e->getMessage()}\n";
        echo "📋 Trace: " . substr($e->getTraceAsString(), 0, 500) . "...\n";
    }

    echo "\n";

    // 7. Vérifier qu'il n'y a plus de suggestions incorrectes
    echo "🚫 Vérification des suggestions incorrectes:\n";

    $incorrectChecks = [
        [
            'user_id' => $amina->id,
            'suggested_user_id' => $fatima->id,
            'incorrect_codes' => ['sister', 'daughter_in_law', 'aunt'],
            'description' => 'Fatima comme sœur/belle-fille/tante'
        ],
        [
            'user_id' => $amina->id,
            'suggested_user_id' => $mohammed->id,
            'incorrect_codes' => ['grandson', 'husband', 'uncle'],
            'description' => 'Mohammed comme petit-fils/mari/oncle'
        ]
    ];

    $foundIncorrect = false;
    foreach ($incorrectChecks as $check) {
        $count = Suggestion::where('user_id', $check['user_id'])
            ->where('suggested_user_id', $check['suggested_user_id'])
            ->whereIn('suggested_relation_code', $check['incorrect_codes'])
            ->count();

        if ($count > 0) {
            echo "❌ {$count} suggestion(s) incorrecte(s): {$check['description']}\n";
            $foundIncorrect = true;
        }
    }

    if (!$foundIncorrect) {
        echo "✅ Aucune suggestion incorrecte trouvée\n";
    }

    echo "\n🎯 Conclusion:\n";
    echo "La logique familiale devrait maintenant être:\n";
    echo "   - Ahmed (père) ↔ Fatima (mère) = couple marié\n";
    echo "   - Amina (fille d'Ahmed) ← Fatima (mère via mariage)\n";
    echo "   - Mohammed (fils de Fatima) ↔ Amina (frère/sœur)\n\n";
    
    echo "✅ Suggestions attendues pour Amina:\n";
    echo "   - Fatima Zahra : Mother (mère)\n";
    echo "   - Mohammed Alami : Brother (frère)\n";

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
