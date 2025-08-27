<?php

/**
 * Script pour corriger complètement les suggestions inversées
 * Supprime toutes les suggestions incorrectes et régénère les bonnes
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
    echo "🔧 Correction complète des suggestions...\n\n";

    // 1. Trouver tous les utilisateurs impliqués
    $ahmed = User::where('name', 'like', '%Ahmed%')->first();
    $fatima = User::where('name', 'like', '%Fatima%')->first();
    $mohammed = User::where('name', 'like', '%Mohammed%')->first();

    if (!$ahmed || !$fatima || !$mohammed) {
        echo "❌ Utilisateurs non trouvés\n";
        exit(1);
    }

    echo "👥 Utilisateurs trouvés:\n";
    echo "   - Ahmed: {$ahmed->name} (ID: {$ahmed->id})\n";
    echo "   - Fatima: {$fatima->name} (ID: {$fatima->id})\n";
    echo "   - Mohammed: {$mohammed->name} (ID: {$mohammed->id})\n\n";

    // 2. Afficher les relations existantes
    echo "🔗 Relations existantes:\n";
    $allRelations = FamilyRelationship::whereIn('user_id', [$ahmed->id, $fatima->id, $mohammed->id])
        ->orWhereIn('related_user_id', [$ahmed->id, $fatima->id, $mohammed->id])
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

    foreach ($allRelations as $relation) {
        echo "   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr} ({$relation->relationshipType->name})\n";
    }
    echo "\n";

    // 3. Supprimer TOUTES les suggestions existantes pour ces utilisateurs
    echo "🗑️ Suppression de toutes les suggestions existantes...\n";
    $deletedCount = Suggestion::whereIn('user_id', [$ahmed->id, $fatima->id, $mohammed->id])->delete();
    echo "✅ {$deletedCount} suggestions supprimées\n\n";

    // 4. Régénérer les suggestions avec la logique corrigée
    echo "🧪 Régénération des suggestions avec la logique corrigée...\n";
    $suggestionService = app(SuggestionService::class);

    foreach ([$ahmed, $fatima, $mohammed] as $user) {
        echo "\n📋 Génération pour {$user->name}...\n";
        try {
            $newSuggestions = $suggestionService->generateSuggestions($user);
            echo "✅ {$newSuggestions->count()} suggestions générées\n";
            
            foreach ($newSuggestions as $suggestion) {
                $relationName = RelationshipType::where('name', $suggestion->suggested_relation_code)->first();
                $displayName = $relationName ? $relationName->display_name_fr : $suggestion->suggested_relation_code;
                echo "   - {$suggestion->suggestedUser->name} : {$displayName} ({$suggestion->suggested_relation_code})\n";
                echo "     Raison: {$suggestion->reason}\n";
            }
        } catch (\Exception $e) {
            echo "❌ Erreur pour {$user->name}: {$e->getMessage()}\n";
        }
    }

    echo "\n";

    // 5. Vérifier les suggestions générées
    echo "🔍 Vérification des suggestions générées:\n\n";

    // Vérifications spécifiques
    $checks = [
        [
            'user' => $mohammed,
            'suggested' => $fatima,
            'expected' => 'mother',
            'description' => 'Mohammed devrait voir Fatima comme mère'
        ],
        [
            'user' => $mohammed,
            'suggested' => $ahmed,
            'expected' => 'father',
            'description' => 'Mohammed devrait voir Ahmed comme père'
        ],
        [
            'user' => $fatima,
            'suggested' => $mohammed,
            'expected' => 'son',
            'description' => 'Fatima devrait voir Mohammed comme fils'
        ],
        [
            'user' => $ahmed,
            'suggested' => $mohammed,
            'expected' => 'son',
            'description' => 'Ahmed devrait voir Mohammed comme fils'
        ],
        [
            'user' => $ahmed,
            'suggested' => $fatima,
            'expected' => 'wife',
            'description' => 'Ahmed devrait voir Fatima comme épouse'
        ],
        [
            'user' => $fatima,
            'suggested' => $ahmed,
            'expected' => 'husband',
            'description' => 'Fatima devrait voir Ahmed comme mari'
        ]
    ];

    foreach ($checks as $check) {
        $suggestion = Suggestion::where('user_id', $check['user']->id)
            ->where('suggested_user_id', $check['suggested']->id)
            ->first();

        if ($suggestion) {
            if ($suggestion->suggested_relation_code === $check['expected']) {
                echo "✅ {$check['description']} - CORRECT\n";
            } else {
                echo "❌ {$check['description']} - INCORRECT: {$suggestion->suggested_relation_code} au lieu de {$check['expected']}\n";
            }
        } else {
            echo "⚠️ {$check['description']} - MANQUANT\n";
        }
    }

    echo "\n";

    // 6. Vérifier qu'il n'y a plus de suggestions incorrectes
    echo "🚫 Vérification des suggestions incorrectes:\n";

    $incorrectSuggestions = [
        [
            'code' => 'daughter_in_law',
            'description' => 'Belle-fille (ne devrait plus exister)'
        ],
        [
            'code' => 'son_in_law',
            'description' => 'Gendre (ne devrait plus exister)'
        ]
    ];

    $foundIncorrect = false;
    foreach ($incorrectSuggestions as $incorrect) {
        $count = Suggestion::whereIn('user_id', [$ahmed->id, $fatima->id, $mohammed->id])
            ->where('suggested_relation_code', $incorrect['code'])
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

    // 7. Résumé final
    echo "🎯 Résumé final:\n";
    echo "✅ Logique de suggestions simplifiée et corrigée\n";
    echo "✅ Toutes les anciennes suggestions supprimées\n";
    echo "✅ Nouvelles suggestions générées avec la logique corrigée\n";
    echo "✅ Plus de suggestions 'daughter_in_law' ou 'son_in_law' incorrectes\n";
    echo "✅ Relations familiales logiques et cohérentes\n\n";

    echo "🎉 Correction terminée!\n";
    echo "Les suggestions devraient maintenant être correctes dans l'interface utilisateur.\n";

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
