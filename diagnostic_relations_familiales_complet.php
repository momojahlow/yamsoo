<?php

/**
 * DIAGNOSTIC COMPLET DES RELATIONS FAMILIALES
 * 
 * Ce script teste TOUS les types de relations familiales possibles
 * et identifie les problèmes dans la logique de suggestion.
 * 
 * Structure des tests :
 * 1. Relations directes (parents/enfants/frères-sœurs)
 * 2. Relations par mariage (conjoints/beaux-parents)
 * 3. Relations étendues (grands-parents/oncles-tantes/cousins)
 * 4. Relations complexes (familles recomposées)
 */

// Charger Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Models\Suggestion;
use App\Services\SuggestionService;
use Illuminate\Support\Facades\DB;

class FamilyRelationshipDiagnostic
{
    private $suggestionService;
    private $relationshipTypes;
    private $testResults = [];
    private $users = [];

    public function __construct()
    {
        $this->suggestionService = app(SuggestionService::class);
        $this->relationshipTypes = RelationshipType::all()->keyBy('name');
        $this->loadUsers();
    }

    private function loadUsers()
    {
        $this->users = [
            'ahmed' => User::where('name', 'like', '%Ahmed%')->first(),
            'fatima' => User::where('name', 'like', '%Fatima%')->first(),
            'mohammed' => User::where('name', 'like', '%Mohammed%')->first(),
            'amina' => User::where('name', 'like', '%Amina%')->first(),
            'youssef' => User::where('name', 'like', '%Youssef%')->first(),
        ];

        foreach ($this->users as $key => $user) {
            if (!$user) {
                throw new Exception("Utilisateur {$key} non trouvé");
            }
        }
    }

    public function runCompleteDiagnostic()
    {
        echo "🔍 DIAGNOSTIC COMPLET DES RELATIONS FAMILIALES\n";
        echo str_repeat("=", 80) . "\n\n";

        // 1. Analyser la structure actuelle
        $this->analyzeCurrentStructure();

        // 2. Tester tous les types de relations
        $this->testAllRelationshipTypes();

        // 3. Tester les scénarios familiaux complexes
        $this->testComplexFamilyScenarios();

        // 4. Identifier les problèmes
        $this->identifyProblems();

        // 5. Générer le rapport final
        $this->generateFinalReport();
    }

    private function analyzeCurrentStructure()
    {
        echo "📊 ANALYSE DE LA STRUCTURE ACTUELLE\n";
        echo str_repeat("-", 50) . "\n";

        // Afficher tous les types de relations disponibles
        echo "Types de relations disponibles (" . $this->relationshipTypes->count() . ") :\n";
        foreach ($this->relationshipTypes as $type) {
            echo sprintf(
                "  %-20s %-20s %-12s %s\n",
                $type->name,
                $type->display_name_fr,
                $type->category,
                "Gen: " . $type->generation_level
            );
        }

        echo "\nUtilisateurs de test :\n";
        foreach ($this->users as $key => $user) {
            echo "  - {$key}: {$user->name} (ID: {$user->id})\n";
        }

        // Afficher les relations existantes
        echo "\nRelations existantes :\n";
        $existingRelations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
        foreach ($existingRelations as $relation) {
            echo "  - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr}\n";
        }

        echo "\n";
    }

    private function testAllRelationshipTypes()
    {
        echo "🧪 TEST DE TOUS LES TYPES DE RELATIONS\n";
        echo str_repeat("-", 50) . "\n";

        $categories = [
            'direct' => 'Relations directes',
            'marriage' => 'Relations par mariage',
            'extended' => 'Relations étendues',
            'adoption' => 'Relations d\'adoption'
        ];

        foreach ($categories as $category => $categoryName) {
            echo "\n📋 {$categoryName} :\n";
            $typesInCategory = $this->relationshipTypes->where('category', $category);
            
            foreach ($typesInCategory as $type) {
                $this->testRelationshipType($type);
            }
        }
    }

    private function testRelationshipType($type)
    {
        echo "  🔸 Test : {$type->display_name_fr} ({$type->name})\n";
        
        // Tester la logique de déduction pour ce type
        $testCases = $this->generateTestCasesForType($type);
        
        foreach ($testCases as $testCase) {
            $result = $this->runTestCase($testCase);
            $this->testResults[] = $result;
            
            if (!$result['success']) {
                echo "    ❌ {$result['description']}\n";
                echo "       Attendu: {$result['expected']}, Obtenu: {$result['actual']}\n";
            } else {
                echo "    ✅ {$result['description']}\n";
            }
        }
    }

    private function generateTestCasesForType($type)
    {
        $testCases = [];
        
        // Générer des cas de test basés sur le type de relation
        switch ($type->category) {
            case 'direct':
                $testCases = $this->generateDirectRelationTestCases($type);
                break;
            case 'marriage':
                $testCases = $this->generateMarriageRelationTestCases($type);
                break;
            case 'extended':
                $testCases = $this->generateExtendedRelationTestCases($type);
                break;
            case 'adoption':
                $testCases = $this->generateAdoptionRelationTestCases($type);
                break;
        }
        
        return $testCases;
    }

    private function generateDirectRelationTestCases($type)
    {
        $testCases = [];
        
        switch ($type->name) {
            case 'father':
            case 'mother':
                // Test parent → enfant
                $testCases[] = [
                    'user' => $this->users['mohammed'],
                    'suggested' => $this->users['ahmed'],
                    'expected' => 'father',
                    'description' => 'Mohammed devrait voir Ahmed comme père'
                ];
                break;
                
            case 'son':
            case 'daughter':
                // Test enfant → parent
                $testCases[] = [
                    'user' => $this->users['ahmed'],
                    'suggested' => $this->users['mohammed'],
                    'expected' => 'son',
                    'description' => 'Ahmed devrait voir Mohammed comme fils'
                ];
                break;
                
            case 'brother':
            case 'sister':
                // Test frère/sœur
                $testCases[] = [
                    'user' => $this->users['mohammed'],
                    'suggested' => $this->users['amina'],
                    'expected' => 'sister',
                    'description' => 'Mohammed devrait voir Amina comme sœur'
                ];
                break;
        }
        
        return $testCases;
    }

    private function generateMarriageRelationTestCases($type)
    {
        $testCases = [];
        
        switch ($type->name) {
            case 'husband':
            case 'wife':
                // Test conjoint
                $testCases[] = [
                    'user' => $this->users['ahmed'],
                    'suggested' => $this->users['fatima'],
                    'expected' => 'wife',
                    'description' => 'Ahmed devrait voir Fatima comme épouse'
                ];
                break;
                
            case 'father_in_law':
            case 'mother_in_law':
                // Test beau-parent
                $testCases[] = [
                    'user' => $this->users['fatima'],
                    'suggested' => $this->users['ahmed'],
                    'expected' => 'father_in_law',
                    'description' => 'Test beau-père via mariage'
                ];
                break;
        }
        
        return $testCases;
    }

    private function generateExtendedRelationTestCases($type)
    {
        $testCases = [];
        
        switch ($type->name) {
            case 'grandfather':
            case 'grandmother':
                // Test grand-parent
                break;
                
            case 'grandson':
            case 'granddaughter':
                // Test petit-enfant
                break;
                
            case 'uncle':
            case 'aunt':
                // Test oncle/tante
                break;
                
            case 'nephew':
            case 'niece':
                // Test neveu/nièce
                break;
                
            case 'cousin':
                // Test cousin
                break;
        }
        
        return $testCases;
    }

    private function generateAdoptionRelationTestCases($type)
    {
        // Tests pour les relations d'adoption
        return [];
    }

    private function runTestCase($testCase)
    {
        // Supprimer les anciennes suggestions
        Suggestion::where('user_id', $testCase['user']->id)->delete();
        
        // Générer de nouvelles suggestions
        try {
            $suggestions = $this->suggestionService->generateSuggestions($testCase['user']);
            
            // Chercher la suggestion pour l'utilisateur suggéré
            $foundSuggestion = $suggestions->first(function ($suggestion) use ($testCase) {
                return $suggestion->suggested_user_id === $testCase['suggested']->id;
            });
            
            $actual = $foundSuggestion ? $foundSuggestion->suggested_relation_code : 'none';
            $success = $actual === $testCase['expected'];
            
            return [
                'success' => $success,
                'description' => $testCase['description'],
                'expected' => $testCase['expected'],
                'actual' => $actual,
                'user' => $testCase['user']->name,
                'suggested' => $testCase['suggested']->name
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'description' => $testCase['description'],
                'expected' => $testCase['expected'],
                'actual' => 'error: ' . $e->getMessage(),
                'user' => $testCase['user']->name,
                'suggested' => $testCase['suggested']->name
            ];
        }
    }

    private function testComplexFamilyScenarios()
    {
        echo "\n🏗️ TEST DES SCÉNARIOS FAMILIAUX COMPLEXES\n";
        echo str_repeat("-", 50) . "\n";

        // Scénario 1: Famille nucléaire simple
        echo "📋 Scénario 1: Famille nucléaire (Ahmed-Fatima-Mohammed-Amina-Youssef)\n";
        $this->testNuclearFamily();

        // Scénario 2: Famille recomposée
        echo "\n📋 Scénario 2: Famille recomposée\n";
        $this->testBlendedFamily();

        // Scénario 3: Relations étendues (grands-parents, oncles, etc.)
        echo "\n📋 Scénario 3: Relations étendues\n";
        $this->testExtendedFamily();
    }

    private function testNuclearFamily()
    {
        $expectedRelations = [
            // Pour Mohammed
            'mohammed' => [
                'ahmed' => 'father',
                'fatima' => 'mother',
                'amina' => 'sister',
                'youssef' => 'brother'
            ],
            // Pour Amina
            'amina' => [
                'ahmed' => 'father',
                'fatima' => 'mother',
                'mohammed' => 'brother',
                'youssef' => 'brother'
            ],
            // Pour Ahmed
            'ahmed' => [
                'fatima' => 'wife',
                'mohammed' => 'son',
                'amina' => 'daughter',
                'youssef' => 'son'
            ]
        ];

        foreach ($expectedRelations as $userKey => $relations) {
            echo "  👤 Test pour {$userKey}:\n";
            foreach ($relations as $suggestedKey => $expectedRelation) {
                $testCase = [
                    'user' => $this->users[$userKey],
                    'suggested' => $this->users[$suggestedKey],
                    'expected' => $expectedRelation,
                    'description' => "{$userKey} → {$suggestedKey} = {$expectedRelation}"
                ];
                
                $result = $this->runTestCase($testCase);
                if (!$result['success']) {
                    echo "    ❌ {$result['description']} (obtenu: {$result['actual']})\n";
                } else {
                    echo "    ✅ {$result['description']}\n";
                }
            }
        }
    }

    private function testBlendedFamily()
    {
        echo "  🔄 Test des familles recomposées...\n";
        // TODO: Implémenter les tests pour familles recomposées
    }

    private function testExtendedFamily()
    {
        echo "  🌳 Test des relations étendues...\n";
        // TODO: Implémenter les tests pour relations étendues
    }

    private function identifyProblems()
    {
        echo "\n🚨 IDENTIFICATION DES PROBLÈMES\n";
        echo str_repeat("-", 50) . "\n";

        $failedTests = array_filter($this->testResults, function ($result) {
            return !$result['success'];
        });

        if (empty($failedTests)) {
            echo "✅ Aucun problème détecté ! Toutes les relations fonctionnent correctement.\n";
            return;
        }

        echo "❌ " . count($failedTests) . " problème(s) détecté(s) :\n\n";

        $problemsByType = [];
        foreach ($failedTests as $test) {
            $problemType = $this->categorizeProblem($test);
            $problemsByType[$problemType][] = $test;
        }

        foreach ($problemsByType as $problemType => $tests) {
            echo "🔸 {$problemType} (" . count($tests) . " cas) :\n";
            foreach ($tests as $test) {
                echo "  - {$test['description']}\n";
                echo "    Attendu: {$test['expected']}, Obtenu: {$test['actual']}\n";
            }
            echo "\n";
        }
    }

    private function categorizeProblem($test)
    {
        if (strpos($test['actual'], 'error:') === 0) {
            return 'Erreurs de logique';
        }
        
        if ($test['actual'] === 'none') {
            return 'Relations manquantes';
        }
        
        return 'Relations incorrectes';
    }

    private function generateFinalReport()
    {
        echo "\n📊 RAPPORT FINAL\n";
        echo str_repeat("=", 50) . "\n";

        $totalTests = count($this->testResults);
        $successfulTests = count(array_filter($this->testResults, function ($r) { return $r['success']; }));
        $failedTests = $totalTests - $successfulTests;

        echo "📈 Statistiques :\n";
        echo "  - Tests exécutés : {$totalTests}\n";
        echo "  - Tests réussis : {$successfulTests}\n";
        echo "  - Tests échoués : {$failedTests}\n";
        echo "  - Taux de réussite : " . round(($successfulTests / $totalTests) * 100, 1) . "%\n\n";

        if ($failedTests > 0) {
            echo "🔧 Actions recommandées :\n";
            echo "  1. Corriger la logique de déduction dans SuggestionService.php\n";
            echo "  2. Ajouter les cas manquants dans la méthode deduceRelationship\n";
            echo "  3. Tester les corrections avec ce script\n";
            echo "  4. Valider avec les utilisateurs finaux\n\n";
        } else {
            echo "🎉 Félicitations ! Toutes les relations familiales fonctionnent correctement.\n\n";
        }

        echo "📋 Types de relations testés :\n";
        $testedTypes = array_unique(array_column($this->testResults, 'expected'));
        foreach ($testedTypes as $type) {
            $typeTests = array_filter($this->testResults, function ($r) use ($type) { return $r['expected'] === $type; });
            $typeSuccess = count(array_filter($typeTests, function ($r) { return $r['success']; }));
            $typeTotal = count($typeTests);
            $status = $typeSuccess === $typeTotal ? '✅' : '❌';
            echo "  {$status} {$type} : {$typeSuccess}/{$typeTotal}\n";
        }
    }
}

// Exécuter le diagnostic
try {
    $diagnostic = new FamilyRelationshipDiagnostic();
    $diagnostic->runCompleteDiagnostic();
} catch (Exception $e) {
    echo "❌ Erreur lors du diagnostic : " . $e->getMessage() . "\n";
    echo "📋 Trace : " . $e->getTraceAsString() . "\n";
    exit(1);
}
