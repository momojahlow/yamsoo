<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\FamilyRelationship;

// Bootstrap Laravel
$app = new Application(realpath(__DIR__));
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 DEBUG DES RELATIONS DANS LA BASE DE DONNÉES\n";
echo "==============================================\n\n";

// Récupérer les utilisateurs
$ahmed = User::where('name', 'like', '%Ahmed%')->first();
$fatima = User::where('name', 'like', '%Fatima%')->first();
$mohammed = User::where('name', 'like', '%Mohammed%')->first();

if (!$ahmed || !$fatima || !$mohammed) {
    echo "❌ Utilisateurs non trouvés\n";
    exit;
}

echo "👥 Utilisateurs trouvés:\n";
echo "- Ahmed: ID {$ahmed->id}\n";
echo "- Fatima: ID {$fatima->id}\n";
echo "- Mohammed: ID {$mohammed->id}\n\n";

// Récupérer toutes les relations
$relations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])
    ->where('status', 'accepted')
    ->get();

echo "📋 TOUTES LES RELATIONS DANS LA BASE:\n";
foreach ($relations as $relation) {
    echo "- {$relation->user->name} ({$relation->user_id}) → {$relation->relatedUser->name} ({$relation->related_user_id}) : {$relation->relationshipType->name}\n";
}

echo "\n🔍 ANALYSE SPÉCIFIQUE:\n";

// Relation Ahmed → Fatima
$ahmedToFatima = FamilyRelationship::where('user_id', $ahmed->id)
    ->where('related_user_id', $fatima->id)
    ->with('relationshipType')
    ->first();

if ($ahmedToFatima) {
    echo "Ahmed → Fatima: {$ahmedToFatima->relationshipType->name}\n";
} else {
    echo "Ahmed → Fatima: AUCUNE RELATION\n";
}

// Relation Fatima → Ahmed
$fatimaToAhmed = FamilyRelationship::where('user_id', $fatima->id)
    ->where('related_user_id', $ahmed->id)
    ->with('relationshipType')
    ->first();

if ($fatimaToAhmed) {
    echo "Fatima → Ahmed: {$fatimaToAhmed->relationshipType->name}\n";
} else {
    echo "Fatima → Ahmed: AUCUNE RELATION\n";
}

// Relation Ahmed → Mohammed
$ahmedToMohammed = FamilyRelationship::where('user_id', $ahmed->id)
    ->where('related_user_id', $mohammed->id)
    ->with('relationshipType')
    ->first();

if ($ahmedToMohammed) {
    echo "Ahmed → Mohammed: {$ahmedToMohammed->relationshipType->name}\n";
} else {
    echo "Ahmed → Mohammed: AUCUNE RELATION\n";
}

// Relation Mohammed → Ahmed
$mohammedToAhmed = FamilyRelationship::where('user_id', $mohammed->id)
    ->where('related_user_id', $ahmed->id)
    ->with('relationshipType')
    ->first();

if ($mohammedToAhmed) {
    echo "Mohammed → Ahmed: {$mohammedToAhmed->relationshipType->name}\n";
} else {
    echo "Mohammed → Ahmed: AUCUNE RELATION\n";
}

echo "\n🎯 PROBLÈME IDENTIFIÉ:\n";
echo "Pour que les suggestions soient correctes:\n";
echo "- Fatima → Ahmed devrait être 'wife'\n";
echo "- Ahmed → Fatima devrait être 'husband'\n";
echo "- Mohammed → Ahmed devrait être 'son'\n";
echo "- Ahmed → Mohammed devrait être 'father'\n";
