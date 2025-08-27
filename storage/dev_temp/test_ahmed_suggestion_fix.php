<?php

/**
 * Script pour tester la correction de la suggestion Ahmed → Mohamed
 * Le problème: Ahmed suggéré comme "son_in_law" au lieu de "father"
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
    echo "🔧 Test de la correction Ahmed → Mohamed...\n\n";

    // 1. Trouver les utilisateurs
    $ahmed = User::where('name', 'like', '%Ahmed%')->first();
    $fatima = User::where('name', 'like', '%Fatima%')->first();
    $mohamed = User::where('name', 'like', '%Mohamed%')->first();

    if (!$ahmed || !$fatima || !$mohamed) {
        echo "❌ Utilisateurs non trouvés\n";
        echo "Ahmed: " . ($ahmed ? "✅ {$ahmed->name}" : "❌ Non trouvé") . "\n";
        echo "Fatima: " . ($fatima ? "✅ {$fatima->name}" : "❌ Non trouvé") . "\n";
        echo "Mohamed: " . ($mohamed ? "✅ {$mohamed->name}" : "❌ Non trouvé") . "\n";
        exit(1);
    }

    echo "👥 Utilisateurs trouvés:\n";
    echo "   - Ahmed: {$ahmed->name} (ID: {$ahmed->id})\n";
    echo "   - Fatima: {$fatima->name} (ID: {$fatima->id})\n";
    echo "   - Mohamed: {$mohamed->name} (ID: {$mohamed->id})\n\n";

    // 2. Vérifier les relations existantes
    echo "🔗 Relations existantes:\n";
    $relations = FamilyRelationship::whereIn('user_id', [$ahmed->id, $fatima->id, $mohamed->id])
        ->orWhereIn('related_user_id', [$ahmed->id, $fatima->id, $mohamed->id])
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

    foreach ($relations as $relation) {
        echo "   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr} ({$relation->relationshipType->name})\n";
    }
    echo "\n";

    // 3. Analyser la situation
    echo "🧠 Analyse de la situation:\n";
    
    // Relation Ahmed ↔ Fatima
    $ahmedFatima = FamilyRelationship::where(function($query) use ($ahmed, $fatima) {
        $query->where('user_id', $ahmed->id)->where('related_user_id', $fatima->id)
              ->orWhere('user_id', $fatima->id)->where('related_user_id', $ahmed->id);
    })->with('relationshipType')->first();

    if ($ahmedFatima) {
        if ($ahmedFatima->user_id === $ahmed->id) {
            echo "   - Ahmed → Fatima : {$ahmedFatima->relationshipType->name}\n";
        } else {
            echo "   - Fatima → Ahmed : {$ahmedFatima->relationshipType->name}\n";
        }
    }

    // Relation Fatima → Mohamed
    $fatimaMohamed = FamilyRelationship::where('user_id', $fatima->id)
        ->where('related_user_id', $mohamed->id)
        ->with('relationshipType')
        ->first();

    if ($fatimaMohamed) {
        echo "   - Fatima → Mohamed : {$fatimaMohamed->relationshipType->name}\n";
    }

    // Relation Mohamed → Fatima (inverse)
    $mohamedFatima = FamilyRelationship::where('user_id', $mohamed->id)
        ->where('related_user_id', $fatima->id)
        ->with('relationshipType')
        ->first();

    if ($mohamedFatima) {
        echo "   - Mohamed → Fatima : {$mohamedFatima->relationshipType->name}\n";
    }

    echo "\n";

    // 4. Logique attendue
    echo "🎯 Logique attendue:\n";
    echo "   - Mohamed est fils de Fatima\n";
    echo "   - Ahmed est mari de Fatima\n";
    echo "   - DONC: Ahmed devrait être père de Mohamed\n";
    echo "   - PAS: Ahmed comme gendre de Mohamed!\n\n";

    // 5. Supprimer les anciennes suggestions incorrectes
    echo "🗑️ Suppression des anciennes suggestions...\n";
    Suggestion::where('user_id', $mohamed->id)->delete();
    echo "✅ Suggestions supprimées\n\n";

    // 6. Générer de nouvelles suggestions
    echo "🧪 Génération de nouvelles suggestions pour Mohamed...\n";
    $suggestionService = app(SuggestionService::class);
    
    try {
        $newSuggestions = $suggestionService->generateSuggestions($mohamed);
        echo "✅ {$newSuggestions->count()} nouvelles suggestions générées\n\n";
        
        echo "💡 Suggestions pour Mohamed:\n";
        foreach ($newSuggestions as $suggestion) {
            $relationName = RelationshipType::where('name', $suggestion->suggested_relation_code)->first();
            $displayName = $relationName ? $relationName->display_name_fr : $suggestion->suggested_relation_code;
            echo "   - {$suggestion->suggestedUser->name} : {$displayName} ({$suggestion->suggested_relation_code})\n";
            echo "     Raison: {$suggestion->reason}\n";
            
            // Vérifier si Ahmed est suggéré correctement
            if ($suggestion->suggested_user_id === $ahmed->id) {
                if ($suggestion->suggested_relation_code === 'father') {
                    echo "     ✅ CORRECT: Ahmed suggéré comme père!\n";
                } else {
                    echo "     ❌ INCORRECT: Ahmed suggéré comme {$suggestion->suggested_relation_code} au lieu de père!\n";
                }
            }
            echo "\n";
        }
    } catch (\Exception $e) {
        echo "❌ Erreur lors de la génération: {$e->getMessage()}\n";
        echo "📋 Trace: " . substr($e->getTraceAsString(), 0, 500) . "...\n";
    }

    echo "\n🎉 Test terminé!\n";
    echo "✅ Vérifiez que Ahmed est maintenant suggéré comme 'père' et non 'gendre'\n";

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
