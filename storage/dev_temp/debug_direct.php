<?php

/**
 * DEBUG DIRECT - EXÉCUTION AUTOMATIQUE
 */

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Démarrer Laravel sans la console
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "🔍 DEBUG DIRECT: AMINA → FATIMA\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // 1. Charger les utilisateurs
    echo "1. 📋 CHARGEMENT DES UTILISATEURS:\n";
    $amina = App\Models\User::where('name', 'like', '%Amina%')->first();
    $fatima = App\Models\User::where('name', 'like', '%Fatima%')->first();
    $ahmed = App\Models\User::where('name', 'like', '%Ahmed%')->first();
    $mohamed = App\Models\User::where('name', 'like', '%Mohammed%')->first();

    if (!$amina || !$fatima || !$ahmed) {
        echo "❌ Utilisateurs manquants\n";
        echo "   Amina: " . ($amina ? "✅" : "❌") . "\n";
        echo "   Fatima: " . ($fatima ? "✅" : "❌") . "\n";
        echo "   Ahmed: " . ($ahmed ? "✅" : "❌") . "\n";
        exit(1);
    }

    echo "✅ Utilisateurs trouvés:\n";
    echo "   Amina: {$amina->name} (ID: {$amina->id})\n";
    echo "   Fatima: {$fatima->name} (ID: {$fatima->id})\n";
    echo "   Ahmed: {$ahmed->name} (ID: {$ahmed->id})\n\n";

    // 2. Analyser TOUTES les relations existantes
    echo "2. 🔗 TOUTES LES RELATIONS EXISTANTES:\n";
    $allRelations = App\Models\FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
    foreach ($allRelations as $rel) {
        echo "   {$rel->user->name} → {$rel->relatedUser->name} : {$rel->relationshipType->name} ({$rel->relationshipType->code})\n";
    }
    echo "\n";

    // 3. Relations spécifiques d'Amina
    echo "3. 🎯 RELATIONS D'AMINA:\n";
    $aminaRelations = App\Models\FamilyRelationship::where('user_id', $amina->id)
        ->orWhere('related_user_id', $amina->id)
        ->with(['user', 'relatedUser', 'relationshipType'])->get();
    foreach ($aminaRelations as $rel) {
        if ($rel->user_id === $amina->id) {
            echo "   Amina → {$rel->relatedUser->name} : {$rel->relationshipType->code}\n";
        } else {
            echo "   {$rel->user->name} → Amina : {$rel->relationshipType->code}\n";
        }
    }
    echo "\n";

    // 4. Relations spécifiques d'Ahmed
    echo "4. 🎯 RELATIONS D'AHMED:\n";
    $ahmedRelations = App\Models\FamilyRelationship::where('user_id', $ahmed->id)
        ->orWhere('related_user_id', $ahmed->id)
        ->with(['user', 'relatedUser', 'relationshipType'])->get();
    foreach ($ahmedRelations as $rel) {
        if ($rel->user_id === $ahmed->id) {
            echo "   Ahmed → {$rel->relatedUser->name} : {$rel->relationshipType->code}\n";
        } else {
            echo "   {$rel->user->name} → Ahmed : {$rel->relationshipType->code}\n";
        }
    }
    echo "\n";

    // 5. Vérifier la relation Ahmed ↔ Fatima
    echo "5. 🔍 RELATION AHMED ↔ FATIMA:\n";
    $ahmedFatimaRelation = App\Models\FamilyRelationship::where(function($query) use ($ahmed, $fatima) {
        $query->where('user_id', $ahmed->id)->where('related_user_id', $fatima->id);
    })->orWhere(function($query) use ($ahmed, $fatima) {
        $query->where('user_id', $fatima->id)->where('related_user_id', $ahmed->id);
    })->with('relationshipType')->first();
    
    if ($ahmedFatimaRelation) {
        echo "   ✅ Relation trouvée: {$ahmedFatimaRelation->user->name} → {$ahmedFatimaRelation->relatedUser->name} : {$ahmedFatimaRelation->relationshipType->code}\n";
    } else {
        echo "   ❌ AUCUNE RELATION AHMED ↔ FATIMA TROUVÉE!\n";
    }
    echo "\n";

    // 6. Vérifier la relation Amina ↔ Ahmed
    echo "6. 🔍 RELATION AMINA ↔ AHMED:\n";
    $aminaAhmedRelation = App\Models\FamilyRelationship::where(function($query) use ($amina, $ahmed) {
        $query->where('user_id', $amina->id)->where('related_user_id', $ahmed->id);
    })->orWhere(function($query) use ($amina, $ahmed) {
        $query->where('user_id', $ahmed->id)->where('related_user_id', $amina->id);
    })->with('relationshipType')->first();
    
    if ($aminaAhmedRelation) {
        echo "   ✅ Relation trouvée: {$aminaAhmedRelation->user->name} → {$aminaAhmedRelation->relatedUser->name} : {$aminaAhmedRelation->relationshipType->code}\n";
    } else {
        echo "   ❌ AUCUNE RELATION AMINA ↔ AHMED TROUVÉE!\n";
    }
    echo "\n";

    // 7. Test de génération de suggestions avec debug
    echo "7. 🧪 GÉNÉRATION DE SUGGESTIONS POUR AMINA:\n";
    App\Models\Suggestion::where('user_id', $amina->id)->delete();
    $suggestionService = app(App\Services\SuggestionService::class);
    
    echo "   Génération en cours...\n";
    $suggestions = $suggestionService->generateSuggestions($amina);
    echo "   Terminé!\n\n";

    // 8. Analyser les résultats
    echo "8. 💡 RÉSULTATS DES SUGGESTIONS:\n";
    foreach ($suggestions as $suggestion) {
        echo "   - {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code}\n";
        echo "     Raison: {$suggestion->reason}\n";
        if ($suggestion->suggested_user_id === $fatima->id) {
            echo "     🎯 FATIMA TROUVÉE: {$suggestion->suggested_relation_code}\n";
            if ($suggestion->suggested_relation_code === 'mother') {
                echo "     ✅ CORRECT!\n";
            } else {
                echo "     ❌ INCORRECT! Devrait être 'mother'\n";
            }
        }
    }
    echo "\n";

    // 9. Analyse de la logique attendue
    echo "9. 🧠 LOGIQUE ATTENDUE:\n";
    echo "   1. Amina → Ahmed : daughter (fille)\n";
    echo "   2. Ahmed → Fatima : husband (mari)\n";
    echo "   3. DÉDUCTION: Amina (enfant) + Fatima (conjoint d'Ahmed) = Fatima est mère\n";
    echo "   4. CAS 1: enfant + conjoint → parent\n";
    echo "   5. RÉSULTAT ATTENDU: mother\n\n";

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo str_repeat("=", 60) . "\n";
echo "Debug terminé.\n";
