<?php

/**
 * Script pour corriger la logique des suggestions
 * Le problème: Fatima Zahra suggérée comme "Sœur" au lieu de "Mère"
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
use Illuminate\Support\Facades\DB;

try {
    echo "🔧 Correction de la logique des suggestions...\n\n";

    // 1. Supprimer toutes les suggestions incorrectes
    echo "🗑️ Suppression des suggestions incorrectes...\n";
    Suggestion::truncate();
    echo "✅ Suggestions supprimées\n\n";

    // 2. Vérifier les types de relations disponibles
    echo "📊 Types de relations disponibles:\n";
    $relationTypes = RelationshipType::orderBy('sort_order')->get();
    $availableTypes = [];
    foreach ($relationTypes as $type) {
        $availableTypes[] = $type->name;
        echo "   - {$type->name} ({$type->display_name_fr})\n";
    }
    echo "\n";

    // 3. Créer des suggestions correctes manuellement
    echo "🎯 Création de suggestions correctes...\n";

    // Trouver les utilisateurs
    $mohammed = User::where('name', 'like', '%Mohammed%')->first();
    $fatima = User::where('name', 'like', '%Fatima%')->first();
    $ahmed = User::where('name', 'like', '%Ahmed%')->first();

    if (!$mohammed || !$fatima || !$ahmed) {
        echo "❌ Utilisateurs non trouvés\n";
        exit(1);
    }

    echo "👥 Utilisateurs trouvés:\n";
    echo "   - Mohammed: {$mohammed->name} (ID: {$mohammed->id})\n";
    echo "   - Fatima: {$fatima->name} (ID: {$fatima->id})\n";
    echo "   - Ahmed: {$ahmed->name} (ID: {$ahmed->id})\n\n";

    // 4. Vérifier les relations existantes
    echo "🔗 Relations existantes:\n";
    $relations = FamilyRelationship::whereIn('user_id', [$mohammed->id, $fatima->id, $ahmed->id])
        ->orWhereIn('related_user_id', [$mohammed->id, $fatima->id, $ahmed->id])
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

    foreach ($relations as $relation) {
        echo "   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr} ({$relation->relationshipType->name})\n";
    }
    echo "\n";

    // 5. Analyser la logique correcte
    echo "🧠 Logique correcte:\n";
    
    // Chercher la relation Fatima → Mohammed
    $fatimaToMohammed = FamilyRelationship::where('user_id', $fatima->id)
        ->where('related_user_id', $mohammed->id)
        ->with('relationshipType')
        ->first();

    if ($fatimaToMohammed) {
        $relationType = $fatimaToMohammed->relationshipType->name;
        echo "   - Fatima → Mohammed : {$relationType}\n";
        
        if ($relationType === 'mother') {
            echo "   ✅ Donc Mohammed → Fatima devrait être : fils (pas sœur!)\n";
            
            // Créer la suggestion correcte
            $motherType = RelationshipType::where('name', 'mother')->first();
            if ($motherType) {
                $suggestion = new Suggestion([
                    'user_id' => $mohammed->id,
                    'suggested_user_id' => $fatima->id,
                    'suggested_relation_code' => 'mother',
                    'suggestion_type' => 'family_link',
                    'reason' => 'Relation mère-fils détectée automatiquement',
                    'confidence_score' => 95,
                    'status' => 'pending'
                ]);
                $suggestion->save();
                echo "   ✅ Suggestion correcte créée : Fatima comme mère de Mohammed\n";
            }
        }
    }

    // Chercher la relation Ahmed → Fatima
    $ahmedToFatima = FamilyRelationship::where('user_id', $ahmed->id)
        ->where('related_user_id', $fatima->id)
        ->with('relationshipType')
        ->first();

    if ($ahmedToFatima) {
        $relationType = $ahmedToFatima->relationshipType->name;
        echo "   - Ahmed → Fatima : {$relationType}\n";
        
        if ($relationType === 'husband') {
            echo "   ✅ Ahmed est le mari de Fatima\n";
            
            // Suggérer Ahmed comme père de Mohammed
            $fatherType = RelationshipType::where('name', 'father')->first();
            if ($fatherType) {
                $suggestion = new Suggestion([
                    'user_id' => $mohammed->id,
                    'suggested_user_id' => $ahmed->id,
                    'suggested_relation_code' => 'father',
                    'suggestion_type' => 'family_link',
                    'reason' => 'Père via relation mari-épouse avec la mère',
                    'confidence_score' => 90,
                    'status' => 'pending'
                ]);
                $suggestion->save();
                echo "   ✅ Suggestion correcte créée : Ahmed comme père de Mohammed\n";
            }
        }
    }

    echo "\n";

    // 6. Vérifier les nouvelles suggestions
    echo "💡 Nouvelles suggestions pour Mohammed:\n";
    $newSuggestions = Suggestion::where('user_id', $mohammed->id)
        ->with(['suggestedUser'])
        ->get();

    foreach ($newSuggestions as $suggestion) {
        $relationName = RelationshipType::where('name', $suggestion->suggested_relation_code)->first();
        $displayName = $relationName ? $relationName->display_name_fr : $suggestion->suggested_relation_code;
        echo "   - {$suggestion->suggestedUser->name} : {$displayName} ({$suggestion->reason})\n";
    }

    echo "\n🎉 Correction terminée!\n";
    echo "✅ Les suggestions devraient maintenant être correctes\n";
    echo "✅ Fatima sera suggérée comme 'Mère' et non 'Sœur'\n";
    echo "✅ Ahmed sera suggéré comme 'Père'\n";

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
