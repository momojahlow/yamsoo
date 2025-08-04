<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Services\SuggestionService;

echo "🔍 DEBUG SPÉCIFIQUE POUR AHMED → LEILA\n";

$ahmed = User::where('name', 'Ahmed Benali')->first();
$youssef = User::where('name', 'Youssef Bennani')->first();
$leila = User::where('name', 'Leila Mansouri')->first();

echo "Ahmed ID: {$ahmed->id}\n";
echo "Youssef ID: {$youssef->id}\n";
echo "Leila ID: {$leila->id}\n\n";

// Vérifier la relation Ahmed → Youssef
$ahmedToYoussef = FamilyRelationship::where('user_id', $ahmed->id)
    ->where('related_user_id', $youssef->id)
    ->with('relationshipType')
    ->first();

if ($ahmedToYoussef) {
    echo "✅ Ahmed → Youssef : {$ahmedToYoussef->relationshipType->name}\n";
} else {
    echo "❌ Pas de relation Ahmed → Youssef\n";
}

// Vérifier la relation Youssef → Leila
$youssefToLeila = FamilyRelationship::where('user_id', $youssef->id)
    ->where('related_user_id', $leila->id)
    ->with('relationshipType')
    ->first();

if ($youssefToLeila) {
    echo "✅ Youssef → Leila : {$youssefToLeila->relationshipType->name}\n";
} else {
    echo "❌ Pas de relation Youssef → Leila\n";
}

echo "\n🔍 SIMULATION DE L'INFÉRENCE :\n";
echo "User: Ahmed\n";
echo "Connector: Youssef\n";
echo "Suggested: Leila\n";
echo "User -> Connector: {$ahmedToYoussef->relationshipType->name}\n";
echo "Connector -> Suggested: {$youssefToLeila->relationshipType->name}\n";

// Analyser le genre de Leila
echo "Leila Gender: " . ($leila->profile?->gender ?? 'unknown') . "\n";

// Déterminer quel cas devrait être déclenché
$userCode = $ahmedToYoussef->relationshipType->name;
$suggestedCode = $youssefToLeila->relationshipType->name;

echo "\nAnalyse des cas :\n";
echo "userCode: {$userCode}\n";
echo "suggestedCode: {$suggestedCode}\n";

if (in_array($userCode, ['son', 'daughter']) && in_array($suggestedCode, ['husband', 'wife'])) {
    echo "✅ CAS 1 DÉCLENCHÉ: enfant + conjoint → parent\n";
} elseif (in_array($userCode, ['father', 'mother']) && in_array($suggestedCode, ['husband', 'wife'])) {
    echo "✅ CAS ALLIANCE PARENT INVERSE DÉCLENCHÉ: parent + conjoint → belle-fille/gendre\n";
} else {
    echo "❌ Aucun cas reconnu\n";
}
