<?php

namespace App\Http\Controllers;

use App\Services\FamilyRelationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyTreeController extends Controller
{
    public function __construct(
        private FamilyRelationService $familyRelationService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Obtenir toutes les relations de l'utilisateur
        $relationships = $this->familyRelationService->getUserRelationships($user);
        
        // Construire les données de l'arbre familial
        $treeData = $this->buildFamilyTreeData($user, $relationships);
        
        // Obtenir les statistiques familiales
        $statistics = $this->familyRelationService->getFamilyStatistics($user);
        
        return Inertia::render('FamilyTree/Index', [
            'user' => $user->load('profile'),
            'treeData' => $treeData,
            'relationships' => $relationships->map(function ($relationship) {
                return [
                    'id' => $relationship->id,
                    'type' => $relationship->relationshipType->name_fr,
                    'type_code' => $relationship->relationshipType->code,
                    'related_user' => [
                        'id' => $relationship->relatedUser->id,
                        'name' => $relationship->relatedUser->name,
                        'profile' => $relationship->relatedUser->profile,
                    ],
                    'created_automatically' => $relationship->created_automatically,
                    'created_at' => $relationship->created_at,
                ];
            }),
            'statistics' => $statistics,
        ]);
    }

    private function buildFamilyTreeData($user, $relationships)
    {
        $treeData = [
            'center' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile' => $user->profile,
                'isCurrentUser' => true,
            ],
            'parents' => [],
            'spouse' => null,
            'children' => [],
            'siblings' => [],
            'grandparents' => [
                'paternal' => [],
                'maternal' => [],
            ],
            'uncles_aunts' => [
                'paternal' => [],
                'maternal' => [],
            ],
            'grandchildren' => [],
            'cousins' => [],
        ];

        foreach ($relationships as $relationship) {
            $relatedUser = [
                'id' => $relationship->relatedUser->id,
                'name' => $relationship->relatedUser->name,
                'profile' => $relationship->relatedUser->profile,
                'relationship_type' => $relationship->relationshipType->name_fr,
                'relationship_code' => $relationship->relationshipType->code,
                'created_automatically' => $relationship->created_automatically,
            ];

            $relationCode = $relationship->relationshipType->code;

            // Classer les relations par catégorie
            switch ($relationCode) {
                case 'father':
                case 'mother':
                    $treeData['parents'][] = $relatedUser;
                    break;

                case 'husband':
                case 'wife':
                    $treeData['spouse'] = $relatedUser;
                    break;

                case 'son':
                case 'daughter':
                    $treeData['children'][] = $relatedUser;
                    break;

                case 'brother':
                case 'sister':
                    $treeData['siblings'][] = $relatedUser;
                    break;

                case 'grandfather_paternal':
                case 'grandmother_paternal':
                    $treeData['grandparents']['paternal'][] = $relatedUser;
                    break;

                case 'grandfather_maternal':
                case 'grandmother_maternal':
                    $treeData['grandparents']['maternal'][] = $relatedUser;
                    break;

                case 'uncle_paternal':
                case 'aunt_paternal':
                    $treeData['uncles_aunts']['paternal'][] = $relatedUser;
                    break;

                case 'uncle_maternal':
                case 'aunt_maternal':
                    $treeData['uncles_aunts']['maternal'][] = $relatedUser;
                    break;

                case 'grandson':
                case 'granddaughter':
                    $treeData['grandchildren'][] = $relatedUser;
                    break;

                case 'cousin_paternal_m':
                case 'cousin_paternal_f':
                case 'cousin_maternal_m':
                case 'cousin_maternal_f':
                    $treeData['cousins'][] = $relatedUser;
                    break;
            }
        }

        return $treeData;
    }
}
