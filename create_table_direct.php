<?php

/**
 * Script PHP pour créer directement la table relationship_types
 * Exécuter avec: php create_table_direct.php
 */

echo "🔧 Création directe de la table relationship_types...\n";

try {
    // Connexion directe à SQLite
    $dbPath = __DIR__ . '/database/database.sqlite';
    
    // Créer le répertoire database s'il n'existe pas
    $dbDir = dirname($dbPath);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
        echo "📁 Répertoire database créé\n";
    }
    
    // Créer le fichier s'il n'existe pas
    if (!file_exists($dbPath)) {
        touch($dbPath);
        echo "📄 Fichier database.sqlite créé\n";
    }
    
    $pdo = new PDO("sqlite:{$dbPath}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "📊 Connexion à la base de données établie\n";
    
    // 1. Supprimer la table si elle existe
    echo "🗑️ Suppression de l'ancienne table...\n";
    $pdo->exec("DROP TABLE IF EXISTS relationship_types");
    
    // 2. Créer la nouvelle table
    echo "🏗️ Création de la nouvelle table...\n";
    $createTableSQL = "
        CREATE TABLE relationship_types (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL UNIQUE,
            display_name_fr VARCHAR(255) NOT NULL,
            display_name_ar VARCHAR(255) NOT NULL,
            display_name_en VARCHAR(255) NOT NULL,
            description TEXT,
            reverse_relationship VARCHAR(255),
            category VARCHAR(255) NOT NULL DEFAULT 'direct',
            generation_level INTEGER NOT NULL DEFAULT 0,
            sort_order INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME,
            updated_at DATETIME
        )
    ";
    
    $pdo->exec($createTableSQL);
    echo "✅ Table relationship_types créée\n";
    
    // 3. Créer les index
    echo "📊 Création des index...\n";
    $pdo->exec("CREATE INDEX idx_relationship_types_category ON relationship_types(category)");
    $pdo->exec("CREATE INDEX idx_relationship_types_generation_level ON relationship_types(generation_level)");
    $pdo->exec("CREATE INDEX idx_relationship_types_sort_order ON relationship_types(sort_order)");
    
    // 4. Insérer les données
    echo "🌱 Insertion des types de relations...\n";
    
    $now = date('Y-m-d H:i:s');
    
    $relationshipTypes = [
        ['parent', 'Parent', 'والد/والدة', 'Parent', 'Relation parent-enfant directe', 'child', 'direct', -1, 1],
        ['father', 'Père', 'أب', 'Father', 'Père biologique ou adoptif', 'child', 'direct', -1, 2],
        ['mother', 'Mère', 'أم', 'Mother', 'Mère biologique ou adoptive', 'child', 'direct', -1, 3],
        ['child', 'Enfant', 'طفل/طفلة', 'Child', 'Enfant biologique ou adoptif', 'parent', 'direct', 1, 4],
        ['son', 'Fils', 'ابن', 'Son', 'Fils biologique ou adoptif', 'parent', 'direct', 1, 5],
        ['daughter', 'Fille', 'ابنة', 'Daughter', 'Fille biologique ou adoptive', 'parent', 'direct', 1, 6],
        ['spouse', 'Époux/Épouse', 'زوج/زوجة', 'Spouse', 'Conjoint marié', 'spouse', 'marriage', 0, 7],
        ['husband', 'Mari', 'زوج', 'Husband', 'Époux masculin', 'wife', 'marriage', 0, 8],
        ['wife', 'Épouse', 'زوجة', 'Wife', 'Épouse féminine', 'husband', 'marriage', 0, 9],
        ['sibling', 'Frère/Sœur', 'أخ/أخت', 'Sibling', 'Frère ou sœur', 'sibling', 'direct', 0, 10],
        ['brother', 'Frère', 'أخ', 'Brother', 'Frère biologique ou adoptif', 'sibling', 'direct', 0, 11],
        ['sister', 'Sœur', 'أخت', 'Sister', 'Sœur biologique ou adoptive', 'sibling', 'direct', 0, 12],
        ['grandparent', 'Grand-parent', 'جد/جدة', 'Grandparent', 'Grand-père ou grand-mère', 'grandchild', 'extended', -2, 13],
        ['grandfather', 'Grand-père', 'جد', 'Grandfather', 'Père du père ou de la mère', 'grandchild', 'extended', -2, 14],
        ['grandmother', 'Grand-mère', 'جدة', 'Grandmother', 'Mère du père ou de la mère', 'grandchild', 'extended', -2, 15],
        ['grandchild', 'Petit-enfant', 'حفيد/حفيدة', 'Grandchild', 'Enfant de son enfant', 'grandparent', 'extended', 2, 16],
        ['grandson', 'Petit-fils', 'حفيد', 'Grandson', 'Fils de son enfant', 'grandparent', 'extended', 2, 17],
        ['granddaughter', 'Petite-fille', 'حفيدة', 'Granddaughter', 'Fille de son enfant', 'grandparent', 'extended', 2, 18],
        ['uncle', 'Oncle', 'عم/خال', 'Uncle', 'Frère du père ou de la mère', 'nephew_niece', 'extended', -1, 19],
        ['aunt', 'Tante', 'عمة/خالة', 'Aunt', 'Sœur du père ou de la mère', 'nephew_niece', 'extended', -1, 20],
        ['nephew', 'Neveu', 'ابن أخ/أخت', 'Nephew', 'Fils du frère ou de la sœur', 'uncle_aunt', 'extended', 1, 21],
        ['niece', 'Nièce', 'ابنة أخ/أخت', 'Niece', 'Fille du frère ou de la sœur', 'uncle_aunt', 'extended', 1, 22],
        ['father_in_law', 'Beau-père', 'حمو', 'Father-in-law', 'Père du conjoint', 'son_daughter_in_law', 'marriage', -1, 23],
        ['mother_in_law', 'Belle-mère', 'حماة', 'Mother-in-law', 'Mère du conjoint', 'son_daughter_in_law', 'marriage', -1, 24],
        ['son_in_law', 'Gendre', 'صهر', 'Son-in-law', 'Mari de la fille', 'father_mother_in_law', 'marriage', 1, 25],
        ['daughter_in_law', 'Belle-fille', 'كنة', 'Daughter-in-law', 'Épouse du fils', 'father_mother_in_law', 'marriage', 1, 26],
        ['cousin', 'Cousin/Cousine', 'ابن/ابنة عم/خال', 'Cousin', 'Enfant de l\'oncle ou de la tante', 'cousin', 'extended', 0, 27],
        ['adoptive_parent', 'Parent adoptif', 'والد/والدة بالتبني', 'Adoptive parent', 'Parent par adoption légale', 'adopted_child', 'adoption', -1, 28],
        ['adopted_child', 'Enfant adopté', 'طفل/طفلة بالتبني', 'Adopted child', 'Enfant par adoption légale', 'adoptive_parent', 'adoption', 1, 29],
        ['family_member', 'Membre de la famille', 'فرد من العائلة', 'Family member', 'Membre de la famille (relation non spécifiée)', 'family_member', 'extended', 0, 30],
    ];
    
    $insertSQL = "
        INSERT INTO relationship_types 
        (name, display_name_fr, display_name_ar, display_name_en, description, reverse_relationship, category, generation_level, sort_order, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    
    $stmt = $pdo->prepare($insertSQL);
    
    foreach ($relationshipTypes as $type) {
        $stmt->execute([
            $type[0], // name
            $type[1], // display_name_fr
            $type[2], // display_name_ar
            $type[3], // display_name_en
            $type[4], // description
            $type[5], // reverse_relationship
            $type[6], // category
            $type[7], // generation_level
            $type[8], // sort_order
            $now,     // created_at
            $now      // updated_at
        ]);
    }
    
    $count = count($relationshipTypes);
    echo "✅ {$count} types de relations insérés!\n";
    
    // 5. Vérifier le résultat
    echo "\n🔍 Vérification finale...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM relationship_types");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Nombre total: {$result['count']}\n";
    
    // Afficher quelques exemples
    $stmt = $pdo->query("SELECT name, display_name_fr, category FROM relationship_types ORDER BY sort_order LIMIT 5");
    echo "📋 Exemples:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['name']} ({$row['display_name_fr']}, {$row['category']})\n";
    }
    
    echo "\n🎉 Succès total!\n";
    echo "✅ Table relationship_types créée avec la nouvelle structure\n";
    echo "✅ 30 types de relations insérés\n";
    echo "✅ Plus de problème de contrainte NOT NULL\n";
    echo "✅ Structure utilise 'name' au lieu de 'code'\n";
    echo "\n🎯 Vous pouvez maintenant exécuter vos seeders sans erreur!\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
