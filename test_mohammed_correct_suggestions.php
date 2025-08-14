<?php

/**
 * Script pour tester les suggestions correctes de Mohammed
 * Logique attendue:
 * - Ahmed = père de Mohammed, Amina ET Youssef + mari de Fatima
 * - Fatima = épouse d'Ahmed = mère de Mohammed
 * - Mohammed, Amina, Youssef = tous enfants d'Ahmed = frères/sœurs
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
    echo "🧪 Test des suggestions correctes pour Mohammed...\n\n";

    // 1. Trouver tous les utilisateurs
    $ahmed = User::where('name', 'like', '%Ahmed%')->first();
    $fatima = User::where('name', 'like', '%Fatima%')->first();
    $mohammed = User::where('name', 'like', '%Mohammed%')->first();
    $amina = User::where('name', 'like', '%Amina%')->first();
    $youssef = User::where('name', 'like', '%Youssef%')->first();

    if (!$ahmed || !$fatima || !$mohammed || !$amina || !$youssef) {
        echo "❌ Utilisateurs non trouvés\n";
        exit(1);
    }

    echo "👥 Utilisateurs trouvés:\n";
    echo "   - Ahmed: {$ahmed->name} (ID: {$ahmed->id}) - PÈRE\n";
    echo "   - Fatima: {$fatima->name} (ID: {$fatima->id}) - MÈRE\n";
    echo "   - Mohammed: {$mohammed->name} (ID: {$mohammed->id}) - UTILISATEUR ACTUEL\n";
    echo "   - Amina: {$amina->name} (ID: {$amina->id}) - SŒUR\n";
    echo "   - Youssef: {$youssef->name} (ID: {$youssef->id}) - FRÈRE\n\n";

    // 2. Vérifier les relations existantes
    echo "🔗 Relations existantes:\n";
    $allRelations = FamilyRelationship::whereIn('user_id', [$ahmed->id, $fatima->id, $mohammed->id, $amina->id, $youssef->id])
        ->orWhereIn('related_user_id', [$ahmed->id, $fatima->id, $mohammed->id, $amina->id, $youssef->id])
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

    foreach ($allRelations as $relation) {
        echo "   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr} ({$relation->relationshipType->name})\n";
    }
    echo "\n";

    // 3. Analyser la logique attendue
    echo "🎯 Logique familiale attendue:\n";
    echo "   - Ahmed = père de Mohammed, Amina ET Youssef ✅\n";
    echo "   - Fatima = épouse d'Ahmed ET mère de Mohammed ✅\n";
    echo "   - DONC: Amina devrait être sœur de Mohammed ✅\n";
    echo "   - DONC: Youssef devrait être frère de Mohammed ✅\n";
    echo "   - DONC: Fatima devrait être mère de Mohammed ✅\n\n";

    // 4. Supprimer les anciennes suggestions de Mohammed
    echo "🗑️ Suppression des anciennes suggestions de Mohammed...\n";
    $deletedCount = Suggestion::where('user_id', $mohammed->id)->delete();
    echo "✅ {$deletedCount} suggestions supprimées\n\n";

    // 5. Régénérer les suggestions pour Mohammed
    echo "🧪 Régénération des suggestions pour Mohammed...\n";
    $suggestionService = app(SuggestionService::class);
    
    try {
        $newSuggestions = $suggestionService->generateSuggestions($mohammed);
        echo "✅ {$newSuggestions->count()} nouvelles suggestions générées\n\n";
        
        echo "💡 Suggestions pour Mohammed:\n";
        $correctSuggestions = 0;
        $incorrectSuggestions = 0;
        
        foreach ($newSuggestions as $suggestion) {
            $relationName = RelationshipType::where('name', $suggestion->suggested_relation_code)->first();
            $displayName = $relationName ? $relationName->display_name_fr : $suggestion->suggested_relation_code;
            echo "   - {$suggestion->suggestedUser->name} : {$displayName} ({$suggestion->suggested_relation_code})\n";
            echo "     Raison: {$suggestion->reason}\n";
            
            // Vérifier si les suggestions sont correctes
            if ($suggestion->suggested_user_id === $amina->id) {
                if ($suggestion->suggested_relation_code === 'sister') {
                    echo "     ✅ CORRECT: Amina suggérée comme sœur!\n";
                    $correctSuggestions++;
                } else {
                    echo "     ❌ INCORRECT: Amina suggérée comme {$suggestion->suggested_relation_code} au lieu de sister!\n";
                    $incorrectSuggestions++;
                }
            }
            
            if ($suggestion->suggested_user_id === $youssef->id) {
                if ($suggestion->suggested_relation_code === 'brother') {
                    echo "     ✅ CORRECT: Youssef suggéré comme frère!\n";
                    $correctSuggestions++;
                } else {
                    echo "     ❌ INCORRECT: Youssef suggéré comme {$suggestion->suggested_relation_code} au lieu de brother!\n";
                    $incorrectSuggestions++;
                }
            }
            
            if ($suggestion->suggested_user_id === $fatima->id) {
                if ($suggestion->suggested_relation_code === 'mother') {
                    echo "     ✅ CORRECT: Fatima suggérée comme mère!\n";
                    $correctSuggestions++;
                } else {
                    echo "     ❌ INCORRECT: Fatima suggérée comme {$suggestion->suggested_relation_code} au lieu de mother!\n";
                    $incorrectSuggestions++;
                }
            }
            echo "\n";
        }
        
        // 6. Résumé des résultats
        echo "📊 Résumé des suggestions:\n";
        echo "   ✅ Suggestions correctes: {$correctSuggestions}\n";
        echo "   ❌ Suggestions incorrectes: {$incorrectSuggestions}\n\n";
        
        if ($incorrectSuggestions === 0 && $correctSuggestions >= 3) {
            echo "🎉 SUCCÈS: Toutes les suggestions sont correctes!\n";
            echo "   ✅ Amina suggérée comme sœur\n";
            echo "   ✅ Youssef suggéré comme frère\n";
            echo "   ✅ Fatima suggérée comme mère\n";
        } else {
            echo "❌ ÉCHEC: Des suggestions sont encore incorrectes\n";
            
            // Vérifications spécifiques
            $aminaAsSister = Suggestion::where('user_id', $mohammed->id)
                ->where('suggested_user_id', $amina->id)
                ->where('suggested_relation_code', 'sister')
                ->exists();
                
            $youssefAsBrother = Suggestion::where('user_id', $mohammed->id)
                ->where('suggested_user_id', $youssef->id)
                ->where('suggested_relation_code', 'brother')
                ->exists();
                
            $fatimaAsMother = Suggestion::where('user_id', $mohammed->id)
                ->where('suggested_user_id', $fatima->id)
                ->where('suggested_relation_code', 'mother')
                ->exists();
                
            echo "\n🔍 Vérifications spécifiques:\n";
            echo "   - Amina comme sœur: " . ($aminaAsSister ? "✅ OUI" : "❌ NON") . "\n";
            echo "   - Youssef comme frère: " . ($youssefAsBrother ? "✅ OUI" : "❌ NON") . "\n";
            echo "   - Fatima comme mère: " . ($fatimaAsMother ? "✅ OUI" : "❌ NON") . "\n";
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
            'user_id' => $mohammed->id,
            'suggested_user_id' => $amina->id,
            'incorrect_codes' => ['granddaughter', 'daughter', 'niece'],
            'description' => 'Amina comme petite-fille/fille/nièce'
        ],
        [
            'user_id' => $mohammed->id,
            'suggested_user_id' => $youssef->id,
            'incorrect_codes' => ['grandson', 'son', 'nephew'],
            'description' => 'Youssef comme petit-fils/fils/neveu'
        ],
        [
            'user_id' => $mohammed->id,
            'suggested_user_id' => $fatima->id,
            'incorrect_codes' => ['sister', 'daughter', 'wife'],
            'description' => 'Fatima comme sœur/fille/épouse'
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
    echo "   - Ahmed (père) → Mohammed, Amina, Youssef (enfants)\n";
    echo "   - Fatima (mère) ↔ Ahmed (mari) → Mohammed (fils)\n";
    echo "   - Mohammed ↔ Amina ↔ Youssef (frères/sœurs)\n\n";
    
    echo "✅ Suggestions attendues pour Mohammed:\n";
    echo "   - Amina Tazi : Sister (sœur)\n";
    echo "   - Youssef Bennani : Brother (frère)\n";
    echo "   - Fatima Zahra : Mother (mère)\n";

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
