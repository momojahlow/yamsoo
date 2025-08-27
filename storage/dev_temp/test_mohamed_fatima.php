<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Services\SuggestionService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 TEST SPÉCIFIQUE MOHAMED → FATIMA\n";
echo str_repeat("=", 50) . "\n";

try {
    // Charger les utilisateurs
    $mohamed = User::where('name', 'like', '%Mohammed%')->first();
    $fatima = User::where('name', 'like', '%Fatima%')->first();
    $ahmed = User::where('name', 'like', '%Ahmed%')->first();

    if (!$mohamed || !$fatima || !$ahmed) {
        echo "❌ Utilisateurs non trouvés:\n";
        echo "   Mohamed: " . ($mohamed ? "✅" : "❌") . "\n";
        echo "   Fatima: " . ($fatima ? "✅" : "❌") . "\n";
        echo "   Ahmed: " . ($ahmed ? "✅" : "❌") . "\n";
        exit(1);
    }

    echo "✅ Utilisateurs trouvés:\n";
    echo "   Mohamed: {$mohamed->name} (ID: {$mohamed->id})\n";
    echo "   Fatima: {$fatima->name} (ID: {$fatima->id})\n";
    echo "   Ahmed: {$ahmed->name} (ID: {$ahmed->id})\n\n";

    // Vérifier les relations existantes
    echo "🔗 Relations existantes:\n";
    
    $mohamedRelations = \App\Models\FamilyRelationship::where('user_id', $mohamed->id)
        ->orWhere('related_user_id', $mohamed->id)
        ->with(['user', 'relatedUser', 'relationshipType'])->get();
    
    echo "   Mohamed:\n";
    foreach ($mohamedRelations as $rel) {
        echo "     - {$rel->user->name} → {$rel->relatedUser->name} : {$rel->relationshipType->name}\n";
    }

    $ahmedRelations = \App\Models\FamilyRelationship::where('user_id', $ahmed->id)
        ->orWhere('related_user_id', $ahmed->id)
        ->with(['user', 'relatedUser', 'relationshipType'])->get();
    
    echo "   Ahmed:\n";
    foreach ($ahmedRelations as $rel) {
        echo "     - {$rel->user->name} → {$rel->relatedUser->name} : {$rel->relationshipType->name}\n";
    }

    // Supprimer les anciennes suggestions
    \App\Models\Suggestion::where('user_id', $mohamed->id)->delete();

    echo "\n🧪 GÉNÉRATION DES SUGGESTIONS POUR MOHAMED:\n";
    
    // Générer les suggestions avec debug
    $suggestionService = app(SuggestionService::class);
    $suggestions = $suggestionService->generateSuggestions($mohamed);

    echo "\n💡 Résultats:\n";
    foreach ($suggestions as $suggestion) {
        $suggestedUser = $suggestion->suggestedUser;
        echo "   - {$suggestedUser->name} : {$suggestion->suggested_relation_code}\n";
        echo "     Raison: {$suggestion->reason}\n";
        
        if ($suggestion->suggested_user_id === $fatima->id) {
            echo "     🎯 FATIMA TROUVÉE: {$suggestion->suggested_relation_code}\n";
            if ($suggestion->suggested_relation_code === 'mother') {
                echo "     ✅ CORRECT!\n";
            } else {
                echo "     ❌ INCORRECT! Devrait être 'mother', mais c'est '{$suggestion->suggested_relation_code}'\n";
            }
        }
    }

    echo "\n🧠 ANALYSE ATTENDUE:\n";
    echo "   Mohamed → Ahmed : son (fils)\n";
    echo "   Ahmed → Fatima : husband (mari)\n";
    echo "   DÉDUCTION: Mohamed (enfant) + Fatima (conjoint d'Ahmed) = Fatima est mère\n";
    echo "   CAS 1: enfant + conjoint → parent\n";
    echo "   RÉSULTAT ATTENDU: mother\n";

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test terminé.\n";
