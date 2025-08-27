<?php

/**
 * TEST SIMPLE POUR MOHAMMED - DIAGNOSTIC DES SUGGESTIONS INCORRECTES
 * 
 * Problème identifié :
 * - Amina Tazi : Granddaughter ❌ → devrait être Sister ✅
 * - Youssef Bennani : Grandson ❌ → devrait être Brother ✅  
 * - Fatima Zahra : Sœur ❌ → devrait être Mother ✅
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

try {
    echo "🔍 DIAGNOSTIC SIMPLE - SUGGESTIONS DE MOHAMMED\n";
    echo str_repeat("=", 60) . "\n\n";

    // 1. Charger les utilisateurs
    $mohammed = User::where('name', 'like', '%Mohammed%')->first();
    $amina = User::where('name', 'like', '%Amina%')->first();
    $youssef = User::where('name', 'like', '%Youssef%')->first();
    $fatima = User::where('name', 'like', '%Fatima%')->first();
    $ahmed = User::where('name', 'like', '%Ahmed%')->first();

    if (!$mohammed || !$amina || !$youssef || !$fatima || !$ahmed) {
        throw new Exception("Utilisateurs manquants");
    }

    echo "👥 Utilisateurs chargés :\n";
    echo "  - Mohammed: {$mohammed->name} (ID: {$mohammed->id})\n";
    echo "  - Amina: {$amina->name} (ID: {$amina->id})\n";
    echo "  - Youssef: {$youssef->name} (ID: {$youssef->id})\n";
    echo "  - Fatima: {$fatima->name} (ID: {$fatima->id})\n";
    echo "  - Ahmed: {$ahmed->name} (ID: {$ahmed->id})\n\n";

    // 2. Analyser les relations existantes
    echo "🔗 Relations existantes dans la base :\n";
    $allRelations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
    
    $relevantRelations = $allRelations->filter(function ($relation) use ($mohammed, $amina, $youssef, $fatima, $ahmed) {
        $userIds = [$mohammed->id, $amina->id, $youssef->id, $fatima->id, $ahmed->id];
        return in_array($relation->user_id, $userIds) || in_array($relation->related_user_id, $userIds);
    });

    foreach ($relevantRelations as $relation) {
        echo "  - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr} ({$relation->relationshipType->name})\n";
    }

    // 3. Analyser la logique attendue
    echo "\n🎯 LOGIQUE FAMILIALE ATTENDUE :\n";
    echo "  Structure familiale :\n";
    echo "    Ahmed (père) ↔ Fatima (mère) = couple marié\n";
    echo "    ├── Mohammed (fils)\n";
    echo "    ├── Amina (fille)\n";
    echo "    └── Youssef (fils)\n\n";

    echo "  Suggestions CORRECTES attendues pour Mohammed :\n";
    echo "    ✅ Amina → Sister (sœur) - même père Ahmed\n";
    echo "    ✅ Youssef → Brother (frère) - même père Ahmed\n";
    echo "    ✅ Fatima → Mother (mère) - épouse du père Ahmed\n\n";

    // 4. Tester la logique actuelle
    echo "🧪 TEST DE LA LOGIQUE ACTUELLE :\n";
    
    // Supprimer les anciennes suggestions
    Suggestion::where('user_id', $mohammed->id)->delete();
    echo "  🗑️ Anciennes suggestions supprimées\n";

    // Générer de nouvelles suggestions
    $suggestionService = app(SuggestionService::class);
    $suggestions = $suggestionService->generateSuggestions($mohammed);
    echo "  ✅ Nouvelles suggestions générées : " . $suggestions->count() . "\n\n";

    // 5. Analyser chaque suggestion
    echo "💡 SUGGESTIONS ACTUELLES POUR MOHAMMED :\n";
    
    $testCases = [
        $amina->id => ['name' => 'Amina', 'expected' => 'sister', 'reason' => 'même père Ahmed'],
        $youssef->id => ['name' => 'Youssef', 'expected' => 'brother', 'reason' => 'même père Ahmed'],
        $fatima->id => ['name' => 'Fatima', 'expected' => 'mother', 'reason' => 'épouse du père Ahmed']
    ];

    $correctCount = 0;
    $incorrectCount = 0;

    foreach ($suggestions as $suggestion) {
        $suggestedUserId = $suggestion->suggested_user_id;
        $suggestedCode = $suggestion->suggested_relation_code;
        $suggestedUser = $suggestion->suggestedUser;
        
        $relationshipType = RelationshipType::where('name', $suggestedCode)->first();
        $displayName = $relationshipType ? $relationshipType->display_name_fr : $suggestedCode;
        
        echo "  🔸 {$suggestedUser->name} : {$displayName} ({$suggestedCode})\n";
        echo "     Raison: {$suggestion->reason}\n";
        
        if (isset($testCases[$suggestedUserId])) {
            $testCase = $testCases[$suggestedUserId];
            $expected = $testCase['expected'];
            $expectedReason = $testCase['reason'];
            
            if ($suggestedCode === $expected) {
                echo "     ✅ CORRECT ! Attendu: {$expected}\n";
                $correctCount++;
            } else {
                echo "     ❌ INCORRECT ! Attendu: {$expected} ({$expectedReason}), Obtenu: {$suggestedCode}\n";
                $incorrectCount++;
            }
        } else {
            echo "     ℹ️ Suggestion non testée\n";
        }
        echo "\n";
    }

    // 6. Diagnostic détaillé des problèmes
    echo "🔍 DIAGNOSTIC DÉTAILLÉ DES PROBLÈMES :\n";
    
    foreach ($testCases as $userId => $testCase) {
        $suggestion = $suggestions->first(function ($s) use ($userId) {
            return $s->suggested_user_id === $userId;
        });
        
        if (!$suggestion) {
            echo "  ❌ {$testCase['name']} : AUCUNE SUGGESTION GÉNÉRÉE\n";
            echo "     Problème: La logique ne détecte pas la relation\n";
            continue;
        }
        
        if ($suggestion->suggested_relation_code !== $testCase['expected']) {
            echo "  ❌ {$testCase['name']} : RELATION INCORRECTE\n";
            echo "     Obtenu: {$suggestion->suggested_relation_code}\n";
            echo "     Attendu: {$testCase['expected']}\n";
            echo "     Raison attendue: {$testCase['reason']}\n";
            echo "     Raison obtenue: {$suggestion->reason}\n";
            
            // Analyser pourquoi la logique échoue
            $this->analyzeLogicFailure($mohammed, $suggestion->suggestedUser, $testCase['expected']);
        }
    }

    // 7. Résumé
    echo "\n📊 RÉSUMÉ DU DIAGNOSTIC :\n";
    echo "  ✅ Suggestions correctes: {$correctCount}\n";
    echo "  ❌ Suggestions incorrectes: {$incorrectCount}\n";
    echo "  📈 Taux de réussite: " . round(($correctCount / ($correctCount + $incorrectCount)) * 100, 1) . "%\n\n";

    if ($incorrectCount > 0) {
        echo "🔧 ACTIONS NÉCESSAIRES :\n";
        echo "  1. Corriger la logique dans SuggestionService::deduceRelationship()\n";
        echo "  2. Ajouter/corriger les cas pour :\n";
        
        foreach ($testCases as $userId => $testCase) {
            $suggestion = $suggestions->first(function ($s) use ($userId) {
                return $s->suggested_user_id === $userId;
            });
            
            if (!$suggestion || $suggestion->suggested_relation_code !== $testCase['expected']) {
                echo "     - {$testCase['name']} → {$testCase['expected']} ({$testCase['reason']})\n";
            }
        }
        
        echo "  3. Retester avec ce script\n";
    } else {
        echo "🎉 SUCCÈS ! Toutes les suggestions sont correctes.\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

function analyzeLogicFailure($user, $suggestedUser, $expectedRelation)
{
    echo "     🔍 Analyse de l'échec logique :\n";
    
    // Chercher les connexions possibles
    $connections = FamilyRelationship::where(function ($query) use ($user) {
        $query->where('user_id', $user->id)->orWhere('related_user_id', $user->id);
    })->with(['user', 'relatedUser', 'relationshipType'])->get();
    
    echo "       Connexions de {$user->name} :\n";
    foreach ($connections as $connection) {
        $connector = $connection->user_id === $user->id ? $connection->relatedUser : $connection->user;
        $relationCode = $connection->user_id === $user->id ? $connection->relationshipType->name : 'inverse_' . $connection->relationshipType->name;
        echo "         → {$connector->name} : {$relationCode}\n";
        
        // Chercher les connexions du connecteur vers la personne suggérée
        $secondaryConnections = FamilyRelationship::where(function ($query) use ($connector, $suggestedUser) {
            $query->where('user_id', $connector->id)->where('related_user_id', $suggestedUser->id)
                  ->orWhere('user_id', $suggestedUser->id)->where('related_user_id', $connector->id);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();
        
        foreach ($secondaryConnections as $secConnection) {
            $secRelationCode = $secConnection->user_id === $connector->id ? $secConnection->relationshipType->name : 'inverse_' . $secConnection->relationshipType->name;
            echo "           └─ {$connector->name} → {$suggestedUser->name} : {$secRelationCode}\n";
            echo "           └─ Déduction attendue: {$user->name} → {$suggestedUser->name} = {$expectedRelation}\n";
        }
    }
}
