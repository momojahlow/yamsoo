#!/bin/bash

echo "🧪 Test du système de relations familiales..."

# Exécuter les tests unitaires
echo "🔬 Exécution des tests unitaires..."
php artisan test tests/Unit/FamilyRelationshipInferenceTest.php --verbose

# Vérifier que les types de relations sont bien créés
echo ""
echo "📊 Vérification des types de relations en base..."
php artisan tinker --execute="
\$count = App\Models\RelationshipType::count();
echo 'Nombre de types de relations: ' . \$count . PHP_EOL;

if (\$count >= 30) {
    echo '✅ Types de relations correctement créés' . PHP_EOL;
} else {
    echo '❌ Problème: pas assez de types de relations' . PHP_EOL;
}

// Vérifier quelques types spécifiques
\$requiredTypes = ['father', 'mother', 'son', 'daughter', 'wife', 'husband', 'daughter_in_law', 'son_in_law'];
\$missing = [];
foreach (\$requiredTypes as \$type) {
    if (!App\Models\RelationshipType::where('name', \$type)->exists()) {
        \$missing[] = \$type;
    }
}

if (empty(\$missing)) {
    echo '✅ Tous les types de relations requis sont présents' . PHP_EOL;
} else {
    echo '❌ Types manquants: ' . implode(', ', \$missing) . PHP_EOL;
}
"

# Test du service d'inférence
echo ""
echo "🧠 Test du service d'inférence..."
php artisan tinker --execute="
use App\Models\User;
use App\Services\FamilyRelationshipInferenceService;

// Créer des utilisateurs de test
\$ahmed = User::factory()->create(['name' => 'Ahmed Test']);
\$mohamed = User::factory()->create(['name' => 'Mohamed Test']);
\$leila = User::factory()->create(['name' => 'Leila Test']);

// Créer un profil pour Leila avec le genre féminin
\$leila->profile()->create([
    'first_name' => 'Leila',
    'last_name' => 'Test',
    'gender' => 'female'
]);

\$service = new FamilyRelationshipInferenceService();

// Test: Ahmed (père) -> Mohamed (fils) -> Leila (épouse)
// Résultat attendu: Leila = Belle-fille d'Ahmed
\$result = \$service->inferRelationship(
    \$ahmed,
    \$leila,
    \$mohamed,
    'father',  // Ahmed -> Mohamed
    'wife'     // Mohamed -> Leila
);

if (\$result && \$result['code'] === 'daughter_in_law') {
    echo '✅ Test d\'inférence réussi: Leila est correctement identifiée comme belle-fille d\'Ahmed' . PHP_EOL;
    echo '   Relation: ' . \$result['code'] . PHP_EOL;
    echo '   Raison: ' . \$result['reason'] . PHP_EOL;
    echo '   Confiance: ' . \$result['confidence'] . '%' . PHP_EOL;
} else {
    echo '❌ Test d\'inférence échoué' . PHP_EOL;
    if (\$result) {
        echo '   Résultat obtenu: ' . \$result['code'] . PHP_EOL;
    } else {
        echo '   Aucun résultat obtenu' . PHP_EOL;
    }
}

// Nettoyer les utilisateurs de test
\$ahmed->delete();
\$mohamed->delete();
\$leila->delete();
"

echo ""
echo "🎯 Tests terminés !"
echo ""
echo "📝 Pour tester manuellement:"
echo "   1. Connectez-vous à l'application"
echo "   2. Créez des relations familiales"
echo "   3. Vérifiez que les suggestions sont correctes"
echo "   4. Le cas 'Ahmed → Mohamed → Leila' devrait maintenant suggérer 'belle-fille' et non 'mère'"
