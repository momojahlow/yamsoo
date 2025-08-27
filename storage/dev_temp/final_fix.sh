#!/bin/bash

echo "🔧 Correction finale du problème de contrainte NOT NULL..."

echo ""
echo "🧹 Nettoyage complet de la base de données..."
php artisan migrate:fresh --force

echo ""
echo "🏗️ Exécution de la migration corrigée..."
php artisan migrate

echo ""
echo "🌱 Insertion des types de relations..."
php artisan db:seed --class=ComprehensiveRelationshipTypesSeeder

echo ""
echo "🧹 Nettoyage du cache..."
php artisan cache:clear

echo ""
echo "✅ Correction terminée!"
echo "🎯 Le problème de contrainte NOT NULL devrait être résolu."
echo ""
echo "📊 Vérification..."
php artisan tinker --execute="echo 'Nombre de types de relations: ' . App\Models\RelationshipType::count() . PHP_EOL; echo 'Exemples:' . PHP_EOL; App\Models\RelationshipType::take(3)->get(['name', 'display_name_fr'])->each(function(\$type) { echo '- ' . \$type->name . ' (' . \$type->display_name_fr . ')' . PHP_EOL; });"
