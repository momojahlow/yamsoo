<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 DIAGNOSTIC DU PROBLÈME FATIMA\n";
echo "================================\n\n";

// Rechercher les utilisateurs
$fatima = User::where('name', 'like', '%Fatima%')->first();
$amina = User::where('name', 'like', '%Amina%')->first();
$mohammed = User::where('name', 'like', '%Mohammed%')->first();

if (!$fatima || !$amina || !$mohammed) {
    echo "❌ Utilisateurs non trouvés:\n";
    if (!$fatima) echo "   - Fatima non trouvée\n";
    if (!$amina) echo "   - Amina non trouvée\n";
    if (!$mohammed) echo "   - Mohammed non trouvé\n";
    
    // Lister tous les utilisateurs disponibles
    echo "\n📋 Utilisateurs disponibles:\n";
    $users = User::all();
    foreach ($users as $user) {
        echo "   - {$user->name} (ID: {$user->id})\n";
    }
    exit;
}

echo "✅ Utilisateurs trouvés:\n";
echo "   Fatima: {$fatima->name} (ID: {$fatima->id})\n";
echo "   Amina: {$amina->name} (ID: {$amina->id})\n";
echo "   Mohammed: {$mohammed->name} (ID: {$mohammed->id})\n\n";

// Vérifier les relations de Fatima
echo "🔗 Relations de Fatima:\n";
$fatimaRelations = FamilyRelationship::where('user_id', $fatima->id)
    ->orWhere('related_user_id', $fatima->id)
    ->with(['user', 'relatedUser', 'relationshipType'])
    ->get();

foreach ($fatimaRelations as $rel) {
    $otherUser = $rel->user_id === $fatima->id ? $rel->relatedUser : $rel->user;
    $direction = $rel->user_id === $fatima->id ? "Fatima → {$otherUser->name}" : "{$otherUser->name} → Fatima";
    echo "   - {$direction} : {$rel->relationshipType->display_name_fr} (code: {$rel->relationshipType->name})\n";
}

echo "\n🔗 Relations d'Amina:\n";
$aminaRelations = FamilyRelationship::where('user_id', $amina->id)
    ->orWhere('related_user_id', $amina->id)
    ->with(['user', 'relatedUser', 'relationshipType'])
    ->get();

foreach ($aminaRelations as $rel) {
    $otherUser = $rel->user_id === $amina->id ? $rel->relatedUser : $rel->user;
    $direction = $rel->user_id === $amina->id ? "Amina → {$otherUser->name}" : "{$otherUser->name} → Amina";
    echo "   - {$direction} : {$rel->relationshipType->display_name_fr} (code: {$rel->relationshipType->name})\n";
}

echo "\n🔗 Relations de Mohammed:\n";
$mohammedRelations = FamilyRelationship::where('user_id', $mohammed->id)
    ->orWhere('related_user_id', $mohammed->id)
    ->with(['user', 'relatedUser', 'relationshipType'])
    ->get();

foreach ($mohammedRelations as $rel) {
    $otherUser = $rel->user_id === $mohammed->id ? $rel->relatedUser : $rel->user;
    $direction = $rel->user_id === $mohammed->id ? "Mohammed → {$otherUser->name}" : "{$otherUser->name} → Mohammed";
    echo "   - {$direction} : {$rel->relationshipType->display_name_fr} (code: {$rel->relationshipType->name})\n";
}

// Analyser pourquoi Fatima apparaît comme "soeur"
echo "\n🧠 ANALYSE DU PROBLÈME:\n";

// Vérifier les relations directes entre Fatima et Amina
$fatimaAminaRelation = FamilyRelationship::where(function($query) use ($fatima, $amina) {
    $query->where('user_id', $fatima->id)->where('related_user_id', $amina->id);
})->orWhere(function($query) use ($fatima, $amina) {
    $query->where('user_id', $amina->id)->where('related_user_id', $fatima->id);
})->with('relationshipType')->first();

if ($fatimaAminaRelation) {
    echo "   ✅ Relation directe Fatima ↔ Amina: {$fatimaAminaRelation->relationshipType->display_name_fr}\n";
} else {
    echo "   ❌ Aucune relation directe entre Fatima et Amina\n";
}

// Vérifier les relations directes entre Fatima et Mohammed
$fatimaMohammedRelation = FamilyRelationship::where(function($query) use ($fatima, $mohammed) {
    $query->where('user_id', $fatima->id)->where('related_user_id', $mohammed->id);
})->orWhere(function($query) use ($fatima, $mohammed) {
    $query->where('user_id', $mohammed->id)->where('related_user_id', $fatima->id);
})->with('relationshipType')->first();

if ($fatimaMohammedRelation) {
    echo "   ✅ Relation directe Fatima ↔ Mohammed: {$fatimaMohammedRelation->relationshipType->display_name_fr}\n";
} else {
    echo "   ❌ Aucune relation directe entre Fatima et Mohammed\n";
}

// Vérifier les types de relations disponibles
echo "\n📋 Types de relations disponibles:\n";
$relationshipTypes = RelationshipType::all();
if ($relationshipTypes->count() > 0) {
    foreach ($relationshipTypes as $type) {
        echo "   - ID: {$type->id}, code: '{$type->code}', name_fr: '{$type->name_fr}'\n";
    }
} else {
    echo "   ❌ Aucun type de relation trouvé dans la base de données\n";
    echo "   💡 Exécutez le script : php fix_relationship_types.php\n";
}

// Vérifier toutes les relations familiales existantes
echo "\n📋 Toutes les relations familiales existantes:\n";
$allRelations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
if ($allRelations->count() > 0) {
    foreach ($allRelations as $rel) {
        echo "   - {$rel->user->name} → {$rel->relatedUser->name} : ";
        echo $rel->relationshipType ? $rel->relationshipType->name_fr : 'Type inconnu';
        echo " (statut: {$rel->status})\n";
    }
} else {
    echo "   ❌ Aucune relation familiale trouvée dans la base de données\n";
}

echo "\n✅ Diagnostic terminé.\n";
