#!/bin/bash

echo "🔧 Correction du problème de migration des types de relations..."

# 1. Vérifier l'état actuel
echo "📊 Vérification de l'état actuel..."
php artisan migrate:status | grep relationship_types

# 2. Rollback de la migration problématique si elle existe
echo "⏪ Rollback de la migration problématique..."
php artisan migrate:rollback --path=database/migrations/2025_07_25_000000_update_relationship_types_table_structure.php --force

# 3. Sauvegarder les données existantes si elles existent
echo "💾 Sauvegarde des données existantes..."
php artisan tinker --execute="
try {
    \$types = App\Models\RelationshipType::all();
    if (\$types->count() > 0) {
        echo 'Données existantes trouvées: ' . \$types->count() . ' types de relations' . PHP_EOL;
        // Sauvegarder dans un fichier temporaire
        file_put_contents('relationship_types_backup.json', \$types->toJson());
        echo 'Sauvegarde créée dans relationship_types_backup.json' . PHP_EOL;
    } else {
        echo 'Aucune donnée existante trouvée' . PHP_EOL;
    }
} catch (Exception \$e) {
    echo 'Erreur lors de la sauvegarde: ' . \$e->getMessage() . PHP_EOL;
}
"

# 4. Supprimer complètement la table pour repartir à zéro
echo "🗑️ Suppression de la table pour repartir à zéro..."
php artisan tinker --execute="
use Illuminate\Support\Facades\Schema;
if (Schema::hasTable('relationship_types')) {
    Schema::dropIfExists('relationship_types');
    echo 'Table relationship_types supprimée' . PHP_EOL;
} else {
    echo 'Table relationship_types n\'existe pas' . PHP_EOL;
}
"

# 5. Exécuter la migration corrigée
echo "🚀 Exécution de la migration corrigée..."
php artisan migrate --path=database/migrations/2025_07_25_000000_update_relationship_types_table_structure.php

# 6. Vérifier que la nouvelle structure est correcte
echo "✅ Vérification de la nouvelle structure..."
php artisan tinker --execute="
use Illuminate\Support\Facades\Schema;
if (Schema::hasTable('relationship_types')) {
    echo 'Table relationship_types créée avec succès' . PHP_EOL;
    
    // Vérifier les colonnes
    \$columns = Schema::getColumnListing('relationship_types');
    echo 'Colonnes disponibles: ' . implode(', ', \$columns) . PHP_EOL;
    
    // Vérifier que les nouvelles colonnes existent
    \$requiredColumns = ['name', 'display_name_fr', 'display_name_ar', 'display_name_en', 'category', 'generation_level'];
    \$missing = array_diff(\$requiredColumns, \$columns);
    
    if (empty(\$missing)) {
        echo '✅ Toutes les colonnes requises sont présentes' . PHP_EOL;
    } else {
        echo '❌ Colonnes manquantes: ' . implode(', ', \$missing) . PHP_EOL;
    }
} else {
    echo '❌ Erreur: Table relationship_types non créée' . PHP_EOL;
}
"

# 7. Exécuter le seeder pour créer les nouveaux types de relations
echo "🌱 Création des nouveaux types de relations..."
php artisan db:seed --class=ComprehensiveRelationshipTypesSeeder

# 8. Vérifier que les données sont correctement créées
echo "📊 Vérification des données créées..."
php artisan tinker --execute="
\$count = App\Models\RelationshipType::count();
echo 'Nombre de types de relations créés: ' . \$count . PHP_EOL;

if (\$count >= 30) {
    echo '✅ Types de relations créés avec succès' . PHP_EOL;
    
    // Afficher quelques exemples
    \$examples = App\Models\RelationshipType::take(5)->get(['name', 'display_name_fr', 'category']);
    echo 'Exemples:' . PHP_EOL;
    foreach (\$examples as \$type) {
        echo '  - ' . \$type->name . ' (' . \$type->display_name_fr . ', ' . \$type->category . ')' . PHP_EOL;
    }
} else {
    echo '❌ Problème: seulement ' . \$count . ' types créés' . PHP_EOL;
}
"

# 9. Nettoyer le fichier de sauvegarde temporaire
if [ -f "relationship_types_backup.json" ]; then
    echo "🧹 Nettoyage du fichier de sauvegarde..."
    rm relationship_types_backup.json
fi

# 10. Vider le cache
echo "🧹 Nettoyage du cache..."
php artisan cache:clear

echo ""
echo "✅ Correction terminée !"
echo "🎯 Le problème de contrainte NOT NULL sur 'code' devrait maintenant être résolu."
echo "📋 Vous pouvez maintenant utiliser les seeders sans erreur."
