<?php

/**
 * Script de debug pour identifier le problème des suggestions
 * Exécuter avec: php debug_suggestion_problem.php
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
    echo "🔍 Debug du problème de suggestions...\n\n";

    // 1. Vérifier les utilisateurs de test
    echo "👥 Utilisateurs de test:\n";
    $ahmed = User::where('name', 'like', '%Ahmed%')->first();
    $fatima = User::where('name', 'like', '%Fatima%')->first();
    $mohammed = User::where('name', 'like', '%Mohammed%')->first();

    if (!$ahmed || !$fatima || !$mohammed) {
        echo "❌ Utilisateurs de test non trouvés\n";
        echo "Ahmed: " . ($ahmed ? "✅ {$ahmed->name}" : "❌ Non trouvé") . "\n";
        echo "Fatima: " . ($fatima ? "✅ {$fatima->name}" : "❌ Non trouvé") . "\n";
        echo "Mohammed: " . ($mohammed ? "✅ {$mohammed->name}" : "❌ Non trouvé") . "\n";
        exit(1);
    }

    echo "✅ Ahmed: {$ahmed->name} (ID: {$ahmed->id})\n";
    echo "✅ Fatima: {$fatima->name} (ID: {$fatima->id})\n";
    echo "✅ Mohammed: {$mohammed->name} (ID: {$mohammed->id})\n\n";

    // 2. Vérifier les relations existantes
    echo "🔗 Relations existantes:\n";
    $relations = FamilyRelationship::whereIn('user_id', [$ahmed->id, $fatima->id, $mohammed->id])
        ->orWhereIn('related_user_id', [$ahmed->id, $fatima->id, $mohammed->id])
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

    foreach ($relations as $relation) {
        echo "   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr}\n";
    }
    echo "\n";

    // 3. Vérifier les suggestions existantes
    echo "💡 Suggestions existantes pour Mohammed:\n";
    $suggestions = Suggestion::where('user_id', $mohammed->id)
        ->with(['suggestedUser'])
        ->get();

    foreach ($suggestions as $suggestion) {
        echo "   - {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code} ({$suggestion->reason})\n";
    }

    if ($suggestions->isEmpty()) {
        echo "   ❌ Aucune suggestion trouvée\n";
    }
    echo "\n";

    // 4. Tester la génération de suggestions
    echo "🧪 Test de génération de suggestions pour Mohammed...\n";
    $suggestionService = app(SuggestionService::class);
    
    try {
        $newSuggestions = $suggestionService->generateSuggestions($mohammed);
        echo "✅ {$newSuggestions->count()} nouvelles suggestions générées\n";
        
        foreach ($newSuggestions as $suggestion) {
            echo "   - {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code} ({$suggestion->reason})\n";
        }
    } catch (\Exception $e) {
        echo "❌ Erreur lors de la génération: {$e->getMessage()}\n";
        echo "📋 Trace: {$e->getTraceAsString()}\n";
    }
    echo "\n";

    // 5. Vérifier les types de relations disponibles
    echo "📊 Types de relations disponibles:\n";
    $relationTypes = RelationshipType::orderBy('sort_order')->get();
    foreach ($relationTypes->take(10) as $type) {
        echo "   - {$type->name} ({$type->display_name_fr})\n";
    }
    echo "   ... et " . ($relationTypes->count() - 10) . " autres\n\n";

    // 6. Analyser la relation spécifique Mohammed → Fatima
    echo "🔍 Analyse de la relation Mohammed → Fatima:\n";
    $mohammedToFatima = FamilyRelationship::where('user_id', $mohammed->id)
        ->where('related_user_id', $fatima->id)
        ->with('relationshipType')
        ->first();

    if ($mohammedToFatima) {
        echo "✅ Relation directe trouvée: {$mohammedToFatima->relationshipType->display_name_fr}\n";
        echo "   Code: {$mohammedToFatima->relationshipType->name}\n";
    } else {
        echo "❌ Aucune relation directe trouvée\n";
        
        // Chercher la relation inverse
        $fatimaToMohammed = FamilyRelationship::where('user_id', $fatima->id)
            ->where('related_user_id', $mohammed->id)
            ->with('relationshipType')
            ->first();
            
        if ($fatimaToMohammed) {
            echo "✅ Relation inverse trouvée: Fatima → Mohammed = {$fatimaToMohammed->relationshipType->display_name_fr}\n";
            echo "   Code: {$fatimaToMohammed->relationshipType->name}\n";
            
            // Déterminer la relation correcte pour Mohammed → Fatima
            if ($fatimaToMohammed->relationshipType->name === 'mother') {
                echo "✅ Donc Mohammed → Fatima devrait être: fils\n";
            } elseif ($fatimaToMohammed->relationshipType->name === 'son') {
                echo "✅ Donc Mohammed → Fatima devrait être: mère\n";
            }
        }
    }

    echo "\n🎯 Conclusion:\n";
    echo "Si Fatima est la mère de Mohammed, alors:\n";
    echo "- ✅ Fatima → Mohammed = mère\n";
    echo "- ✅ Mohammed → Fatima = fils\n";
    echo "- ❌ PAS sœur comme suggéré dans l'image!\n";

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
