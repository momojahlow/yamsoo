<?php

/**
 * SIMULATION FAMILLE AHMED VIA ARTISAN TINKER
 * Copiez-collez ces commandes dans php artisan tinker
 */

echo "🎬 SIMULATION FAMILLE AHMED - COMMANDES ARTISAN TINKER\n";
echo str_repeat("=", 80) . "\n\n";

echo "Ouvrez un terminal et exécutez : php artisan tinker\n";
echo "Puis copiez-collez les blocs de commandes suivants :\n\n";

echo "// ===== ÉTAPE 0: PRÉPARATION =====\n";
echo "echo \"🧹 Nettoyage de la base...\";\n";
echo "App\\Models\\Suggestion::truncate();\n";
echo "App\\Models\\FamilyRelationship::truncate();\n";
echo "echo \"✅ Base nettoyée\\n\";\n\n";

echo "// Charger les utilisateurs\n";
echo "\$ahmed = App\\Models\\User::where('name', 'like', '%Ahmed%')->first();\n";
echo "\$fatima = App\\Models\\User::where('name', 'like', '%Fatima%')->first();\n";
echo "\$mohamed = App\\Models\\User::where('name', 'like', '%Mohammed%')->first();\n";
echo "\$amina = App\\Models\\User::where('name', 'like', '%Amina%')->first();\n";
echo "\$youssef = App\\Models\\User::where('name', 'like', '%Youssef%')->first();\n\n";

echo "// Vérifier les utilisateurs\n";
echo "echo \"👥 Utilisateurs chargés:\\n\";\n";
echo "echo \"   Ahmed: \" . (\$ahmed ? \$ahmed->name : 'NON TROUVÉ') . \"\\n\";\n";
echo "echo \"   Fatima: \" . (\$fatima ? \$fatima->name : 'NON TROUVÉ') . \"\\n\";\n";
echo "echo \"   Mohamed: \" . (\$mohamed ? \$mohamed->name : 'NON TROUVÉ') . \"\\n\";\n";
echo "echo \"   Amina: \" . (\$amina ? \$amina->name : 'NON TROUVÉ') . \"\\n\";\n";
echo "echo \"   Youssef: \" . (\$youssef ? \$youssef->name : 'NON TROUVÉ') . \"\\n\";\n\n";

echo "// Charger les types de relations\n";
echo "\$husband = App\\Models\\RelationshipType::where('name', 'husband')->first();\n";
echo "\$wife = App\\Models\\RelationshipType::where('name', 'wife')->first();\n";
echo "\$father = App\\Models\\RelationshipType::where('name', 'father')->first();\n";
echo "\$mother = App\\Models\\RelationshipType::where('name', 'mother')->first();\n";
echo "\$son = App\\Models\\RelationshipType::where('name', 'son')->first();\n";
echo "\$daughter = App\\Models\\RelationshipType::where('name', 'daughter')->first();\n\n";

echo str_repeat("-", 80) . "\n\n";

echo "// ===== ÉTAPE 1: AHMED + FATIMA (ÉPOUX) =====\n";
echo "echo \"💑 ÉTAPE 1: Ahmed ajoute Fatima comme épouse\\n\";\n\n";

echo "// Créer la relation Ahmed → Fatima (husband)\n";
echo "App\\Models\\FamilyRelationship::create([\n";
echo "    'user_id' => \$ahmed->id,\n";
echo "    'related_user_id' => \$fatima->id,\n";
echo "    'relationship_type_id' => \$husband->id,\n";
echo "    'status' => 'accepted'\n";
echo "]);\n\n";

echo "// Créer la relation inverse Fatima → Ahmed (wife)\n";
echo "App\\Models\\FamilyRelationship::create([\n";
echo "    'user_id' => \$fatima->id,\n";
echo "    'related_user_id' => \$ahmed->id,\n";
echo "    'relationship_type_id' => \$wife->id,\n";
echo "    'status' => 'accepted'\n";
echo "]);\n\n";

echo "echo \"✅ Relations créées: Ahmed ↔ Fatima (époux)\\n\";\n\n";

echo str_repeat("-", 80) . "\n\n";

echo "// ===== ÉTAPE 2: AHMED + AMINA (PÈRE/FILLE) =====\n";
echo "echo \"👧 ÉTAPE 2: Ahmed ajoute Amina comme fille\\n\";\n\n";

echo "// Créer la relation Ahmed → Amina (father)\n";
echo "App\\Models\\FamilyRelationship::create([\n";
echo "    'user_id' => \$ahmed->id,\n";
echo "    'related_user_id' => \$amina->id,\n";
echo "    'relationship_type_id' => \$father->id,\n";
echo "    'status' => 'accepted'\n";
echo "]);\n\n";

echo "// Créer la relation inverse Amina → Ahmed (daughter)\n";
echo "App\\Models\\FamilyRelationship::create([\n";
echo "    'user_id' => \$amina->id,\n";
echo "    'related_user_id' => \$ahmed->id,\n";
echo "    'relationship_type_id' => \$daughter->id,\n";
echo "    'status' => 'accepted'\n";
echo "]);\n\n";

echo "echo \"✅ Relations créées: Ahmed ↔ Amina (père/fille)\\n\";\n\n";

echo "// TESTER LES SUGGESTIONS APRÈS ÉTAPE 2\n";
echo "echo \"🧪 Test suggestions pour Fatima (devrait voir Amina comme fille):\\n\";\n";
echo "App\\Models\\Suggestion::where('user_id', \$fatima->id)->delete();\n";
echo "\$suggestionService = app(App\\Services\\SuggestionService::class);\n";
echo "\$suggestions = \$suggestionService->generateSuggestions(\$fatima);\n";
echo "foreach (\$suggestions as \$suggestion) {\n";
echo "    echo \"   - {\$suggestion->suggestedUser->name} : {\$suggestion->suggested_relation_code}\\n\";\n";
echo "}\n\n";

echo "echo \"🧪 Test suggestions pour Amina (devrait voir Fatima comme mère):\\n\";\n";
echo "App\\Models\\Suggestion::where('user_id', \$amina->id)->delete();\n";
echo "\$suggestions = \$suggestionService->generateSuggestions(\$amina);\n";
echo "foreach (\$suggestions as \$suggestion) {\n";
echo "    echo \"   - {\$suggestion->suggestedUser->name} : {\$suggestion->suggested_relation_code}\\n\";\n";
echo "}\n\n";

echo str_repeat("-", 80) . "\n\n";

echo "// ===== ÉTAPE 3: AHMED + MOHAMED (PÈRE/FILS) =====\n";
echo "echo \"👦 ÉTAPE 3: Ahmed ajoute Mohamed comme fils\\n\";\n\n";

echo "// Créer la relation Ahmed → Mohamed (father)\n";
echo "App\\Models\\FamilyRelationship::create([\n";
echo "    'user_id' => \$ahmed->id,\n";
echo "    'related_user_id' => \$mohamed->id,\n";
echo "    'relationship_type_id' => \$father->id,\n";
echo "    'status' => 'accepted'\n";
echo "]);\n\n";

echo "// Créer la relation inverse Mohamed → Ahmed (son)\n";
echo "App\\Models\\FamilyRelationship::create([\n";
echo "    'user_id' => \$mohamed->id,\n";
echo "    'related_user_id' => \$ahmed->id,\n";
echo "    'relationship_type_id' => \$son->id,\n";
echo "    'status' => 'accepted'\n";
echo "]);\n\n";

echo "echo \"✅ Relations créées: Ahmed ↔ Mohamed (père/fils)\\n\";\n\n";

echo "// TESTER LES SUGGESTIONS APRÈS ÉTAPE 3\n";
echo "echo \"🧪 Test suggestions pour Mohamed (devrait voir Fatima comme mère, Amina comme sœur):\\n\";\n";
echo "App\\Models\\Suggestion::where('user_id', \$mohamed->id)->delete();\n";
echo "\$suggestions = \$suggestionService->generateSuggestions(\$mohamed);\n";
echo "foreach (\$suggestions as \$suggestion) {\n";
echo "    \$expected = '';\n";
echo "    if (\$suggestion->suggested_user_id === \$fatima->id) \$expected = ' (attendu: mother)';\n";
echo "    if (\$suggestion->suggested_user_id === \$amina->id) \$expected = ' (attendu: sister)';\n";
echo "    \$status = '';\n";
echo "    if (\$suggestion->suggested_user_id === \$fatima->id && \$suggestion->suggested_relation_code === 'mother') \$status = ' ✅';\n";
echo "    elseif (\$suggestion->suggested_user_id === \$amina->id && \$suggestion->suggested_relation_code === 'sister') \$status = ' ✅';\n";
echo "    elseif (\$suggestion->suggested_user_id === \$fatima->id || \$suggestion->suggested_user_id === \$amina->id) \$status = ' ❌';\n";
echo "    echo \"   - {\$suggestion->suggestedUser->name} : {\$suggestion->suggested_relation_code}{\$expected}{\$status}\\n\";\n";
echo "}\n\n";

echo str_repeat("-", 80) . "\n\n";

echo "// ===== ÉTAPE 4: AHMED + YOUSSEF (PÈRE/FILS) =====\n";
echo "echo \"👦 ÉTAPE 4: Ahmed ajoute Youssef comme fils\\n\";\n\n";

echo "// Créer la relation Ahmed → Youssef (father)\n";
echo "App\\Models\\FamilyRelationship::create([\n";
echo "    'user_id' => \$ahmed->id,\n";
echo "    'related_user_id' => \$youssef->id,\n";
echo "    'relationship_type_id' => \$father->id,\n";
echo "    'status' => 'accepted'\n";
echo "]);\n\n";

echo "// Créer la relation inverse Youssef → Ahmed (son)\n";
echo "App\\Models\\FamilyRelationship::create([\n";
echo "    'user_id' => \$youssef->id,\n";
echo "    'related_user_id' => \$ahmed->id,\n";
echo "    'relationship_type_id' => \$son->id,\n";
echo "    'status' => 'accepted'\n";
echo "]);\n\n";

echo "echo \"✅ Relations créées: Ahmed ↔ Youssef (père/fils)\\n\";\n\n";

echo str_repeat("-", 80) . "\n\n";

echo "// ===== ÉTAPE 5: TESTS FINAUX COMPLETS =====\n";
echo "echo \"🎯 TESTS FINAUX - Vérification de toutes les suggestions\\n\";\n\n";

echo "// Test pour Mohamed (le cas problématique original)\n";
echo "echo \"🧪 MOHAMED - Suggestions attendues: Fatima=mother, Amina=sister, Youssef=brother\\n\";\n";
echo "App\\Models\\Suggestion::where('user_id', \$mohamed->id)->delete();\n";
echo "\$suggestions = \$suggestionService->generateSuggestions(\$mohamed);\n";
echo "\$correctCount = 0;\n";
echo "\$incorrectCount = 0;\n";
echo "foreach (\$suggestions as \$suggestion) {\n";
echo "    \$expected = '';\n";
echo "    \$correct = false;\n";
echo "    if (\$suggestion->suggested_user_id === \$fatima->id) {\n";
echo "        \$expected = 'mother';\n";
echo "        \$correct = \$suggestion->suggested_relation_code === 'mother';\n";
echo "    } elseif (\$suggestion->suggested_user_id === \$amina->id) {\n";
echo "        \$expected = 'sister';\n";
echo "        \$correct = \$suggestion->suggested_relation_code === 'sister';\n";
echo "    } elseif (\$suggestion->suggested_user_id === \$youssef->id) {\n";
echo "        \$expected = 'brother';\n";
echo "        \$correct = \$suggestion->suggested_relation_code === 'brother';\n";
echo "    }\n";
echo "    \$status = \$correct ? '✅' : '❌';\n";
echo "    if (\$correct) \$correctCount++; else \$incorrectCount++;\n";
echo "    echo \"   - {\$suggestion->suggestedUser->name} : {\$suggestion->suggested_relation_code} (attendu: {\$expected}) {\$status}\\n\";\n";
echo "}\n";
echo "echo \"📊 Mohamed: {\$correctCount} correct(s), {\$incorrectCount} incorrect(s)\\n\\n\";\n\n";

echo "// Test pour Amina\n";
echo "echo \"🧪 AMINA - Suggestions attendues: Fatima=mother, Mohamed=brother, Youssef=brother\\n\";\n";
echo "App\\Models\\Suggestion::where('user_id', \$amina->id)->delete();\n";
echo "\$suggestions = \$suggestionService->generateSuggestions(\$amina);\n";
echo "\$correctCount = 0;\n";
echo "\$incorrectCount = 0;\n";
echo "foreach (\$suggestions as \$suggestion) {\n";
echo "    \$expected = '';\n";
echo "    \$correct = false;\n";
echo "    if (\$suggestion->suggested_user_id === \$fatima->id) {\n";
echo "        \$expected = 'mother';\n";
echo "        \$correct = \$suggestion->suggested_relation_code === 'mother';\n";
echo "    } elseif (\$suggestion->suggested_user_id === \$mohamed->id) {\n";
echo "        \$expected = 'brother';\n";
echo "        \$correct = \$suggestion->suggested_relation_code === 'brother';\n";
echo "    } elseif (\$suggestion->suggested_user_id === \$youssef->id) {\n";
echo "        \$expected = 'brother';\n";
echo "        \$correct = \$suggestion->suggested_relation_code === 'brother';\n";
echo "    }\n";
echo "    \$status = \$correct ? '✅' : '❌';\n";
echo "    if (\$correct) \$correctCount++; else \$incorrectCount++;\n";
echo "    echo \"   - {\$suggestion->suggestedUser->name} : {\$suggestion->suggested_relation_code} (attendu: {\$expected}) {\$status}\\n\";\n";
echo "}\n";
echo "echo \"📊 Amina: {\$correctCount} correct(s), {\$incorrectCount} incorrect(s)\\n\\n\";\n\n";

echo "// Test pour Fatima\n";
echo "echo \"🧪 FATIMA - Suggestions attendues: Amina=daughter, Mohamed=son, Youssef=son\\n\";\n";
echo "App\\Models\\Suggestion::where('user_id', \$fatima->id)->delete();\n";
echo "\$suggestions = \$suggestionService->generateSuggestions(\$fatima);\n";
echo "\$correctCount = 0;\n";
echo "\$incorrectCount = 0;\n";
echo "foreach (\$suggestions as \$suggestion) {\n";
echo "    \$expected = '';\n";
echo "    \$correct = false;\n";
echo "    if (\$suggestion->suggested_user_id === \$amina->id) {\n";
echo "        \$expected = 'daughter';\n";
echo "        \$correct = \$suggestion->suggested_relation_code === 'daughter';\n";
echo "    } elseif (\$suggestion->suggested_user_id === \$mohamed->id) {\n";
echo "        \$expected = 'son';\n";
echo "        \$correct = \$suggestion->suggested_relation_code === 'son';\n";
echo "    } elseif (\$suggestion->suggested_user_id === \$youssef->id) {\n";
echo "        \$expected = 'son';\n";
echo "        \$correct = \$suggestion->suggested_relation_code === 'son';\n";
echo "    }\n";
echo "    \$status = \$correct ? '✅' : '❌';\n";
echo "    if (\$correct) \$correctCount++; else \$incorrectCount++;\n";
echo "    echo \"   - {\$suggestion->suggestedUser->name} : {\$suggestion->suggested_relation_code} (attendu: {\$expected}) {\$status}\\n\";\n";
echo "}\n";
echo "echo \"📊 Fatima: {\$correctCount} correct(s), {\$incorrectCount} incorrect(s)\\n\\n\";\n\n";

echo str_repeat("-", 80) . "\n\n";

echo "// ===== RAPPORT FINAL =====\n";
echo "echo \"📊 RAPPORT FINAL DE LA SIMULATION\\n\";\n";
echo "echo \"Structure familiale créée:\\n\";\n";
echo "\$relations = App\\Models\\FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();\n";
echo "foreach (\$relations as \$relation) {\n";
echo "    echo \"   - {\$relation->user->name} → {\$relation->relatedUser->name} : {\$relation->relationshipType->display_name_fr}\\n\";\n";
echo "}\n\n";

echo "echo \"\\n🎯 RÉSUMÉ:\\n\";\n";
echo "echo \"Si toutes les suggestions sont correctes (✅), alors le système fonctionne parfaitement !\\n\";\n";
echo "echo \"Si des suggestions sont incorrectes (❌), alors il faut encore corriger la logique.\\n\";\n\n";

echo str_repeat("=", 80) . "\n";
echo "INSTRUCTIONS D'UTILISATION :\n";
echo "1. Ouvrez un terminal dans le projet\n";
echo "2. Exécutez : php artisan tinker\n";
echo "3. Copiez-collez chaque bloc de commandes ci-dessus\n";
echo "4. Observez les résultats après chaque étape\n";
echo "5. Vérifiez que toutes les suggestions sont correctes (✅)\n\n";

echo "RÉSULTATS ATTENDUS :\n";
echo "- Mohamed devrait voir : Fatima=mother ✅, Amina=sister ✅, Youssef=brother ✅\n";
echo "- Amina devrait voir : Fatima=mother ✅, Mohamed=brother ✅, Youssef=brother ✅\n";
echo "- Fatima devrait voir : Amina=daughter ✅, Mohamed=son ✅, Youssef=son ✅\n\n";

echo "Si ces résultats sont obtenus, le système de suggestions familiales\n";
echo "fonctionne parfaitement ! 🎉\n";
