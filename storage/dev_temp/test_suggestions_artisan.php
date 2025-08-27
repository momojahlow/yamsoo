<?php

/**
 * Test des suggestions via Artisan Tinker
 * Ce script peut être copié-collé dans artisan tinker
 */

echo "🧪 SCRIPT POUR ARTISAN TINKER\n";
echo str_repeat("=", 60) . "\n\n";

echo "Copiez et collez les commandes suivantes dans 'php artisan tinker' :\n\n";

echo "// 1. Charger les utilisateurs\n";
echo "\$mohammed = App\\Models\\User::where('name', 'like', '%Mohammed%')->first();\n";
echo "\$amina = App\\Models\\User::where('name', 'like', '%Amina%')->first();\n";
echo "\$youssef = App\\Models\\User::where('name', 'like', '%Youssef%')->first();\n";
echo "\$fatima = App\\Models\\User::where('name', 'like', '%Fatima%')->first();\n";
echo "\$ahmed = App\\Models\\User::where('name', 'like', '%Ahmed%')->first();\n\n";

echo "// 2. Vérifier que les utilisateurs existent\n";
echo "echo \"Mohammed: \" . (\$mohammed ? \$mohammed->name : 'NON TROUVÉ') . \"\\n\";\n";
echo "echo \"Amina: \" . (\$amina ? \$amina->name : 'NON TROUVÉ') . \"\\n\";\n";
echo "echo \"Youssef: \" . (\$youssef ? \$youssef->name : 'NON TROUVÉ') . \"\\n\";\n";
echo "echo \"Fatima: \" . (\$fatima ? \$fatima->name : 'NON TROUVÉ') . \"\\n\";\n";
echo "echo \"Ahmed: \" . (\$ahmed ? \$ahmed->name : 'NON TROUVÉ') . \"\\n\";\n\n";

echo "// 3. Examiner les relations existantes\n";
echo "echo \"\\n🔗 Relations existantes:\\n\";\n";
echo "\$relations = App\\Models\\FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();\n";
echo "foreach (\$relations as \$relation) {\n";
echo "    echo \"   - {\$relation->user->name} → {\$relation->relatedUser->name} : {\$relation->relationshipType->display_name_fr} ({\$relation->relationshipType->name})\\n\";\n";
echo "}\n\n";

echo "// 4. Supprimer les anciennes suggestions de Mohammed\n";
echo "App\\Models\\Suggestion::where('user_id', \$mohammed->id)->delete();\n";
echo "echo \"\\n🗑️ Anciennes suggestions supprimées\\n\";\n\n";

echo "// 5. Générer de nouvelles suggestions avec debug\n";
echo "echo \"\\n🧪 Génération des suggestions pour Mohammed...\\n\";\n";
echo "\$suggestionService = app(App\\Services\\SuggestionService::class);\n";
echo "\$suggestions = \$suggestionService->generateSuggestions(\$mohammed);\n\n";

echo "// 6. Afficher les résultats\n";
echo "echo \"\\n💡 Suggestions générées: \" . \$suggestions->count() . \"\\n\";\n";
echo "foreach (\$suggestions as \$suggestion) {\n";
echo "    \$relationshipType = App\\Models\\RelationshipType::where('name', \$suggestion->suggested_relation_code)->first();\n";
echo "    \$displayName = \$relationshipType ? \$relationshipType->display_name_fr : \$suggestion->suggested_relation_code;\n";
echo "    echo \"   - {\$suggestion->suggestedUser->name} : {\$displayName} ({\$suggestion->suggested_relation_code})\\n\";\n";
echo "    echo \"     Raison: {\$suggestion->reason}\\n\";\n";
echo "}\n\n";

echo "// 7. Analyser les résultats\n";
echo "echo \"\\n📊 Analyse des résultats:\\n\";\n";
echo "\$testCases = [\n";
echo "    \$amina->id => ['name' => 'Amina', 'expected' => 'sister'],\n";
echo "    \$youssef->id => ['name' => 'Youssef', 'expected' => 'brother'],\n";
echo "    \$fatima->id => ['name' => 'Fatima', 'expected' => 'mother']\n";
echo "];\n\n";

echo "foreach (\$testCases as \$userId => \$testCase) {\n";
echo "    \$suggestion = \$suggestions->first(function (\$s) use (\$userId) {\n";
echo "        return \$s->suggested_user_id === \$userId;\n";
echo "    });\n";
echo "    \n";
echo "    if (!\$suggestion) {\n";
echo "        echo \"   ❌ {\$testCase['name']} : AUCUNE SUGGESTION\\n\";\n";
echo "    } elseif (\$suggestion->suggested_relation_code === \$testCase['expected']) {\n";
echo "        echo \"   ✅ {\$testCase['name']} : CORRECT ({\$suggestion->suggested_relation_code})\\n\";\n";
echo "    } else {\n";
echo "        echo \"   ❌ {\$testCase['name']} : INCORRECT - Obtenu: {\$suggestion->suggested_relation_code}, Attendu: {\$testCase['expected']}\\n\";\n";
echo "    }\n";
echo "}\n\n";

echo str_repeat("=", 60) . "\n";
echo "INSTRUCTIONS :\n";
echo "1. Ouvrez un terminal dans le projet\n";
echo "2. Exécutez : php artisan tinker\n";
echo "3. Copiez-collez les commandes ci-dessus une par une\n";
echo "4. Observez les logs de debug qui s'affichent\n";
echo "5. Analysez les résultats pour identifier le problème\n\n";

echo "Les logs de debug devraient montrer :\n";
echo "- 🔍 DEBUG DÉDUCTION pour chaque connexion\n";
echo "- Les codes de relation exacts (son, father, husband, etc.)\n";
echo "- Le cas déclenché (CAS 1, CAS 2, etc.) ou AUCUN CAS\n";
echo "- Le résultat de la déduction\n\n";

echo "Cela nous permettra de voir exactement où la logique échoue !\n";
