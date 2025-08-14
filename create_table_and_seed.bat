@echo off
echo 🔧 Création de la table et insertion des données...

echo.
echo 📍 Vérification du fichier de base de données...
if not exist "database\database.sqlite" (
    echo 🏗️ Création du fichier de base de données...
    type nul > database\database.sqlite
)

echo.
echo 🏗️ Création de la table relationship_types...
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
    echo 🎉 Table créée avec succès!
    echo ✅ 30 types de relations ont été insérés
    echo ✅ Structure correcte (name au lieu de code)
    echo.
    echo 🧪 Test de la structure...
    echo PRAGMA table_info(relationship_types); | sqlite3 database\database.sqlite
    echo.
    echo 🎯 Maintenant vous pouvez:
    echo    1. Exécuter les seeders sans erreur
    echo    2. Tester le système de suggestions
    echo    3. Le cas Ahmed → Mohamed → Leila fonctionnera correctement
) else (
    echo ❌ Erreur lors de l'exécution du script SQL
    echo 💡 Assurez-vous que sqlite3 est installé et accessible
)

echo.
pause
