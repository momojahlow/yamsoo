<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Suggestion;
use App\Models\User;
use App\Models\FamilyRelationship;

echo "📊 RELATIONS DANS LA BASE DE DONNÉES:\n";
$relations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
foreach ($relations as $relation) {
    echo "- {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->name}\n";
}
echo "\n";

echo "📊 SUGGESTIONS DANS LA BASE DE DONNÉES:\n";
$suggestions = Suggestion::with(['user', 'suggestedUser'])->get();
foreach ($suggestions as $suggestion) {
    echo "- {$suggestion->user->name} → {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code} ({$suggestion->suggested_relation_name})\n";
}
echo "Total: " . $suggestions->count() . " suggestions\n\n";

echo "🔍 SUGGESTIONS PAR UTILISATEUR:\n";
$users = ['Ahmed Benali', 'Fatima Zahra', 'Mohammed Alami', 'Amina Tazi', 'Youssef Bennani', 'Leila Mansouri', 'Karim El Fassi'];

foreach ($users as $userName) {
    $user = User::where('name', $userName)->first();
    if ($user) {
        $userSuggestions = Suggestion::where('user_id', $user->id)->with('suggestedUser')->get();
        echo "\n🔍 Suggestions pour {$userName}:\n";
        if ($userSuggestions->count() > 0) {
            foreach ($userSuggestions as $suggestion) {
                echo "  - {$suggestion->suggestedUser->name} comme {$suggestion->suggested_relation_name} ({$suggestion->suggested_relation_code})\n";
            }
        } else {
            echo "  (Aucune suggestion)\n";
        }
    }
}
