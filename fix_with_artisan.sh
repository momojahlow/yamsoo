#!/bin/bash

echo "🔧 Correction du problème de contrainte NOT NULL avec Artisan..."

echo ""
echo "🗑️ Suppression de toutes les migrations relationship_types..."
php artisan migrate:rollback --step=10

echo ""
echo "🧹 Nettoyage complet de la base de données..."
php artisan migrate:fresh --force

echo ""
echo "🏗️ Exécution de la nouvelle migration..."
php artisan migrate --path=database/migrations/2025_07_28_000000_fix_relationship_types_structure.php

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
php artisan tinker --execute="echo 'Nombre de types de relations: ' . App\Models\RelationshipType::count() . PHP_EOL;"
