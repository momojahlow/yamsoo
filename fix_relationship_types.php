<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 CORRECTION DES TYPES DE RELATIONS\n";
echo "====================================\n\n";

// Supprimer les enregistrements existants vides
DB::table('relationship_types')->truncate();
echo "🧹 Table relationship_types vidée\n";

// Insérer les types de relations corrects
$relationshipTypes = [
    // Relations directes
    ['code' => 'father', 'name_fr' => 'Père', 'name_ar' => 'أب', 'name_en' => 'Father', 'gender' => 'male', 'requires_mother_name' => false],
    ['code' => 'mother', 'name_fr' => 'Mère', 'name_ar' => 'أم', 'name_en' => 'Mother', 'gender' => 'female', 'requires_mother_name' => false],
    ['code' => 'son', 'name_fr' => 'Fils', 'name_ar' => 'ابن', 'name_en' => 'Son', 'gender' => 'male', 'requires_mother_name' => false],
    ['code' => 'daughter', 'name_fr' => 'Fille', 'name_ar' => 'ابنة', 'name_en' => 'Daughter', 'gender' => 'female', 'requires_mother_name' => false],

    // Relations de fratrie
    ['code' => 'brother', 'name_fr' => 'Frère', 'name_ar' => 'أخ', 'name_en' => 'Brother', 'gender' => 'male', 'requires_mother_name' => false],
    ['code' => 'sister', 'name_fr' => 'Sœur', 'name_ar' => 'أخت', 'name_en' => 'Sister', 'gender' => 'female', 'requires_mother_name' => false],

    // Relations de mariage
    ['code' => 'husband', 'name_fr' => 'Mari', 'name_ar' => 'زوج', 'name_en' => 'Husband', 'gender' => 'male', 'requires_mother_name' => false],
    ['code' => 'wife', 'name_fr' => 'Épouse', 'name_ar' => 'زوجة', 'name_en' => 'Wife', 'gender' => 'female', 'requires_mother_name' => false],

    // Cousins (simplifié)
    ['code' => 'cousin', 'name_fr' => 'Cousin/Cousine', 'name_ar' => 'ابن/ابنة عم/خال', 'name_en' => 'Cousin', 'gender' => 'both', 'requires_mother_name' => false],
];

$inserted = 0;
foreach ($relationshipTypes as $type) {
    DB::table('relationship_types')->insert(array_merge($type, [
        'created_at' => now(),
        'updated_at' => now(),
    ]));
    $inserted++;
    echo "✅ {$type['name_fr']} ({$type['code']}) ajouté\n";
}

echo "\n📊 {$inserted} types de relations insérés avec succès\n\n";

// Vérifier le résultat
echo "🔍 VÉRIFICATION:\n";
$types = DB::table('relationship_types')->orderBy('code')->get();
foreach ($types as $type) {
    echo "   - {$type->code} : {$type->name_fr}\n";
}

echo "\n✅ Types de relations corrigés avec succès !\n";
