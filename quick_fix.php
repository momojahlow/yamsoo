<?php

/**
 * Script de correction rapide pour le problème de contrainte NOT NULL
 * Exécuter avec: php quick_fix.php
 */

require_once 'vendor/autoload.php';

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

echo "🔧 Correction rapide du problème de contrainte NOT NULL...\n";

try {
    // 1. Supprimer complètement la table relationship_types
    echo "🗑️ Suppression de l'ancienne table...\n";
    Schema::dropIfExists('relationship_types');
    
    // 2. Créer la nouvelle table avec la structure correcte
    echo "🏗️ Création de la nouvelle table...\n";
    Schema::create('relationship_types', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('display_name_fr');
        $table->string('display_name_ar');
        $table->string('display_name_en');
        $table->text('description')->nullable();
        $table->string('reverse_relationship')->nullable();
        $table->string('category')->default('direct');
        $table->integer('generation_level')->default(0);
        $table->integer('sort_order')->default(1);
        $table->timestamps();
        
        // Index
        $table->index('category');
        $table->index('generation_level');
        $table->index('sort_order');
    });
    
    echo "✅ Table relationship_types créée avec succès!\n";
    
    // 3. Insérer les données des types de relations
    echo "🌱 Insertion des types de relations...\n";
    
    $relationshipTypes = [
        // Relations directes - Génération -1 (Parents)
        [
            'name' => 'parent',
            'display_name_fr' => 'Parent',
            'display_name_ar' => 'والد/والدة',
            'display_name_en' => 'Parent',
            'description' => 'Relation parent-enfant directe',
            'reverse_relationship' => 'child',
            'category' => 'direct',
            'generation_level' => -1,
            'sort_order' => 1
        ],
        [
            'name' => 'father',
            'display_name_fr' => 'Père',
            'display_name_ar' => 'أب',
            'display_name_en' => 'Father',
            'description' => 'Père biologique ou adoptif',
            'reverse_relationship' => 'child',
            'category' => 'direct',
            'generation_level' => -1,
            'sort_order' => 2
        ],
        [
            'name' => 'mother',
            'display_name_fr' => 'Mère',
            'display_name_ar' => 'أم',
            'display_name_en' => 'Mother',
            'description' => 'Mère biologique ou adoptive',
            'reverse_relationship' => 'child',
            'category' => 'direct',
            'generation_level' => -1,
            'sort_order' => 3
        ],
        [
            'name' => 'child',
            'display_name_fr' => 'Enfant',
            'display_name_ar' => 'طفل/طفلة',
            'display_name_en' => 'Child',
            'description' => 'Enfant biologique ou adoptif',
            'reverse_relationship' => 'parent',
            'category' => 'direct',
            'generation_level' => 1,
            'sort_order' => 4
        ],
        [
            'name' => 'son',
            'display_name_fr' => 'Fils',
            'display_name_ar' => 'ابن',
            'display_name_en' => 'Son',
            'description' => 'Fils biologique ou adoptif',
            'reverse_relationship' => 'parent',
            'category' => 'direct',
            'generation_level' => 1,
            'sort_order' => 5
        ],
        [
            'name' => 'daughter',
            'display_name_fr' => 'Fille',
            'display_name_ar' => 'ابنة',
            'display_name_en' => 'Daughter',
            'description' => 'Fille biologique ou adoptive',
            'reverse_relationship' => 'parent',
            'category' => 'direct',
            'generation_level' => 1,
            'sort_order' => 6
        ],
        [
            'name' => 'wife',
            'display_name_fr' => 'Épouse',
            'display_name_ar' => 'زوجة',
            'display_name_en' => 'Wife',
            'description' => 'Épouse féminine',
            'reverse_relationship' => 'husband',
            'category' => 'marriage',
            'generation_level' => 0,
            'sort_order' => 9
        ],
        [
            'name' => 'husband',
            'display_name_fr' => 'Mari',
            'display_name_ar' => 'زوج',
            'display_name_en' => 'Husband',
            'description' => 'Époux masculin',
            'reverse_relationship' => 'wife',
            'category' => 'marriage',
            'generation_level' => 0,
            'sort_order' => 8
        ],
        [
            'name' => 'daughter_in_law',
            'display_name_fr' => 'Belle-fille',
            'display_name_ar' => 'كنة',
            'display_name_en' => 'Daughter-in-law',
            'description' => 'Épouse du fils',
            'reverse_relationship' => 'father_mother_in_law',
            'category' => 'marriage',
            'generation_level' => 1,
            'sort_order' => 26
        ],
        [
            'name' => 'cousin',
            'display_name_fr' => 'Cousin/Cousine',
            'display_name_ar' => 'ابن/ابنة عم/خال',
            'display_name_en' => 'Cousin',
            'description' => 'Enfant de l\'oncle ou de la tante',
            'reverse_relationship' => 'cousin',
            'category' => 'extended',
            'generation_level' => 0,
            'sort_order' => 27
        ],
    ];
    
    foreach ($relationshipTypes as $type) {
        $type['created_at'] = now();
        $type['updated_at'] = now();
        DB::table('relationship_types')->insert($type);
    }
    
    $count = DB::table('relationship_types')->count();
    echo "✅ {$count} types de relations insérés avec succès!\n";
    
    echo "\n🎯 Problème résolu ! Vous pouvez maintenant :\n";
    echo "   1. Exécuter les seeders sans erreur\n";
    echo "   2. Utiliser le système de suggestions corrigé\n";
    echo "   3. Tester l'inférence des relations\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
}
