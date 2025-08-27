<?php

/**
 * Script pour déboguer les suggestions inversées
 * Problème: Fatima suggérée comme "Daughter_in_law" et Mohammed comme "Mari"
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
    echo "🔍 Debug des suggestions inversées...\n\n";

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

    // 2. Vérifier toutes les relations existantes
    echo "🔗 TOUTES les relations existantes:\n";
    $allRelations = FamilyRelationship::whereIn('user_id', [$ahmed->id, $fatima->id, $mohammed->id])
        ->orWhereIn('related_user_id', [$ahmed->id, $fatima->id, $mohammed->id])
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

    foreach ($allRelations as $relation) {
        echo "   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr} ({$relation->relationshipType->name})\n";
    }
    echo "\n";

    // 3. Vérifier les suggestions actuelles pour chaque utilisateur
    echo "💡 Suggestions actuelles:\n\n";

    foreach ([$ahmed, $fatima, $mohammed] as $user) {
        echo "📋 Suggestions pour {$user->name}:\n";
        $suggestions = Suggestion::where('user_id', $user->id)
            ->with(['suggestedUser'])
            ->get();

        if ($suggestions->isEmpty()) {
            echo "   ❌ Aucune suggestion\n";
        } else {
            foreach ($suggestions as $suggestion) {
                $relationName = RelationshipType::where('name', $suggestion->suggested_relation_code)->first();
                $displayName = $relationName ? $relationName->display_name_fr : $suggestion->suggested_relation_code;
                echo "   - {$suggestion->suggestedUser->name} : {$displayName} ({$suggestion->suggested_relation_code})\n";
                echo "     Raison: {$suggestion->reason}\n";
            }
        }
        echo "\n";
    }

    // 4. Analyser la logique pour chaque cas problématique
    echo "🧠 Analyse de la logique:\n\n";

    // Cas 1: Qui voit Fatima comme "Daughter_in_law" ?
    $fatimaAsBelleFilleFor = Suggestion::where('suggested_user_id', $fatima->id)
        ->where('suggested_relation_code', 'daughter_in_law')
        ->with(['user'])
        ->first();

    if ($fatimaAsBelleFilleFor) {
        echo "🔍 Cas 1: {$fatimaAsBelleFilleFor->user->name} voit Fatima comme 'Daughter_in_law'\n";
        echo "   Raison: {$fatimaAsBelleFilleFor->reason}\n";
        
        // Analyser pourquoi cette suggestion existe
        $userToAhmed = FamilyRelationship::where('user_id', $fatimaAsBelleFilleFor->user_id)
            ->where('related_user_id', $ahmed->id)
            ->with('relationshipType')
            ->first();
            
        $fatimaToAhmed = FamilyRelationship::where('user_id', $fatima->id)
            ->where('related_user_id', $ahmed->id)
            ->with('relationshipType')
            ->first();

        if ($userToAhmed && $fatimaToAhmed) {
            echo "   Logique détectée:\n";
            echo "     - {$fatimaAsBelleFilleFor->user->name} → Ahmed : {$userToAhmed->relationshipType->name}\n";
            echo "     - Fatima → Ahmed : {$fatimaToAhmed->relationshipType->name}\n";
            
            if (in_array($userToAhmed->relationshipType->name, ['father', 'mother']) && 
                in_array($fatimaToAhmed->relationshipType->name, ['wife', 'husband'])) {
                echo "     ✅ Logique correcte: Parent d'Ahmed + Épouse d'Ahmed = Belle-fille\n";
            } else {
                echo "     ❌ Logique incorrecte!\n";
            }
        }
        echo "\n";
    }

    // Cas 2: Qui voit Mohammed comme "Mari" ?
    $mohammedAsMariFor = Suggestion::where('suggested_user_id', $mohammed->id)
        ->where('suggested_relation_code', 'husband')
        ->with(['user'])
        ->first();

    if ($mohammedAsMariFor) {
        echo "🔍 Cas 2: {$mohammedAsMariFor->user->name} voit Mohammed comme 'Mari'\n";
        echo "   Raison: {$mohammedAsMariFor->reason}\n";
        
        // Cette suggestion est clairement incorrecte
        echo "   ❌ SUGGESTION INCORRECTE: Mohammed ne peut pas être mari de quelqu'un d'autre que sa propre épouse!\n";
        echo "\n";
    }

    // 5. Proposer des corrections
    echo "🔧 Corrections nécessaires:\n\n";

    echo "✅ Suggestions CORRECTES attendues:\n";
    echo "   - Pour Mohammed: Fatima comme 'Mother' (mère)\n";
    echo "   - Pour Mohammed: Ahmed comme 'Father' (père)\n";
    echo "   - Pour Fatima: Mohammed comme 'Son' (fils)\n";
    echo "   - Pour Ahmed: Mohammed comme 'Son' (fils)\n";
    echo "   - Pour Ahmed: Fatima comme 'Wife' (épouse)\n";
    echo "   - Pour Fatima: Ahmed comme 'Husband' (mari)\n\n";

    echo "❌ Suggestions INCORRECTES à supprimer:\n";
    echo "   - Fatima comme 'Daughter_in_law' de qui que ce soit\n";
    echo "   - Mohammed comme 'Mari' de qui que ce soit (sauf sa vraie épouse)\n\n";

    // 6. Supprimer les suggestions incorrectes
    echo "🗑️ Suppression des suggestions incorrectes...\n";
    $deletedCount = Suggestion::whereIn('suggested_relation_code', ['daughter_in_law', 'son_in_law'])
        ->orWhere(function($query) use ($mohammed) {
            $query->where('suggested_user_id', $mohammed->id)
                  ->where('suggested_relation_code', 'husband');
        })
        ->delete();
    
    echo "✅ {$deletedCount} suggestions incorrectes supprimées\n\n";

    echo "🎯 Prochaine étape: Régénérer les suggestions avec la logique corrigée\n";

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
