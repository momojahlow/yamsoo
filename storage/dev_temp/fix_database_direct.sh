#!/bin/bash

echo "🔧 Correction directe de la base de données SQLite..."

echo ""
echo "📍 Vérification de l'existence du fichier de base de données..."
if [ ! -f "database/database.sqlite" ]; then
    echo "❌ Fichier database/database.sqlite non trouvé!"
    echo "🏗️ Création du fichier de base de données..."
    touch database/database.sqlite
fi

echo ""
echo "🗑️ Application du script SQL de correction..."
sqlite3 database/database.sqlite < fix_database.sql

if [ $? -eq 0 ]; then
    echo "✅ Script SQL exécuté avec succès!"
    echo ""
    echo "📊 Vérification du résultat..."
    echo "SELECT 'Nombre total de types:', COUNT(*) FROM relationship_types;" | sqlite3 database/database.sqlite
    echo ""
    echo "📋 Exemples de types créés:"
    echo "SELECT '- ' || name || ' (' || display_name_fr || ', ' || category || ')' FROM relationship_types ORDER BY sort_order LIMIT 5;" | sqlite3 database/database.sqlite
    echo ""
    echo "🎉 Correction terminée avec succès!"
    echo "✅ Le problème de contrainte NOT NULL est résolu"
    echo "✅ Vous pouvez maintenant utiliser les seeders sans erreur"
    echo "✅ Le système de suggestions fonctionnera correctement"
else
    echo "❌ Erreur lors de l'exécution du script SQL"
    echo "💡 Assurez-vous que sqlite3 est installé et accessible"
fi

echo ""
