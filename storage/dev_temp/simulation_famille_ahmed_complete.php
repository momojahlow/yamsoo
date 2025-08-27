<?php

/**
 * SIMULATION COMPLÈTE DE LA FAMILLE AHMED
 * 
 * Scénario :
 * 1. Ahmed (papa) ajoute Fatima comme épouse
 * 2. Ahmed ajoute Amina comme fille
 * 3. Ahmed ajoute Mohamed comme fils
 * 4. Ahmed ajoute Youssef comme fils
 * 
 * Après chaque acceptation, vérifier les suggestions pour tous les membres
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
use App\Services\FamilyRelationService;
use Illuminate\Support\Facades\DB;

class FamilySimulation
{
    private $suggestionService;
    private $familyRelationService;
    private $users = [];
    private $relationshipTypes = [];
    private $testResults = [];

    public function __construct()
    {
        $this->suggestionService = app(SuggestionService::class);
        $this->familyRelationService = app(FamilyRelationService::class);
        $this->loadRelationshipTypes();
        $this->loadUsers();
    }

    private function loadRelationshipTypes()
    {
        $this->relationshipTypes = RelationshipType::all()->keyBy('name');
        echo "📋 Types de relations chargés : " . $this->relationshipTypes->count() . "\n";
    }

    private function loadUsers()
    {
        $this->users = [
            'ahmed' => User::where('name', 'like', '%Ahmed%')->first(),
            'fatima' => User::where('name', 'like', '%Fatima%')->first(),
            'mohamed' => User::where('name', 'like', '%Mohammed%')->first(),
            'amina' => User::where('name', 'like', '%Amina%')->first(),
            'youssef' => User::where('name', 'like', '%Youssef%')->first(),
        ];

        echo "👥 Utilisateurs chargés :\n";
        foreach ($this->users as $key => $user) {
            if ($user) {
                echo "   ✅ {$key}: {$user->name} (ID: {$user->id})\n";
            } else {
                echo "   ❌ {$key}: NON TROUVÉ\n";
                throw new Exception("Utilisateur {$key} non trouvé");
            }
        }
        echo "\n";
    }

    public function runCompleteSimulation()
    {
        echo "🎬 SIMULATION COMPLÈTE DE LA FAMILLE AHMED\n";
        echo str_repeat("=", 80) . "\n\n";

        // Étape 0: Nettoyer la base
        $this->cleanDatabase();

        // Étape 1: Ahmed ajoute Fatima comme épouse
        $this->step1_AhmedAjouteFatima();

        // Étape 2: Ahmed ajoute Amina comme fille
        $this->step2_AhmedAjouteAmina();

        // Étape 3: Ahmed ajoute Mohamed comme fils
        $this->step3_AhmedAjouteMohamed();

        // Étape 4: Ahmed ajoute Youssef comme fils
        $this->step4_AhmedAjouteYoussef();

        // Étape 5: Tests finaux complets
        $this->step5_TestsFinaux();

        // Rapport final
        $this->generateFinalReport();
    }

    private function cleanDatabase()
    {
        echo "🧹 ÉTAPE 0: NETTOYAGE DE LA BASE\n";
        echo str_repeat("-", 50) . "\n";

        // Supprimer toutes les suggestions
        $suggestionsDeleted = Suggestion::count();
        Suggestion::truncate();
        echo "   🗑️ {$suggestionsDeleted} suggestions supprimées\n";

        // Supprimer toutes les relations familiales
        $relationsDeleted = FamilyRelationship::count();
        FamilyRelationship::truncate();
        echo "   🗑️ {$relationsDeleted} relations familiales supprimées\n";

        echo "   ✅ Base de données nettoyée\n\n";
    }

    private function step1_AhmedAjouteFatima()
    {
        echo "💑 ÉTAPE 1: AHMED AJOUTE FATIMA COMME ÉPOUSE\n";
        echo str_repeat("-", 50) . "\n";

        $this->createRelation(
            $this->users['ahmed'],
            $this->users['fatima'],
            'husband',
            "Ahmed ajoute Fatima comme épouse"
        );

        // Tester les suggestions après cette relation
        $this->testSuggestionsAfterStep("Étape 1", [
            'ahmed' => [
                // Ahmed ne devrait pas avoir de nouvelles suggestions familiales
                // car il n'y a que lui et Fatima pour l'instant
            ],
            'fatima' => [
                // Fatima ne devrait pas avoir de nouvelles suggestions familiales
                // car il n'y a que elle et Ahmed pour l'instant
            ]
        ]);

        echo "\n";
    }

    private function step2_AhmedAjouteAmina()
    {
        echo "👧 ÉTAPE 2: AHMED AJOUTE AMINA COMME FILLE\n";
        echo str_repeat("-", 50) . "\n";

        $this->createRelation(
            $this->users['ahmed'],
            $this->users['amina'],
            'father',
            "Ahmed ajoute Amina comme fille"
        );

        // Tester les suggestions après cette relation
        $this->testSuggestionsAfterStep("Étape 2", [
            'ahmed' => [
                // Ahmed ne devrait pas avoir de nouvelles suggestions
            ],
            'fatima' => [
                // Fatima devrait voir Amina comme fille (via mariage avec Ahmed)
                $this->users['amina']->id => 'daughter'
            ],
            'amina' => [
                // Amina devrait voir Fatima comme mère (épouse du père)
                $this->users['fatima']->id => 'mother'
            ]
        ]);

        echo "\n";
    }

    private function step3_AhmedAjouteMohamed()
    {
        echo "👦 ÉTAPE 3: AHMED AJOUTE MOHAMED COMME FILS\n";
        echo str_repeat("-", 50) . "\n";

        $this->createRelation(
            $this->users['ahmed'],
            $this->users['mohamed'],
            'father',
            "Ahmed ajoute Mohamed comme fils"
        );

        // Tester les suggestions après cette relation
        $this->testSuggestionsAfterStep("Étape 3", [
            'ahmed' => [
                // Ahmed ne devrait pas avoir de nouvelles suggestions
            ],
            'fatima' => [
                // Fatima devrait voir Mohamed comme fils
                $this->users['mohamed']->id => 'son'
            ],
            'amina' => [
                // Amina devrait voir Mohamed comme frère
                $this->users['mohamed']->id => 'brother'
            ],
            'mohamed' => [
                // Mohamed devrait voir Fatima comme mère et Amina comme sœur
                $this->users['fatima']->id => 'mother',
                $this->users['amina']->id => 'sister'
            ]
        ]);

        echo "\n";
    }

    private function step4_AhmedAjouteYoussef()
    {
        echo "👦 ÉTAPE 4: AHMED AJOUTE YOUSSEF COMME FILS\n";
        echo str_repeat("-", 50) . "\n";

        $this->createRelation(
            $this->users['ahmed'],
            $this->users['youssef'],
            'father',
            "Ahmed ajoute Youssef comme fils"
        );

        // Tester les suggestions après cette relation
        $this->testSuggestionsAfterStep("Étape 4", [
            'ahmed' => [
                // Ahmed ne devrait pas avoir de nouvelles suggestions
            ],
            'fatima' => [
                // Fatima devrait voir Youssef comme fils
                $this->users['youssef']->id => 'son'
            ],
            'amina' => [
                // Amina devrait voir Youssef comme frère
                $this->users['youssef']->id => 'brother'
            ],
            'mohamed' => [
                // Mohamed devrait voir Youssef comme frère
                $this->users['youssef']->id => 'brother'
            ],
            'youssef' => [
                // Youssef devrait voir Fatima comme mère, Amina comme sœur, Mohamed comme frère
                $this->users['fatima']->id => 'mother',
                $this->users['amina']->id => 'sister',
                $this->users['mohamed']->id => 'brother'
            ]
        ]);

        echo "\n";
    }

    private function step5_TestsFinaux()
    {
        echo "🎯 ÉTAPE 5: TESTS FINAUX COMPLETS\n";
        echo str_repeat("-", 50) . "\n";

        // Tester toutes les suggestions pour tous les membres
        $expectedSuggestions = [
            'ahmed' => [
                // Ahmed a déjà toutes ses relations directes, pas de suggestions attendues
            ],
            'fatima' => [
                // Fatima devrait voir tous les enfants d'Ahmed comme ses enfants
                $this->users['amina']->id => 'daughter',
                $this->users['mohamed']->id => 'son',
                $this->users['youssef']->id => 'son'
            ],
            'amina' => [
                // Amina devrait voir Fatima comme mère et ses frères
                $this->users['fatima']->id => 'mother',
                $this->users['mohamed']->id => 'brother',
                $this->users['youssef']->id => 'brother'
            ],
            'mohamed' => [
                // Mohamed devrait voir Fatima comme mère et ses frères/sœurs
                $this->users['fatima']->id => 'mother',
                $this->users['amina']->id => 'sister',
                $this->users['youssef']->id => 'brother'
            ],
            'youssef' => [
                // Youssef devrait voir Fatima comme mère et ses frères/sœurs
                $this->users['fatima']->id => 'mother',
                $this->users['amina']->id => 'sister',
                $this->users['mohamed']->id => 'brother'
            ]
        ];

        $this->testSuggestionsAfterStep("Tests finaux", $expectedSuggestions);
    }

    private function createRelation($user1, $user2, $relationType, $description)
    {
        echo "   🔗 {$description}\n";
        
        $relationshipType = $this->relationshipTypes[$relationType];
        if (!$relationshipType) {
            throw new Exception("Type de relation '{$relationType}' non trouvé");
        }

        // Créer la relation directement comme acceptée
        $relation = FamilyRelationship::create([
            'user_id' => $user1->id,
            'related_user_id' => $user2->id,
            'relationship_type_id' => $relationshipType->id,
            'status' => 'accepted',
            'created_automatically' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Créer la relation inverse
        $inverseType = $this->getInverseRelationType($relationType, $user2);
        if ($inverseType) {
            FamilyRelationship::create([
                'user_id' => $user2->id,
                'related_user_id' => $user1->id,
                'relationship_type_id' => $inverseType->id,
                'status' => 'accepted',
                'created_automatically' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        echo "   ✅ Relation créée : {$user1->name} ↔ {$user2->name} ({$relationType})\n";
    }

    private function getInverseRelationType($relationType, $targetUser)
    {
        $gender = $targetUser->profile?->gender ?? $this->guessGenderFromName($targetUser);
        
        $inverseMap = [
            'husband' => 'wife',
            'wife' => 'husband',
            'father' => $gender === 'female' ? 'daughter' : 'son',
            'mother' => $gender === 'female' ? 'daughter' : 'son',
            'son' => 'father', // On ne peut pas deviner le genre du parent
            'daughter' => 'mother', // On ne peut pas deviner le genre du parent
            'brother' => $gender === 'female' ? 'sister' : 'brother',
            'sister' => $gender === 'female' ? 'sister' : 'brother'
        ];

        $inverseName = $inverseMap[$relationType] ?? null;
        return $inverseName ? $this->relationshipTypes[$inverseName] : null;
    }

    private function guessGenderFromName($user)
    {
        $name = strtolower($user->name);
        $maleNames = ['ahmed', 'mohamed', 'mohammed', 'youssef', 'omar', 'karim'];
        $femaleNames = ['fatima', 'amina', 'leila', 'nadia', 'zineb'];
        
        foreach ($maleNames as $maleName) {
            if (strpos($name, $maleName) !== false) {
                return 'male';
            }
        }
        
        foreach ($femaleNames as $femaleName) {
            if (strpos($name, $femaleName) !== false) {
                return 'female';
            }
        }
        
        return 'male'; // Par défaut
    }

    private function testSuggestionsAfterStep($stepName, $expectedSuggestions)
    {
        echo "   🧪 Test des suggestions après {$stepName}:\n";
        
        foreach ($expectedSuggestions as $userKey => $expectations) {
            $user = $this->users[$userKey];
            
            // Supprimer les anciennes suggestions
            Suggestion::where('user_id', $user->id)->delete();
            
            // Générer de nouvelles suggestions
            $suggestions = $this->suggestionService->generateSuggestions($user);
            
            echo "      👤 {$user->name} ({$suggestions->count()} suggestions):\n";
            
            if (empty($expectations)) {
                if ($suggestions->count() === 0) {
                    echo "         ✅ Aucune suggestion attendue, aucune générée\n";
                } else {
                    echo "         ⚠️ Aucune suggestion attendue, mais {$suggestions->count()} générée(s)\n";
                    foreach ($suggestions as $suggestion) {
                        echo "            - {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code}\n";
                    }
                }
                continue;
            }
            
            foreach ($expectations as $expectedUserId => $expectedRelation) {
                $suggestion = $suggestions->first(function ($s) use ($expectedUserId) {
                    return $s->suggested_user_id === $expectedUserId;
                });
                
                $expectedUser = collect($this->users)->first(function ($u) use ($expectedUserId) {
                    return $u->id === $expectedUserId;
                });
                
                if (!$suggestion) {
                    echo "         ❌ {$expectedUser->name} : AUCUNE SUGGESTION (attendu: {$expectedRelation})\n";
                    $this->testResults[] = [
                        'step' => $stepName,
                        'user' => $user->name,
                        'expected_user' => $expectedUser->name,
                        'expected_relation' => $expectedRelation,
                        'actual_relation' => 'none',
                        'success' => false
                    ];
                } elseif ($suggestion->suggested_relation_code === $expectedRelation) {
                    echo "         ✅ {$expectedUser->name} : {$suggestion->suggested_relation_code} (correct)\n";
                    $this->testResults[] = [
                        'step' => $stepName,
                        'user' => $user->name,
                        'expected_user' => $expectedUser->name,
                        'expected_relation' => $expectedRelation,
                        'actual_relation' => $suggestion->suggested_relation_code,
                        'success' => true
                    ];
                } else {
                    echo "         ❌ {$expectedUser->name} : {$suggestion->suggested_relation_code} (attendu: {$expectedRelation})\n";
                    $this->testResults[] = [
                        'step' => $stepName,
                        'user' => $user->name,
                        'expected_user' => $expectedUser->name,
                        'expected_relation' => $expectedRelation,
                        'actual_relation' => $suggestion->suggested_relation_code,
                        'success' => false
                    ];
                }
            }
        }
    }

    private function generateFinalReport()
    {
        echo "📊 RAPPORT FINAL DE LA SIMULATION\n";
        echo str_repeat("=", 80) . "\n\n";

        $totalTests = count($this->testResults);
        $successfulTests = count(array_filter($this->testResults, function ($r) { return $r['success']; }));
        $failedTests = $totalTests - $successfulTests;

        echo "📈 Statistiques globales :\n";
        echo "   - Tests exécutés : {$totalTests}\n";
        echo "   - Tests réussis : {$successfulTests}\n";
        echo "   - Tests échoués : {$failedTests}\n";
        echo "   - Taux de réussite : " . round(($successfulTests / max($totalTests, 1)) * 100, 1) . "%\n\n";

        if ($failedTests > 0) {
            echo "❌ Tests échoués :\n";
            foreach ($this->testResults as $result) {
                if (!$result['success']) {
                    echo "   - {$result['step']} | {$result['user']} → {$result['expected_user']} : ";
                    echo "Attendu {$result['expected_relation']}, Obtenu {$result['actual_relation']}\n";
                }
            }
            echo "\n";
        }

        // Afficher l'état final de la famille
        echo "👨‍👩‍👧‍👦 Structure familiale finale :\n";
        $finalRelations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
        foreach ($finalRelations as $relation) {
            echo "   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr}\n";
        }

        echo "\n";
        if ($failedTests === 0) {
            echo "🎉 SUCCÈS COMPLET ! Toutes les suggestions familiales fonctionnent correctement.\n";
        } else {
            echo "⚠️ Des problèmes subsistent dans le système de suggestions.\n";
        }
    }
}

// Exécuter la simulation
try {
    $simulation = new FamilySimulation();
    $simulation->runCompleteSimulation();
} catch (Exception $e) {
    echo "❌ Erreur lors de la simulation : " . $e->getMessage() . "\n";
    echo "📋 Trace : " . $e->getTraceAsString() . "\n";
    exit(1);
}
