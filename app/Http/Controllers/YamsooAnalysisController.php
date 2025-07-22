<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FamilyRelationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class YamsooAnalysisController extends Controller
{
    protected FamilyRelationService $familyRelationService;

    public function __construct(FamilyRelationService $familyRelationService)
    {
        $this->familyRelationService = $familyRelationService;
    }

    /**
     * Analyser la relation entre l'utilisateur connecté et un autre utilisateur
     */
    public function analyzeRelation(Request $request): JsonResponse
    {
        $request->validate([
            'target_user_id' => 'required|integer|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $targetUser = User::find($request->target_user_id);

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        try {
            $analysis = $this->familyRelationService->analyzeRelationshipBetweenUsers($currentUser, $targetUser);

            return response()->json([
                'success' => true,
                'analysis' => $analysis,
                'target_user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'profile' => $targetUser->profile ? [
                        'first_name' => $targetUser->profile->first_name,
                        'last_name' => $targetUser->profile->last_name,
                        'avatar_url' => $targetUser->profile->avatar_url,
                    ] : null,
                ],
                'current_user' => [
                    'id' => $currentUser->id,
                    'name' => $currentUser->name,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse de la relation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir un résumé des relations familiales de l'utilisateur connecté
     */
    public function getRelationsSummary(): JsonResponse
    {
        $currentUser = Auth::user();

        try {
            $statistics = $this->familyRelationService->getFamilyStatistics($currentUser);
            $relationships = $this->familyRelationService->getUserRelationships($currentUser);

            $relationsList = $relationships->map(function ($relationship) use ($currentUser) {
                $relatedUser = $relationship->user_id === $currentUser->id
                    ? $relationship->relatedUser
                    : $relationship->user;

                return [
                    'id' => $relatedUser->id,
                    'name' => $relatedUser->name,
                    'relationship' => $relationship->relationshipType->name_fr,
                    'relationship_code' => $relationship->relationshipType->code,
                    'profile' => $relatedUser->profile ? [
                        'first_name' => $relatedUser->profile->first_name,
                        'last_name' => $relatedUser->profile->last_name,
                        'avatar_url' => $relatedUser->profile->avatar_url,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'statistics' => $statistics,
                'relationships' => $relationsList,
                'total_family_members' => $relationships->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des relations.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyser les relations multiples (pour une liste d'utilisateurs)
     */
    public function analyzeMultipleRelations(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array|max:10',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $userIds = $request->user_ids;
        $results = [];

        try {
            foreach ($userIds as $userId) {
                $targetUser = User::find($userId);
                if ($targetUser) {
                    $analysis = $this->familyRelationService->analyzeRelationshipBetweenUsers($currentUser, $targetUser);
                    $results[] = [
                        'user_id' => $userId,
                        'user_name' => $targetUser->name,
                        'analysis' => $analysis,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'analyzed_count' => count($results),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse des relations multiples.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir des suggestions de relations basées sur l'analyse Yamsoo
     */
    public function getRelationSuggestions(): JsonResponse
    {
        $currentUser = Auth::user();

        try {
            // Obtenir les utilisateurs sans relation avec l'utilisateur actuel
            $potentialRelatives = User::where('id', '!=', $currentUser->id)
                ->whereNotIn('id', function ($query) use ($currentUser) {
                    $query->select('related_user_id')
                        ->from('family_relationships')
                        ->where('user_id', $currentUser->id)
                        ->where('status', 'accepted')
                        ->union(
                            $query->newQuery()
                                ->select('user_id')
                                ->from('family_relationships')
                                ->where('related_user_id', $currentUser->id)
                                ->where('status', 'accepted')
                        );
                })
                ->with('profile')
                ->limit(20)
                ->get();

            $suggestions = [];
            foreach ($potentialRelatives as $user) {
                $analysis = $this->familyRelationService->analyzeRelationshipBetweenUsers($currentUser, $user);
                
                // Ajouter seulement les utilisateurs avec des relations indirectes potentielles
                if ($analysis['relation_type'] === 'indirect' || 
                    ($analysis['relation_type'] === 'none' && $this->hasCommonConnections($currentUser, $user))) {
                    $suggestions[] = [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'profile' => $user->profile,
                        ],
                        'analysis' => $analysis,
                        'suggestion_reason' => $this->getSuggestionReason($analysis),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'suggestions' => array_slice($suggestions, 0, 10), // Limiter à 10 suggestions
                'total_suggestions' => count($suggestions),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération des suggestions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Vérifier s'il y a des connexions communes entre deux utilisateurs
     */
    private function hasCommonConnections(User $user1, User $user2): bool
    {
        $user1Relations = $this->familyRelationService->getUserRelationships($user1);
        $user2Relations = $this->familyRelationService->getUserRelationships($user2);

        $user1RelatedIds = $user1Relations->map(function ($rel) use ($user1) {
            return $rel->user_id === $user1->id ? $rel->related_user_id : $rel->user_id;
        });

        $user2RelatedIds = $user2Relations->map(function ($rel) use ($user2) {
            return $rel->user_id === $user2->id ? $rel->related_user_id : $rel->user_id;
        });

        return $user1RelatedIds->intersect($user2RelatedIds)->isNotEmpty();
    }

    /**
     * Obtenir la raison de la suggestion
     */
    private function getSuggestionReason(array $analysis): string
    {
        if ($analysis['relation_type'] === 'indirect') {
            return "Relation indirecte détectée : {$analysis['relation_name']}";
        }

        return "Connexions familiales communes détectées";
    }
}
