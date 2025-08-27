<?php

/**
 * DEBUG DIRECT VIA SQL - CONTOURNE LES PROBLÈMES PHP
 */

echo "🔍 DEBUG DIRECT VIA SQL\n";
echo str_repeat("=", 60) . "\n\n";

// Configuration de la base de données (ajustez selon votre config)
$dbPath = __DIR__ . '/database/database.sqlite';

if (!file_exists($dbPath)) {
    echo "❌ Base de données non trouvée: {$dbPath}\n";
    exit(1);
}

try {
    $pdo = new PDO("sqlite:{$dbPath}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // 1. Lister tous les utilisateurs
    echo "1. 👥 TOUS LES UTILISATEURS:\n";
    $stmt = $pdo->query("SELECT id, name, email FROM users ORDER BY name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $amina_id = null;
    $fatima_id = null;
    $ahmed_id = null;
    $mohamed_id = null;
    
    foreach ($users as $user) {
        echo "   {$user['name']} (ID: {$user['id']}) - {$user['email']}\n";
        
        if (stripos($user['name'], 'Amina') !== false) $amina_id = $user['id'];
        if (stripos($user['name'], 'Fatima') !== false) $fatima_id = $user['id'];
        if (stripos($user['name'], 'Ahmed') !== false) $ahmed_id = $user['id'];
        if (stripos($user['name'], 'Mohammed') !== false) $mohamed_id = $user['id'];
    }
    
    echo "\n🎯 Utilisateurs clés identifiés:\n";
    echo "   Amina ID: " . ($amina_id ?? "NON TROUVÉ") . "\n";
    echo "   Fatima ID: " . ($fatima_id ?? "NON TROUVÉ") . "\n";
    echo "   Ahmed ID: " . ($ahmed_id ?? "NON TROUVÉ") . "\n";
    echo "   Mohamed ID: " . ($mohamed_id ?? "NON TROUVÉ") . "\n\n";
    
    // 2. Lister tous les types de relations
    echo "2. 🔗 TYPES DE RELATIONS DISPONIBLES:\n";
    $stmt = $pdo->query("SELECT id, code, name, name_fr FROM relationship_types ORDER BY code");
    $relationTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($relationTypes as $type) {
        echo "   {$type['code']} - {$type['name']} ({$type['name_fr']})\n";
    }
    
    // 3. Lister TOUTES les relations existantes
    echo "\n3. 🔗 TOUTES LES RELATIONS EXISTANTES:\n";
    $stmt = $pdo->query("
        SELECT 
            fr.id,
            u1.name as user_name,
            u2.name as related_user_name,
            rt.code as relation_code,
            rt.name as relation_name,
            fr.status
        FROM family_relationships fr
        JOIN users u1 ON fr.user_id = u1.id
        JOIN users u2 ON fr.related_user_id = u2.id
        JOIN relationship_types rt ON fr.relationship_type_id = rt.id
        ORDER BY u1.name, u2.name
    ");
    $relations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($relations)) {
        echo "   ❌ AUCUNE RELATION TROUVÉE!\n";
    } else {
        foreach ($relations as $rel) {
            echo "   {$rel['user_name']} → {$rel['related_user_name']} : {$rel['relation_code']} ({$rel['relation_name']}) [{$rel['status']}]\n";
        }
    }
    
    // 4. Relations spécifiques d'Amina
    if ($amina_id) {
        echo "\n4. 🎯 RELATIONS SPÉCIFIQUES D'AMINA:\n";
        $stmt = $pdo->prepare("
            SELECT 
                u1.name as user_name,
                u2.name as related_user_name,
                rt.code as relation_code,
                rt.name as relation_name,
                CASE 
                    WHEN fr.user_id = ? THEN 'Amina → ' || u2.name
                    ELSE u1.name || ' → Amina'
                END as direction
            FROM family_relationships fr
            JOIN users u1 ON fr.user_id = u1.id
            JOIN users u2 ON fr.related_user_id = u2.id
            JOIN relationship_types rt ON fr.relationship_type_id = rt.id
            WHERE fr.user_id = ? OR fr.related_user_id = ?
            ORDER BY direction
        ");
        $stmt->execute([$amina_id, $amina_id, $amina_id]);
        $aminaRelations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($aminaRelations)) {
            echo "   ❌ AUCUNE RELATION POUR AMINA!\n";
        } else {
            foreach ($aminaRelations as $rel) {
                echo "   {$rel['direction']} : {$rel['relation_code']}\n";
            }
        }
    }
    
    // 5. Vérification spécifique Ahmed ↔ Fatima
    if ($ahmed_id && $fatima_id) {
        echo "\n5. 🔍 RELATION AHMED ↔ FATIMA:\n";
        $stmt = $pdo->prepare("
            SELECT 
                u1.name as user_name,
                u2.name as related_user_name,
                rt.code as relation_code,
                rt.name as relation_name
            FROM family_relationships fr
            JOIN users u1 ON fr.user_id = u1.id
            JOIN users u2 ON fr.related_user_id = u2.id
            JOIN relationship_types rt ON fr.relationship_type_id = rt.id
            WHERE (fr.user_id = ? AND fr.related_user_id = ?)
               OR (fr.user_id = ? AND fr.related_user_id = ?)
        ");
        $stmt->execute([$ahmed_id, $fatima_id, $fatima_id, $ahmed_id]);
        $ahmedFatimaRelation = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($ahmedFatimaRelation)) {
            echo "   ❌ AUCUNE RELATION AHMED ↔ FATIMA TROUVÉE!\n";
        } else {
            foreach ($ahmedFatimaRelation as $rel) {
                echo "   ✅ {$rel['user_name']} → {$rel['related_user_name']} : {$rel['relation_code']}\n";
            }
        }
    }
    
    // 6. Vérification spécifique Amina ↔ Ahmed
    if ($amina_id && $ahmed_id) {
        echo "\n6. 🔍 RELATION AMINA ↔ AHMED:\n";
        $stmt = $pdo->prepare("
            SELECT 
                u1.name as user_name,
                u2.name as related_user_name,
                rt.code as relation_code,
                rt.name as relation_name
            FROM family_relationships fr
            JOIN users u1 ON fr.user_id = u1.id
            JOIN users u2 ON fr.related_user_id = u2.id
            JOIN relationship_types rt ON fr.relationship_type_id = rt.id
            WHERE (fr.user_id = ? AND fr.related_user_id = ?)
               OR (fr.user_id = ? AND fr.related_user_id = ?)
        ");
        $stmt->execute([$amina_id, $ahmed_id, $ahmed_id, $amina_id]);
        $aminaAhmedRelation = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($aminaAhmedRelation)) {
            echo "   ❌ AUCUNE RELATION AMINA ↔ AHMED TROUVÉE!\n";
        } else {
            foreach ($aminaAhmedRelation as $rel) {
                echo "   ✅ {$rel['user_name']} → {$rel['related_user_name']} : {$rel['relation_code']}\n";
            }
        }
    }
    
    // 7. Suggestions actuelles pour Amina
    if ($amina_id) {
        echo "\n7. 💡 SUGGESTIONS ACTUELLES POUR AMINA:\n";
        $stmt = $pdo->prepare("
            SELECT 
                u.name as suggested_user_name,
                s.suggested_relation_code,
                s.reason,
                s.type
            FROM suggestions s
            JOIN users u ON s.suggested_user_id = u.id
            WHERE s.user_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$amina_id]);
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($suggestions)) {
            echo "   ❌ AUCUNE SUGGESTION POUR AMINA!\n";
        } else {
            foreach ($suggestions as $suggestion) {
                echo "   - {$suggestion['suggested_user_name']} : {$suggestion['suggested_relation_code']}\n";
                echo "     Raison: {$suggestion['reason']}\n";
                echo "     Type: {$suggestion['type']}\n";
                
                if (stripos($suggestion['suggested_user_name'], 'Fatima') !== false) {
                    echo "     🎯 FATIMA TROUVÉE: {$suggestion['suggested_relation_code']}\n";
                    if ($suggestion['suggested_relation_code'] === 'mother') {
                        echo "     ✅ CORRECT!\n";
                    } else {
                        echo "     ❌ INCORRECT! Devrait être 'mother'\n";
                    }
                }
            }
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🧠 ANALYSE:\n";
    echo "Pour que Fatima soit suggérée comme 'mother' à Amina, il faut:\n";
    echo "1. ✓ Amina → Ahmed : daughter (fille)\n";
    echo "2. ✓ Ahmed → Fatima : husband (mari)\n";
    echo "3. ✓ DÉDUCTION: Amina (enfant) + Fatima (conjoint) = Fatima est mère\n";
    echo "4. ✓ CAS 1 dans SuggestionService: enfant + conjoint → parent\n";
    echo "5. ✓ RÉSULTAT ATTENDU: mother\n\n";
    
    echo "Si le problème persiste, c'est dans la logique de SuggestionService.php\n";
    echo "Debug terminé.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
