<?php

/**
 * DEBUG SPÉCIFIQUE MOHAMED → FATIMA
 * Pour comprendre pourquoi Fatima est suggérée comme "Sœur" au lieu de "Mère"
 */

echo "🔍 DEBUG SPÉCIFIQUE: MOHAMED → FATIMA\n";
echo str_repeat("=", 60) . "\n\n";

echo "📋 COMMANDES POUR ARTISAN TINKER:\n";
echo "Copiez-collez dans: php artisan tinker\n\n";

echo "// Charger les utilisateurs\n";
echo "\$mohamed = App\\Models\\User::where('name', 'like', '%Mohammed%')->first();\n";
echo "\$fatima = App\\Models\\User::where('name', 'like', '%Fatima%')->first();\n";
echo "\$ahmed = App\\Models\\User::where('name', 'like', '%Ahmed%')->first();\n\n";

echo "// Vérifier les relations existantes\n";
echo "echo \"🔗 Relations existantes pour Mohamed:\\n\";\n";
echo "\$mohamedRelations = App\\Models\\FamilyRelationship::where('user_id', \$mohamed->id)\n";
echo "    ->orWhere('related_user_id', \$mohamed->id)\n";
echo "    ->with(['user', 'relatedUser', 'relationshipType'])->get();\n";
echo "foreach (\$mohamedRelations as \$rel) {\n";
echo "    echo \"   - {\$rel->user->name} → {\$rel->relatedUser->name} : {\$rel->relationshipType->name}\\n\";\n";
echo "}\n\n";

echo "echo \"\\n🔗 Relations existantes pour Ahmed:\\n\";\n";
echo "\$ahmedRelations = App\\Models\\FamilyRelationship::where('user_id', \$ahmed->id)\n";
echo "    ->orWhere('related_user_id', \$ahmed->id)\n";
echo "    ->with(['user', 'relatedUser', 'relationshipType'])->get();\n";
echo "foreach (\$ahmedRelations as \$rel) {\n";
echo "    echo \"   - {\$rel->user->name} → {\$rel->relatedUser->name} : {\$rel->relationshipType->name}\\n\";\n";
echo "}\n\n";

echo "// Tester la suggestion Mohamed → Fatima avec debug\n";
echo "echo \"\\n🧪 TEST SUGGESTION MOHAMED → FATIMA:\\n\";\n";
echo "App\\Models\\Suggestion::where('user_id', \$mohamed->id)->delete();\n";
echo "\$suggestionService = app(App\\Services\\SuggestionService::class);\n";
echo "\$suggestions = \$suggestionService->generateSuggestions(\$mohamed);\n\n";

echo "echo \"💡 Suggestions générées pour Mohamed:\\n\";\n";
echo "foreach (\$suggestions as \$suggestion) {\n";
echo "    echo \"   - {\$suggestion->suggestedUser->name} : {\$suggestion->suggested_relation_code}\\n\";\n";
echo "    echo \"     Raison: {\$suggestion->reason}\\n\";\n";
echo "    if (\$suggestion->suggested_user_id === \$fatima->id) {\n";
echo "        echo \"     🎯 FATIMA TROUVÉE: {\$suggestion->suggested_relation_code} (attendu: mother)\\n\";\n";
echo "        if (\$suggestion->suggested_relation_code === 'mother') {\n";
echo "            echo \"     ✅ CORRECT!\\n\";\n";
echo "        } else {\n";
echo "            echo \"     ❌ INCORRECT! Devrait être 'mother'\\n\";\n";
echo "        }\n";
echo "    }\n";
echo "}\n\n";

echo "// Analyser le chemin de déduction attendu\n";
echo "echo \"\\n🧠 ANALYSE DU CHEMIN DE DÉDUCTION ATTENDU:\\n\";\n";
echo "echo \"   Mohamed → Ahmed : son (fils)\\n\";\n";
echo "echo \"   Ahmed → Fatima : husband (mari)\\n\";\n";
echo "echo \"   DÉDUCTION: Mohamed + Ahmed (son) + Fatima (épouse d'Ahmed) = Fatima est mère de Mohamed\\n\";\n";
echo "echo \"   CAS 1: enfant + conjoint → parent\\n\";\n";
echo "echo \"   RÉSULTAT ATTENDU: mother\\n\";\n\n";

echo str_repeat("=", 60) . "\n";
echo "INSTRUCTIONS:\n";
echo "1. Exécutez: php artisan tinker\n";
echo "2. Copiez-collez les commandes ci-dessus\n";
echo "3. Observez les logs de debug détaillés\n";
echo "4. Identifiez pourquoi le CAS 1 ne se déclenche pas\n\n";

echo "LOGS DE DEBUG ATTENDUS:\n";
echo "🔍 DEBUG DÉDUCTION:\n";
echo "   User: Mohamed Alami\n";
echo "   Connector: Ahmed Benali\n";
echo "   Suggested: Fatima Zahra\n";
echo "   User -> Connector: son\n";
echo "   Connector -> Suggested: husband\n";
echo "   Suggested Gender: female\n";
echo "   Checking CAS 1: user is child ✅\n";
echo "   Checking CAS 1: suggested is spouse ✅\n";
echo "   ✅ CAS 1 DÉCLENCHÉ: enfant + conjoint → parent (mother)\n\n";

echo "Si ces logs n'apparaissent pas, il y a un problème dans:\n";
echo "- La détection des relations\n";
echo "- La logique bidirectionnelle\n";
echo "- L'ordre des connexions\n\n";

echo "🔧 SOLUTIONS POSSIBLES:\n";
echo "1. Vérifier que les relations Ahmed ↔ Fatima existent bien\n";
echo "2. Vérifier que les relations Mohamed ↔ Ahmed existent bien\n";
echo "3. Corriger la logique de getUserRelationTypeFromRelation\n";
echo "4. Ajouter des cas manquants dans deduceRelationship\n\n";

echo "Exécutez le debug et partagez les résultats !\n";
