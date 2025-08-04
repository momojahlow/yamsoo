<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FamilyRelationship;
use App\Models\User;

echo "🔍 RELATIONS DANS LA BASE :\n";
$relations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
foreach($relations as $rel) {
    echo $rel->user->name . ' → ' . $rel->relatedUser->name . ' : ' . $rel->relationshipType->name . "\n";
}

echo "\n🔍 FOCUS SUR AHMED ET YOUSSEF :\n";
$ahmed = User::where('name', 'Ahmed Benali')->first();
$youssef = User::where('name', 'Youssef Bennani')->first();

$ahmedToYoussef = FamilyRelationship::where('user_id', $ahmed->id)
    ->where('related_user_id', $youssef->id)
    ->with('relationshipType')
    ->first();

$youssefToAhmed = FamilyRelationship::where('user_id', $youssef->id)
    ->where('related_user_id', $ahmed->id)
    ->with('relationshipType')
    ->first();

if ($ahmedToYoussef) {
    echo "Ahmed → Youssef : " . $ahmedToYoussef->relationshipType->name . "\n";
} else {
    echo "❌ Aucune relation Ahmed → Youssef\n";
}

if ($youssefToAhmed) {
    echo "Youssef → Ahmed : " . $youssefToAhmed->relationshipType->name . "\n";
} else {
    echo "❌ Aucune relation Youssef → Ahmed\n";
}
