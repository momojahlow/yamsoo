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
                    'type' => $relationship->relationshipType->display_name_fr,
                    'type_code' => $relationship->relationshipType->name,
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
                'relationship_type' => $relationship->relationshipType->display_name_fr,
                'relationship_code' => $relationship->relationshipType->name,
                'created_automatically' => $relationship->created_automatically,
            ];

            $relationCode = $relationship->relationshipType->name;

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

                case 'grandfather':
                case 'grandmother':
                    $treeData['grandparents']['paternal'][] = $relatedUser;
                    break;

                case 'uncle':
                case 'aunt':
                    $treeData['uncles_aunts']['paternal'][] = $relatedUser;
                    break;

                case 'grandson':
                case 'granddaughter':
                    $treeData['grandchildren'][] = $relatedUser;
                    break;

                case 'cousin':
                    $treeData['cousins'][] = $relatedUser;
                    break;

                // Relations par alliance (belle-famille)
                case 'father_in_law':
                case 'mother_in_law':
                    $treeData['parents'][] = $relatedUser;
                    break;

                case 'brother_in_law':
                case 'sister_in_law':
                    $treeData['siblings'][] = $relatedUser;
                    break;

                case 'stepson':
                case 'stepdaughter':
                    $treeData['children'][] = $relatedUser;
                    break;

                case 'nephew':
                case 'niece':
                    $treeData['uncles_aunts']['paternal'][] = $relatedUser;
                    break;
            }
        }

        return $treeData;
    }

    /**
     * API endpoint pour récupérer les relations familiales
     */
    public function getFamilyRelations(Request $request)
    {
        $user = $request->user();

        // Récupérer toutes les relations de l'utilisateur
        $relationships = $this->familyRelationService->getUserRelationships($user);

        // Formater les données pour le frontend
        $relations = $relationships->map(function ($relationship) use ($user) {
            $relatedUser = $relationship->relatedUser;
            $relationType = $relationship->relationshipType;

            return [
                'id' => $relationship->id,
                'user_id' => $user->id,
                'related_user_id' => $relatedUser->id,
                'relation_type' => $relationType->name, // Code de la relation
                'status' => $relationship->status,
                'created_at' => $relationship->created_at,
                'updated_at' => $relationship->updated_at,
                'related_user' => [
                    'id' => $relatedUser->id,
                    'name' => $relatedUser->name,
                    'email' => $relatedUser->email,
                    'profile' => $relatedUser->profile ? [
                        'id' => $relatedUser->profile->id,
                        'first_name' => $relatedUser->profile->first_name,
                        'last_name' => $relatedUser->profile->last_name,
                        'gender' => $relatedUser->profile->gender,
                        'birth_date' => $relatedUser->profile->birth_date,
                        'avatar_url' => $relatedUser->profile->avatar,
                    ] : null,
                ],
            ];
        });

        return response()->json([
            'relations' => $relations,
            'userId' => $user->id,
        ]);
    }
}
