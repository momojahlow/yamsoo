<?php

require_once 'vendor/autoload.php';

use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 ANALYSE DES SUGGESTIONS DANS LA BASE:\n\n";

$users = [
    'Ahmed' => User::where('name', 'like', '%Ahmed%')->first(),
    'Fatima' => User::where('name', 'like', '%Fatima%')->first(),
    'Mohammed' => User::where('name', 'like', '%Mohammed%')->first(),
    'Amina' => User::where('name', 'like', '%Amina%')->first(),
    'Youssef' => User::where('name', 'like', '%Youssef%')->first(),
    'Leila' => User::where('name', 'like', '%Leila%')->first(),
    'Karim' => User::where('name', 'like', '%Karim%')->first(),
];

foreach ($users as $name => $user) {
    echo "👤 {$name}: {$user->name} (ID: {$user->id})\n";
}

echo "\n📋 TOUTES LES SUGGESTIONS DANS LA BASE:\n";
$allSuggestions = Suggestion::with(['user', 'suggestedUser'])->get();

if ($allSuggestions->count() === 0) {
    echo "❌ AUCUNE SUGGESTION TROUVÉE DANS LA BASE DE DONNÉES !\n";
    echo "   Cela confirme que les suggestions ne sont pas sauvegardées.\n\n";
} else {
    foreach ($allSuggestions as $suggestion) {
        echo "  • {$suggestion->user->name} → {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code} ({$suggestion->status})\n";
    }
}

echo "\n📋 SUGGESTIONS PAR UTILISATEUR:\n";
foreach ($users as $name => $user) {
    $userSuggestions = Suggestion::where('user_id', $user->id)->with('suggestedUser')->get();
    echo "🔍 {$name} ({$user->name}):\n";
    
    if ($userSuggestions->count() === 0) {
        echo "  ❌ Aucune suggestion\n";
    } else {
        foreach ($userSuggestions as $suggestion) {
            echo "  • → {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code} ({$suggestion->status})\n";
        }
    }
    echo "\n";
}

echo "🎯 DIAGNOSTIC:\n";
echo "Si aucune suggestion n'est trouvée, cela signifie que :\n";
echo "1. Les inférences sont calculées mais pas sauvegardées\n";
echo "2. Il y a une exception dans createSuggestion() qui empêche la sauvegarde\n";
echo "3. La méthode hasExistingSuggestion() bloque incorrectement la création\n";
