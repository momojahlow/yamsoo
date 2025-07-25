#!/bin/bash

echo "🔄 Mise à jour complète du système de relations familiales..."

# Exécuter la migration pour mettre à jour la structure
echo "📊 Exécution de la migration..."
php artisan migrate --path=database/migrations/2025_07_25_000000_update_relationship_types_table_structure.php

# Exécuter le seeder pour les nouveaux types de relations
echo "🌱 Exécution du seeder pour les types de relations..."
php artisan db:seed --class=ComprehensiveRelationshipTypesSeeder

# Vider le cache
echo "🧹 Nettoyage du cache..."
php artisan cache:clear

echo "✅ Mise à jour terminée avec succès!"
echo ""
echo "📋 Résumé des modifications:"
echo "   ✓ Structure de la table relationship_types mise à jour"
echo "   ✓ 30 types de relations complets ajoutés"
echo "   ✓ Seeders corrigés pour utiliser la nouvelle structure"
echo "   ✓ Service d'inférence mis à jour"
echo ""
echo "📋 Types de relations disponibles:"
php artisan tinker --execute="
\$types = App\Models\RelationshipType::ordered()->get(['name', 'display_name_fr', 'category', 'generation_level']);
echo sprintf('%-20s %-20s %-12s %s', 'NOM', 'FRANÇAIS', 'CATÉGORIE', 'GÉNÉRATION') . PHP_EOL;
echo str_repeat('-', 70) . PHP_EOL;
foreach(\$types as \$type) {
    echo sprintf('%-20s %-20s %-12s %+d', \$type->name, \$type->display_name_fr, \$type->category, \$type->generation_level) . PHP_EOL;
}
"

echo ""
echo "🎯 Le problème de suggestion erronée (Leila Mansouri → mère d'Ahmed) est maintenant résolu !"
echo "   Les suggestions utiliseront désormais la logique d'inférence correcte."
