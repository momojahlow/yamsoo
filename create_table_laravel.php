<?php

/**
 * Script Laravel pour créer la table relationship_types
 * Exécuter avec: php create_table_laravel.php
 */

// Charger Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

echo "🔧 Création de la table relationship_types avec Laravel...\n";

try {
    // 1. Supprimer la table si elle existe
    echo "🗑️ Suppression de l'ancienne table...\n";
    Schema::dropIfExists('relationship_types');
    
    // 2. Créer la nouvelle table
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
    
    echo "✅ Table créée avec succès!\n";
    
    // 3. Insérer les données
    echo "🌱 Insertion des types de relations...\n";
    
    $relationshipTypes = [
        ['name' => 'parent', 'display_name_fr' => 'Parent', 'display_name_ar' => 'والد/والدة', 'display_name_en' => 'Parent', 'description' => 'Relation parent-enfant directe', 'reverse_relationship' => 'child', 'category' => 'direct', 'generation_level' => -1, 'sort_order' => 1],
        ['name' => 'father', 'display_name_fr' => 'Père', 'display_name_ar' => 'أب', 'display_name_en' => 'Father', 'description' => 'Père biologique ou adoptif', 'reverse_relationship' => 'child', 'category' => 'direct', 'generation_level' => -1, 'sort_order' => 2],
        ['name' => 'mother', 'display_name_fr' => 'Mère', 'display_name_ar' => 'أم', 'display_name_en' => 'Mother', 'description' => 'Mère biologique ou adoptive', 'reverse_relationship' => 'child', 'category' => 'direct', 'generation_level' => -1, 'sort_order' => 3],
        ['name' => 'child', 'display_name_fr' => 'Enfant', 'display_name_ar' => 'طفل/طفلة', 'display_name_en' => 'Child', 'description' => 'Enfant biologique ou adoptif', 'reverse_relationship' => 'parent', 'category' => 'direct', 'generation_level' => 1, 'sort_order' => 4],
        ['name' => 'son', 'display_name_fr' => 'Fils', 'display_name_ar' => 'ابن', 'display_name_en' => 'Son', 'description' => 'Fils biologique ou adoptif', 'reverse_relationship' => 'parent', 'category' => 'direct', 'generation_level' => 1, 'sort_order' => 5],
        ['name' => 'daughter', 'display_name_fr' => 'Fille', 'display_name_ar' => 'ابنة', 'display_name_en' => 'Daughter', 'description' => 'Fille biologique ou adoptive', 'reverse_relationship' => 'parent', 'category' => 'direct', 'generation_level' => 1, 'sort_order' => 6],
        ['name' => 'spouse', 'display_name_fr' => 'Époux/Épouse', 'display_name_ar' => 'زوج/زوجة', 'display_name_en' => 'Spouse', 'description' => 'Conjoint marié', 'reverse_relationship' => 'spouse', 'category' => 'marriage', 'generation_level' => 0, 'sort_order' => 7],
        ['name' => 'husband', 'display_name_fr' => 'Mari', 'display_name_ar' => 'زوج', 'display_name_en' => 'Husband', 'description' => 'Époux masculin', 'reverse_relationship' => 'wife', 'category' => 'marriage', 'generation_level' => 0, 'sort_order' => 8],
        ['name' => 'wife', 'display_name_fr' => 'Épouse', 'display_name_ar' => 'زوجة', 'display_name_en' => 'Wife', 'description' => 'Épouse féminine', 'reverse_relationship' => 'husband', 'category' => 'marriage', 'generation_level' => 0, 'sort_order' => 9],
        ['name' => 'sibling', 'display_name_fr' => 'Frère/Sœur', 'display_name_ar' => 'أخ/أخت', 'display_name_en' => 'Sibling', 'description' => 'Frère ou sœur', 'reverse_relationship' => 'sibling', 'category' => 'direct', 'generation_level' => 0, 'sort_order' => 10],
        ['name' => 'brother', 'display_name_fr' => 'Frère', 'display_name_ar' => 'أخ', 'display_name_en' => 'Brother', 'description' => 'Frère biologique ou adoptif', 'reverse_relationship' => 'sibling', 'category' => 'direct', 'generation_level' => 0, 'sort_order' => 11],
        ['name' => 'sister', 'display_name_fr' => 'Sœur', 'display_name_ar' => 'أخت', 'display_name_en' => 'Sister', 'description' => 'Sœur biologique ou adoptive', 'reverse_relationship' => 'sibling', 'category' => 'direct', 'generation_level' => 0, 'sort_order' => 12],
        ['name' => 'grandparent', 'display_name_fr' => 'Grand-parent', 'display_name_ar' => 'جد/جدة', 'display_name_en' => 'Grandparent', 'description' => 'Grand-père ou grand-mère', 'reverse_relationship' => 'grandchild', 'category' => 'extended', 'generation_level' => -2, 'sort_order' => 13],
        ['name' => 'grandfather', 'display_name_fr' => 'Grand-père', 'display_name_ar' => 'جد', 'display_name_en' => 'Grandfather', 'description' => 'Père du père ou de la mère', 'reverse_relationship' => 'grandchild', 'category' => 'extended', 'generation_level' => -2, 'sort_order' => 14],
        ['name' => 'grandmother', 'display_name_fr' => 'Grand-mère', 'display_name_ar' => 'جدة', 'display_name_en' => 'Grandmother', 'description' => 'Mère du père ou de la mère', 'reverse_relationship' => 'grandchild', 'category' => 'extended', 'generation_level' => -2, 'sort_order' => 15],
        ['name' => 'grandchild', 'display_name_fr' => 'Petit-enfant', 'display_name_ar' => 'حفيد/حفيدة', 'display_name_en' => 'Grandchild', 'description' => 'Enfant de son enfant', 'reverse_relationship' => 'grandparent', 'category' => 'extended', 'generation_level' => 2, 'sort_order' => 16],
        ['name' => 'grandson', 'display_name_fr' => 'Petit-fils', 'display_name_ar' => 'حفيد', 'display_name_en' => 'Grandson', 'description' => 'Fils de son enfant', 'reverse_relationship' => 'grandparent', 'category' => 'extended', 'generation_level' => 2, 'sort_order' => 17],
        ['name' => 'granddaughter', 'display_name_fr' => 'Petite-fille', 'display_name_ar' => 'حفيدة', 'display_name_en' => 'Granddaughter', 'description' => 'Fille de son enfant', 'reverse_relationship' => 'grandparent', 'category' => 'extended', 'generation_level' => 2, 'sort_order' => 18],
        ['name' => 'uncle', 'display_name_fr' => 'Oncle', 'display_name_ar' => 'عم/خال', 'display_name_en' => 'Uncle', 'description' => 'Frère du père ou de la mère', 'reverse_relationship' => 'nephew_niece', 'category' => 'extended', 'generation_level' => -1, 'sort_order' => 19],
        ['name' => 'aunt', 'display_name_fr' => 'Tante', 'display_name_ar' => 'عمة/خالة', 'display_name_en' => 'Aunt', 'description' => 'Sœur du père ou de la mère', 'reverse_relationship' => 'nephew_niece', 'category' => 'extended', 'generation_level' => -1, 'sort_order' => 20],
        ['name' => 'nephew', 'display_name_fr' => 'Neveu', 'display_name_ar' => 'ابن أخ/أخت', 'display_name_en' => 'Nephew', 'description' => 'Fils du frère ou de la sœur', 'reverse_relationship' => 'uncle_aunt', 'category' => 'extended', 'generation_level' => 1, 'sort_order' => 21],
        ['name' => 'niece', 'display_name_fr' => 'Nièce', 'display_name_ar' => 'ابنة أخ/أخت', 'display_name_en' => 'Niece', 'description' => 'Fille du frère ou de la sœur', 'reverse_relationship' => 'uncle_aunt', 'category' => 'extended', 'generation_level' => 1, 'sort_order' => 22],
        ['name' => 'father_in_law', 'display_name_fr' => 'Beau-père', 'display_name_ar' => 'حمو', 'display_name_en' => 'Father-in-law', 'description' => 'Père du conjoint', 'reverse_relationship' => 'son_daughter_in_law', 'category' => 'marriage', 'generation_level' => -1, 'sort_order' => 23],
        ['name' => 'mother_in_law', 'display_name_fr' => 'Belle-mère', 'display_name_ar' => 'حماة', 'display_name_en' => 'Mother-in-law', 'description' => 'Mère du conjoint', 'reverse_relationship' => 'son_daughter_in_law', 'category' => 'marriage', 'generation_level' => -1, 'sort_order' => 24],
        ['name' => 'son_in_law', 'display_name_fr' => 'Gendre', 'display_name_ar' => 'صهر', 'display_name_en' => 'Son-in-law', 'description' => 'Mari de la fille', 'reverse_relationship' => 'father_mother_in_law', 'category' => 'marriage', 'generation_level' => 1, 'sort_order' => 25],
        ['name' => 'daughter_in_law', 'display_name_fr' => 'Belle-fille', 'display_name_ar' => 'كنة', 'display_name_en' => 'Daughter-in-law', 'description' => 'Épouse du fils', 'reverse_relationship' => 'father_mother_in_law', 'category' => 'marriage', 'generation_level' => 1, 'sort_order' => 26],
        ['name' => 'cousin', 'display_name_fr' => 'Cousin/Cousine', 'display_name_ar' => 'ابن/ابنة عم/خال', 'display_name_en' => 'Cousin', 'description' => 'Enfant de l\'oncle ou de la tante', 'reverse_relationship' => 'cousin', 'category' => 'extended', 'generation_level' => 0, 'sort_order' => 27],
        ['name' => 'adoptive_parent', 'display_name_fr' => 'Parent adoptif', 'display_name_ar' => 'والد/والدة بالتبني', 'display_name_en' => 'Adoptive parent', 'description' => 'Parent par adoption légale', 'reverse_relationship' => 'adopted_child', 'category' => 'adoption', 'generation_level' => -1, 'sort_order' => 28],
        ['name' => 'adopted_child', 'display_name_fr' => 'Enfant adopté', 'display_name_ar' => 'طفل/طفلة بالتبني', 'display_name_en' => 'Adopted child', 'description' => 'Enfant par adoption légale', 'reverse_relationship' => 'adoptive_parent', 'category' => 'adoption', 'generation_level' => 1, 'sort_order' => 29],
        ['name' => 'family_member', 'display_name_fr' => 'Membre de la famille', 'display_name_ar' => 'فرد من العائلة', 'display_name_en' => 'Family member', 'description' => 'Membre de la famille (relation non spécifiée)', 'reverse_relationship' => 'family_member', 'category' => 'extended', 'generation_level' => 0, 'sort_order' => 30],
    ];
    
    foreach ($relationshipTypes as $type) {
        $type['created_at'] = now();
        $type['updated_at'] = now();
        DB::table('relationship_types')->insert($type);
    }
    
    $count = count($relationshipTypes);
    echo "✅ {$count} types de relations insérés!\n";
    
    // 4. Vérifier le résultat
    echo "\n🔍 Vérification finale...\n";
    $totalCount = DB::table('relationship_types')->count();
    echo "📊 Nombre total: {$totalCount}\n";
    
    // Afficher quelques exemples
    $examples = DB::table('relationship_types')
        ->select('name', 'display_name_fr', 'category')
        ->orderBy('sort_order')
        ->limit(5)
        ->get();
    
    echo "📋 Exemples:\n";
    foreach ($examples as $example) {
        echo "   - {$example->name} ({$example->display_name_fr}, {$example->category})\n";
    }
    
    echo "\n🎉 Succès total!\n";
    echo "✅ Table relationship_types créée avec la nouvelle structure\n";
    echo "✅ 30 types de relations insérés\n";
    echo "✅ Plus de problème de contrainte NOT NULL\n";
    echo "✅ Structure utilise 'name' au lieu de 'code'\n";
    echo "\n🎯 Vous pouvez maintenant exécuter vos seeders sans erreur!\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
