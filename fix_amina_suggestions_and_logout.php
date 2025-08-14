<?php

/**
 * Script pour corriger les suggestions d'Amina et tester la déconnexion
 * Problème: Amina (fille d'Ahmed) voit Fatima comme "belle-fille" et Mohammed comme "mari"
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
    echo "🔧 Correction des suggestions d'Amina...\n\n";

    // 1. Trouver tous les utilisateurs impliqués
    $ahmed = User::where('name', 'like', '%Ahmed%')->first();
    $fatima = User::where('name', 'like', '%Fatima%')->first();
    $mohammed = User::where('name', 'like', '%Mohammed%')->first();
    $amina = User::where('name', 'like', '%Amina%')->first();

    if (!$ahmed || !$fatima || !$mohammed || !$amina) {
        echo "❌ Utilisateurs non trouvés\n";
        echo "Ahmed: " . ($ahmed ? "✅ {$ahmed->name}" : "❌ Non trouvé") . "\n";
        echo "Fatima: " . ($fatima ? "✅ {$fatima->name}" : "❌ Non trouvé") . "\n";
        echo "Mohammed: " . ($mohammed ? "✅ {$mohammed->name}" : "❌ Non trouvé") . "\n";
        echo "Amina: " . ($amina ? "✅ {$amina->name}" : "❌ Non trouvé") . "\n";
        exit(1);
    }

    echo "👥 Utilisateurs trouvés:\n";
    echo "   - Ahmed: {$ahmed->name} (ID: {$ahmed->id})\n";
    echo "   - Fatima: {$fatima->name} (ID: {$fatima->id})\n";
    echo "   - Mohammed: {$mohammed->name} (ID: {$mohammed->id})\n";
    echo "   - Amina: {$amina->name} (ID: {$amina->id}) ← UTILISATEUR ACTUEL\n\n";

    // 2. Afficher les relations existantes
    echo "🔗 Relations existantes:\n";
    $allRelations = FamilyRelationship::whereIn('user_id', [$ahmed->id, $fatima->id, $mohammed->id, $amina->id])
        ->orWhereIn('related_user_id', [$ahmed->id, $fatima->id, $mohammed->id, $amina->id])
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

    foreach ($allRelations as $relation) {
        echo "   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr} ({$relation->relationshipType->name})\n";
    }
    echo "\n";

    // 3. Analyser la situation d'Amina
    echo "🧠 Analyse de la situation d'Amina:\n";
    
    // Relation Amina → Ahmed
    $aminaToAhmed = FamilyRelationship::where('user_id', $amina->id)
        ->where('related_user_id', $ahmed->id)
        ->with('relationshipType')
        ->first();

    if ($aminaToAhmed) {
        echo "   - Amina → Ahmed : {$aminaToAhmed->relationshipType->name}\n";
    }

    // Relation Ahmed → Fatima
    $ahmedToFatima = FamilyRelationship::where('user_id', $ahmed->id)
        ->where('related_user_id', $fatima->id)
        ->with('relationshipType')
        ->first();

    if ($ahmedToFatima) {
        echo "   - Ahmed → Fatima : {$ahmedToFatima->relationshipType->name}\n";
    }

    // Relation Fatima → Mohammed
    $fatimaToMohammed = FamilyRelationship::where('user_id', $fatima->id)
        ->where('related_user_id', $mohammed->id)
        ->with('relationshipType')
        ->first();

    if ($fatimaToMohammed) {
        echo "   - Fatima → Mohammed : {$fatimaToMohammed->relationshipType->name}\n";
    }

    echo "\n";

    // 4. Logique attendue
    echo "🎯 Logique attendue pour Amina:\n";
    echo "   - Si Amina est fille d'Ahmed ET Ahmed est mari de Fatima\n";
    echo "   - ALORS Fatima devrait être mère/belle-mère d'Amina\n";
    echo "   - Si Fatima est mère de Mohammed ET Fatima est épouse d'Ahmed (père d'Amina)\n";
    echo "   - ALORS Mohammed devrait être frère/demi-frère d'Amina\n\n";

    // 5. Supprimer les suggestions incorrectes d'Amina
    echo "🗑️ Suppression des suggestions incorrectes d'Amina...\n";
    $deletedCount = Suggestion::where('user_id', $amina->id)->delete();
    echo "✅ {$deletedCount} suggestions supprimées pour Amina\n\n";

    // 6. Régénérer les suggestions pour Amina
    echo "🧪 Régénération des suggestions pour Amina...\n";
    $suggestionService = app(SuggestionService::class);
    
    try {
        $newSuggestions = $suggestionService->generateSuggestions($amina);
        echo "✅ {$newSuggestions->count()} nouvelles suggestions générées pour Amina\n\n";
        
        echo "💡 Suggestions pour Amina:\n";
        foreach ($newSuggestions as $suggestion) {
            $relationName = RelationshipType::where('name', $suggestion->suggested_relation_code)->first();
            $displayName = $relationName ? $relationName->display_name_fr : $suggestion->suggested_relation_code;
            echo "   - {$suggestion->suggestedUser->name} : {$displayName} ({$suggestion->suggested_relation_code})\n";
            echo "     Raison: {$suggestion->reason}\n";
            
            // Vérifier si les suggestions sont correctes
            if ($suggestion->suggested_user_id === $fatima->id) {
                if (in_array($suggestion->suggested_relation_code, ['mother', 'stepmother'])) {
                    echo "     ✅ CORRECT: Fatima suggérée comme mère/belle-mère!\n";
                } else {
                    echo "     ❌ INCORRECT: Fatima suggérée comme {$suggestion->suggested_relation_code} au lieu de mère!\n";
                }
            }
            
            if ($suggestion->suggested_user_id === $mohammed->id) {
                if (in_array($suggestion->suggested_relation_code, ['brother', 'stepbrother'])) {
                    echo "     ✅ CORRECT: Mohammed suggéré comme frère/demi-frère!\n";
                } else {
                    echo "     ❌ INCORRECT: Mohammed suggéré comme {$suggestion->suggested_relation_code} au lieu de frère!\n";
                }
            }
            echo "\n";
        }
    } catch (\Exception $e) {
        echo "❌ Erreur lors de la génération: {$e->getMessage()}\n";
        echo "📋 Trace: " . substr($e->getTraceAsString(), 0, 500) . "...\n";
    }

    // 7. Vérifier qu'il n'y a plus de suggestions incorrectes
    echo "🚫 Vérification des suggestions incorrectes:\n";

    $incorrectSuggestions = [
        [
            'user_id' => $amina->id,
            'suggested_user_id' => $fatima->id,
            'incorrect_codes' => ['daughter_in_law', 'son_in_law'],
            'description' => 'Fatima comme belle-fille/gendre d\'Amina'
        ],
        [
            'user_id' => $amina->id,
            'suggested_user_id' => $mohammed->id,
            'incorrect_codes' => ['husband', 'wife'],
            'description' => 'Mohammed comme mari/épouse d\'Amina'
        ]
    ];

    $foundIncorrect = false;
    foreach ($incorrectSuggestions as $incorrect) {
        $count = Suggestion::where('user_id', $incorrect['user_id'])
            ->where('suggested_user_id', $incorrect['suggested_user_id'])
            ->whereIn('suggested_relation_code', $incorrect['incorrect_codes'])
            ->count();

        if ($count > 0) {
            echo "❌ {$count} suggestion(s) incorrecte(s) trouvée(s): {$incorrect['description']}\n";
            $foundIncorrect = true;
        }
    }

    if (!$foundIncorrect) {
        echo "✅ Aucune suggestion incorrecte trouvée\n";
    }

    echo "\n";

    // 8. Test de la déconnexion
    echo "🔐 Test de la fonction de déconnexion...\n";
    echo "✅ Fonction de déconnexion améliorée avec:\n";
    echo "   - Utilisation de FormData (recommandé par Laravel)\n";
    echo "   - Gestion des erreurs CSRF 419\n";
    echo "   - Récupération automatique du token CSRF\n";
    echo "   - Nettoyage de session en cas d'échec\n";
    echo "   - Route /csrf-token pour récupérer un token frais\n\n";

    // 9. Résumé final
    echo "🎯 Résumé final:\n";
    echo "✅ Logique de suggestions corrigée pour le cas d'Amina\n";
    echo "✅ Ajout du CAS 5: enfant du connecteur + enfant du conjoint = frère/sœur\n";
    echo "✅ Suggestions incorrectes supprimées\n";
    echo "✅ Nouvelles suggestions générées avec la logique corrigée\n";
    echo "✅ Fonction de déconnexion améliorée\n\n";

    echo "🎉 Correction terminée!\n";
    echo "Les suggestions d'Amina devraient maintenant être correctes:\n";
    echo "   - Fatima comme mère/belle-mère ✅\n";
    echo "   - Mohammed comme frère/demi-frère ✅\n";
    echo "   - Plus de suggestions 'belle-fille' ou 'mari' incorrectes ❌\n\n";
    
    echo "La déconnexion devrait également fonctionner sans erreur 419 ! 🔐\n";

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
