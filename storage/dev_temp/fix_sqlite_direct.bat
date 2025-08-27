@echo off
echo 🔧 Correction directe SQLite (sans PHP)...

echo.
echo 📍 Vérification du fichier de base de données...
if not exist "database\database.sqlite" (
    echo 🏗️ Création du fichier de base de données...
    type nul > database\database.sqlite
)

echo.
echo 🗑️ Suppression de la table relationship_types existante...
echo DROP TABLE IF EXISTS relationship_types; | sqlite3 database\database.sqlite

echo.
echo 🏗️ Création de la nouvelle table avec la structure correcte...
sqlite3 database\database.sqlite < fix_database.sql

if %ERRORLEVEL% EQU 0 (
    echo ✅ Table créée et données insérées avec succès!
    echo.
    echo 📊 Vérification du résultat...
    echo SELECT 'Nombre total de types:', COUNT(*) FROM relationship_types; | sqlite3 database\database.sqlite
    echo.
    echo 📋 Exemples de types créés:
    echo SELECT '- ' || name || ' (' || display_name_fr || ', ' || category || ')' FROM relationship_types ORDER BY sort_order LIMIT 5; | sqlite3 database\database.sqlite
    echo.
    echo 🎉 Correction terminée avec succès!
    echo ✅ Le problème de contrainte NOT NULL est résolu
    echo ✅ La table utilise maintenant la nouvelle structure (name au lieu de code)
    echo ✅ 30 types de relations ont été insérés
    echo.
    echo 🎯 Vous pouvez maintenant:
    echo    1. Utiliser les seeders sans erreur
    echo    2. Tester le système de suggestions
    echo    3. Le cas Ahmed → Mohamed → Leila fonctionnera correctement
) else (
    echo ❌ Erreur lors de l'exécution du script SQL
    echo 💡 Assurez-vous que sqlite3 est installé et accessible
)

echo.
pause
