<?php

/**
 * DEBUG COMPLET AMINA → FATIMA
 * Pour comprendre pourquoi Fatima est suggérée comme "Sœur" au lieu de "Mère"
 */

echo "🔍 DEBUG COMPLET: AMINA → FATIMA\n";
echo str_repeat("=", 60) . "\n\n";

echo "📋 COMMANDES POUR ARTISAN TINKER:\n";
echo "Copiez-collez dans: php artisan tinker\n\n";

echo "// 1. Charger les utilisateurs\n";
echo "\$amina = App\\Models\\User::where('name', 'like', '%Amina%')->first();\n";
echo "\$fatima = App\\Models\\User::where('name', 'like', '%Fatima%')->first();\n";
echo "\$ahmed = App\\Models\\User::where('name', 'like', '%Ahmed%')->first();\n";
echo "\$mohamed = App\\Models\\User::where('name', 'like', '%Mohammed%')->first();\n\n";

echo "// 2. Vérifier les utilisateurs\n";
echo "if (!\$amina || !\$fatima || !\$ahmed) {\n";
echo "    echo \"❌ Utilisateurs manquants\\n\";\n";
echo "    exit;\n";
echo "}\n";
echo "echo \"✅ Utilisateurs trouvés:\\n\";\n";
echo "echo \"   Amina: {\$amina->name} (ID: {\$amina->id})\\n\";\n";
echo "echo \"   Fatima: {\$fatima->name} (ID: {\$fatima->id})\\n\";\n";
echo "echo \"   Ahmed: {\$ahmed->name} (ID: {\$ahmed->id})\\n\";\n\n";

echo "// 3. Analyser TOUTES les relations existantes\n";
echo "echo \"\\n🔗 TOUTES LES RELATIONS EXISTANTES:\\n\";\n";
echo "\$allRelations = App\\Models\\FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();\n";
echo "foreach (\$allRelations as \$rel) {\n";
echo "    echo \"   {\$rel->user->name} → {\$rel->relatedUser->name} : {\$rel->relationshipType->name} ({\$rel->relationshipType->code})\\n\";\n";
echo "}\n\n";

echo "// 4. Relations spécifiques d'Amina\n";
echo "echo \"\\n🎯 RELATIONS D'AMINA:\\n\";\n";
echo "\$aminaRelations = App\\Models\\FamilyRelationship::where('user_id', \$amina->id)\n";
echo "    ->orWhere('related_user_id', \$amina->id)\n";
echo "    ->with(['user', 'relatedUser', 'relationshipType'])->get();\n";
echo "foreach (\$aminaRelations as \$rel) {\n";
echo "    if (\$rel->user_id === \$amina->id) {\n";
echo "        echo \"   Amina → {\$rel->relatedUser->name} : {\$rel->relationshipType->code}\\n\";\n";
echo "    } else {\n";
echo "        echo \"   {\$rel->user->name} → Amina : {\$rel->relationshipType->code}\\n\";\n";
echo "    }\n";
echo "}\n\n";

echo "// 5. Relations spécifiques d'Ahmed\n";
echo "echo \"\\n🎯 RELATIONS D'AHMED:\\n\";\n";
echo "\$ahmedRelations = App\\Models\\FamilyRelationship::where('user_id', \$ahmed->id)\n";
echo "    ->orWhere('related_user_id', \$ahmed->id)\n";
echo "    ->with(['user', 'relatedUser', 'relationshipType'])->get();\n";
echo "foreach (\$ahmedRelations as \$rel) {\n";
echo "    if (\$rel->user_id === \$ahmed->id) {\n";
echo "        echo \"   Ahmed → {\$rel->relatedUser->name} : {\$rel->relationshipType->code}\\n\";\n";
echo "    } else {\n";
echo "        echo \"   {\$rel->user->name} → Ahmed : {\$rel->relationshipType->code}\\n\";\n";
echo "    }\n";
echo "}\n\n";

echo "// 6. Vérifier la relation Ahmed ↔ Fatima\n";
echo "echo \"\\n🔍 RELATION AHMED ↔ FATIMA:\\n\";\n";
echo "\$ahmedFatimaRelation = App\\Models\\FamilyRelationship::where(function(\$query) use (\$ahmed, \$fatima) {\n";
echo "    \$query->where('user_id', \$ahmed->id)->where('related_user_id', \$fatima->id);\n";
echo "})->orWhere(function(\$query) use (\$ahmed, \$fatima) {\n";
echo "    \$query->where('user_id', \$fatima->id)->where('related_user_id', \$ahmed->id);\n";
echo "})->with('relationshipType')->first();\n";
echo "if (\$ahmedFatimaRelation) {\n";
echo "    echo \"   ✅ Relation trouvée: {\$ahmedFatimaRelation->user->name} → {\$ahmedFatimaRelation->relatedUser->name} : {\$ahmedFatimaRelation->relationshipType->code}\\n\";\n";
echo "} else {\n";
echo "    echo \"   ❌ AUCUNE RELATION AHMED ↔ FATIMA TROUVÉE!\\n\";\n";
echo "}\n\n";

echo "// 7. Vérifier la relation Amina ↔ Ahmed\n";
echo "echo \"\\n🔍 RELATION AMINA ↔ AHMED:\\n\";\n";
echo "\$aminaAhmedRelation = App\\Models\\FamilyRelationship::where(function(\$query) use (\$amina, \$ahmed) {\n";
echo "    \$query->where('user_id', \$amina->id)->where('related_user_id', \$ahmed->id);\n";
echo "})->orWhere(function(\$query) use (\$amina, \$ahmed) {\n";
echo "    \$query->where('user_id', \$ahmed->id)->where('related_user_id', \$amina->id);\n";
echo "})->with('relationshipType')->first();\n";
echo "if (\$aminaAhmedRelation) {\n";
echo "    echo \"   ✅ Relation trouvée: {\$aminaAhmedRelation->user->name} → {\$aminaAhmedRelation->relatedUser->name} : {\$aminaAhmedRelation->relationshipType->code}\\n\";\n";
echo "} else {\n";
echo "    echo \"   ❌ AUCUNE RELATION AMINA ↔ AHMED TROUVÉE!\\n\";\n";
echo "}\n\n";

echo "// 8. Test de génération de suggestions avec debug\n";
echo "echo \"\\n🧪 GÉNÉRATION DE SUGGESTIONS POUR AMINA:\\n\";\n";
echo "App\\Models\\Suggestion::where('user_id', \$amina->id)->delete();\n";
echo "\$suggestionService = app(App\\Services\\SuggestionService::class);\n";
echo "\$suggestions = \$suggestionService->generateSuggestions(\$amina);\n\n";

echo "// 9. Analyser les résultats\n";
echo "echo \"\\n💡 RÉSULTATS DES SUGGESTIONS:\\n\";\n";
echo "foreach (\$suggestions as \$suggestion) {\n";
echo "    echo \"   - {\$suggestion->suggestedUser->name} : {\$suggestion->suggested_relation_code}\\n\";\n";
echo "    echo \"     Raison: {\$suggestion->reason}\\n\";\n";
echo "    if (\$suggestion->suggested_user_id === \$fatima->id) {\n";
echo "        echo \"     🎯 FATIMA TROUVÉE: {\$suggestion->suggested_relation_code}\\n\";\n";
echo "        if (\$suggestion->suggested_relation_code === 'mother') {\n";
echo "            echo \"     ✅ CORRECT!\\n\";\n";
echo "        } else {\n";
echo "            echo \"     ❌ INCORRECT! Devrait être 'mother'\\n\";\n";
echo "        }\n";
echo "    }\n";
echo "}\n\n";

echo "// 10. Analyse de la logique attendue\n";
echo "echo \"\\n🧠 LOGIQUE ATTENDUE:\\n\";\n";
echo "echo \"   1. Amina → Ahmed : daughter (fille)\\n\";\n";
echo "echo \"   2. Ahmed → Fatima : husband (mari)\\n\";\n";
echo "echo \"   3. DÉDUCTION: Amina (enfant) + Fatima (conjoint d'Ahmed) = Fatima est mère\\n\";\n";
echo "echo \"   4. CAS 1: enfant + conjoint → parent\\n\";\n";
echo "echo \"   5. RÉSULTAT ATTENDU: mother\\n\";\n\n";

echo str_repeat("=", 60) . "\n";
echo "INSTRUCTIONS:\n";
echo "1. Exécutez: php artisan tinker\n";
echo "2. Copiez-collez TOUTES les commandes ci-dessus\n";
echo "3. Observez CHAQUE étape\n";
echo "4. Identifiez où la logique échoue\n\n";

echo "POINTS CRITIQUES À VÉRIFIER:\n";
echo "✓ Les relations Ahmed ↔ Fatima existent-elles ?\n";
echo "✓ Les relations Amina ↔ Ahmed existent-elles ?\n";
echo "✓ Les codes de relation sont-ils corrects ?\n";
echo "✓ Le CAS 1 se déclenche-t-il ?\n";
echo "✓ Y a-t-il des logs de debug ?\n\n";

echo "Exécutez le debug complet et partagez TOUS les résultats !\n";
