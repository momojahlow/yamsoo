<?php

require_once 'vendor/autoload.php';

use App\Models\FamilyRelationship;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 ANALYSE DES RELATIONS DANS LA BASE:\n";

$ahmed = User::where('name', 'like', '%Ahmed%')->first();
$fatima = User::where('name', 'like', '%Fatima%')->first();
$youssef = User::where('name', 'like', '%Youssef%')->first();
$leila = User::where('name', 'like', '%Leila%')->first();

echo "👤 Ahmed: {$ahmed->name} (ID: {$ahmed->id})\n";
echo "👤 Fatima: {$fatima->name} (ID: {$fatima->id})\n";
echo "👤 Youssef: {$youssef->name} (ID: {$youssef->id})\n";
echo "👤 Leila: {$leila->name} (ID: {$leila->id})\n\n";

echo "📋 RELATIONS AHMED ↔ FATIMA:\n";
$ahmedFatimaRelations = FamilyRelationship::where(function($query) use ($ahmed, $fatima) {
    $query->where(function($subQuery) use ($ahmed, $fatima) {
        $subQuery->where('user_id', $ahmed->id)->where('related_user_id', $fatima->id);
    })->orWhere(function($subQuery) use ($ahmed, $fatima) {
        $subQuery->where('user_id', $fatima->id)->where('related_user_id', $ahmed->id);
    });
})->with('relationshipType')->get();

foreach ($ahmedFatimaRelations as $rel) {
    echo "  • user_id={$rel->user_id} → related_user_id={$rel->related_user_id} : {$rel->relationshipType->name}\n";
}

echo "\n📋 RELATIONS AHMED ↔ YOUSSEF:\n";
$ahmedYoussefRelations = FamilyRelationship::where(function($query) use ($ahmed, $youssef) {
    $query->where(function($subQuery) use ($ahmed, $youssef) {
        $subQuery->where('user_id', $ahmed->id)->where('related_user_id', $youssef->id);
    })->orWhere(function($subQuery) use ($ahmed, $youssef) {
        $subQuery->where('user_id', $youssef->id)->where('related_user_id', $ahmed->id);
    });
})->with('relationshipType')->get();

foreach ($ahmedYoussefRelations as $rel) {
    echo "  • user_id={$rel->user_id} → related_user_id={$rel->related_user_id} : {$rel->relationshipType->name}\n";
}

echo "\n📋 RELATIONS YOUSSEF ↔ LEILA:\n";
$youssefLeilaRelations = FamilyRelationship::where(function($query) use ($youssef, $leila) {
    $query->where(function($subQuery) use ($youssef, $leila) {
        $subQuery->where('user_id', $youssef->id)->where('related_user_id', $leila->id);
    })->orWhere(function($subQuery) use ($youssef, $leila) {
        $subQuery->where('user_id', $leila->id)->where('related_user_id', $youssef->id);
    });
})->with('relationshipType')->get();

foreach ($youssefLeilaRelations as $rel) {
    echo "  • user_id={$rel->user_id} → related_user_id={$rel->related_user_id} : {$rel->relationshipType->name}\n";
}

echo "\n🔍 PROBLÈME IDENTIFIÉ:\n";
echo "Quand Ahmed (ID: {$ahmed->id}) regarde Leila via Youssef:\n";
echo "- Ahmed → Youssef : ";

// Trouver la relation Ahmed → Youssef
$ahmedToYoussef = FamilyRelationship::where('user_id', $ahmed->id)
    ->where('related_user_id', $youssef->id)
    ->with('relationshipType')
    ->first();

if ($ahmedToYoussef) {
    echo "{$ahmedToYoussef->relationshipType->name} ✅\n";
} else {
    // Chercher l'inverse
    $youssefToAhmed = FamilyRelationship::where('user_id', $youssef->id)
        ->where('related_user_id', $ahmed->id)
        ->with('relationshipType')
        ->first();
    
    if ($youssefToAhmed) {
        echo "INVERSE: {$youssefToAhmed->relationshipType->name} (Youssef → Ahmed) ❌\n";
        echo "  PROBLÈME: Le système devrait détecter Ahmed comme 'father' de Youssef, pas l'inverse!\n";
    }
}

echo "- Youssef → Leila : ";
$youssefToLeila = FamilyRelationship::where('user_id', $youssef->id)
    ->where('related_user_id', $leila->id)
    ->with('relationshipType')
    ->first();

if ($youssefToLeila) {
    echo "{$youssefToLeila->relationshipType->name} ✅\n";
} else {
    $leilaToYoussef = FamilyRelationship::where('user_id', $leila->id)
        ->where('related_user_id', $youssef->id)
        ->with('relationshipType')
        ->first();
    
    if ($leilaToYoussef) {
        echo "INVERSE: {$leilaToYoussef->relationshipType->name} (Leila → Youssef)\n";
    }
}

echo "\n🎯 RÉSULTAT ATTENDU:\n";
echo "Ahmed (father de Youssef) + Leila (wife de Youssef) = Ahmed voit Leila comme 'daughter_in_law'\n";
echo "Mais le système détecte Ahmed comme 'son' de Youssef = Ahmed voit Leila comme 'mother' ❌\n";
