<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Suggestion;
use App\Models\User;

echo "🔍 SUGGESTIONS DANS LA BASE :\n";
$suggestions = Suggestion::with(['user', 'suggestedUser'])->get();

if ($suggestions->count() === 0) {
    echo "❌ AUCUNE SUGGESTION TROUVÉE !\n";
} else {
    echo "✅ {$suggestions->count()} suggestions trouvées :\n";
    foreach($suggestions as $suggestion) {
        echo $suggestion->user->name . ' → ' . $suggestion->suggestedUser->name . ' : ' . $suggestion->suggested_relation_code . " (" . $suggestion->suggested_relation_name . ")\n";
    }
}

echo "\n🔍 FOCUS SUR LEILA ET AHMED :\n";
$leila = User::where('name', 'Leila Mansouri')->first();
$ahmed = User::where('name', 'Ahmed Benali')->first();

$leilaToAhmed = Suggestion::where('user_id', $leila->id)
    ->where('suggested_user_id', $ahmed->id)
    ->first();

$ahmedToLeila = Suggestion::where('user_id', $ahmed->id)
    ->where('suggested_user_id', $leila->id)
    ->first();

if ($leilaToAhmed) {
    echo "Leila → Ahmed : " . $leilaToAhmed->suggested_relation_code . " (" . $leilaToAhmed->suggested_relation_name . ")\n";
} else {
    echo "❌ Aucune suggestion Leila → Ahmed\n";
}

if ($ahmedToLeila) {
    echo "Ahmed → Leila : " . $ahmedToLeila->suggested_relation_code . " (" . $ahmedToLeila->suggested_relation_name . ")\n";
} else {
    echo "❌ Aucune suggestion Ahmed → Leila\n";
}
