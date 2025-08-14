<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Services\FamilyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyController extends Controller
{
    public function __construct(
        private FamilyService $familyService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $family = $this->familyService->getUserFamily($user);

        // Récupérer toutes les relations acceptées (bidirectionnelles)
        $relationsAsUser = \App\Models\FamilyRelationship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get();

        $relationsAsRelated = \App\Models\FamilyRelationship::where('related_user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get();

        // Construire la liste des membres à afficher
        $members = collect();

        // Relations où l'utilisateur est user_id
        foreach ($relationsAsUser as $relation) {
            $member = $relation->relatedUser;
            $profile = $member->profile;
            $members->push([
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'relation' => $relation->relationshipType->display_name_fr ?? $relation->relationshipType->name ?? 'Relation',
                'relation_code' => $relation->relationshipType->name,
                'category' => $relation->relationshipType->category ?? 'family',
                'status' => $relation->status,
                'avatar' => $profile?->avatar ?? null,
                'bio' => $profile?->bio ?? null,
                'birth_date' => $profile?->birth_date ?? null,
                'gender' => $profile?->gender ?? null,
                'phone' => $profile?->phone ?? null,
            ]);
        }

        // Relations où l'utilisateur est related_user_id (utiliser la relation inverse)
        foreach ($relationsAsRelated as $relation) {
            $member = $relation->user;
            $profile = $member->profile;

            // Obtenir la relation inverse
            $reverseRelationName = $this->getReverseRelationName($relation->relationshipType->name);
            $reverseRelationType = \App\Models\RelationshipType::where('name', $reverseRelationName)->first();

            $members->push([
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'relation' => $reverseRelationType?->display_name_fr ?? $reverseRelationName ?? 'Relation',
                'relation_code' => $reverseRelationName,
                'category' => $reverseRelationType?->category ?? 'family',
                'status' => $relation->status,
                'avatar' => $profile?->avatar ?? null,
                'bio' => $profile?->bio ?? null,
                'birth_date' => $profile?->birth_date ?? null,
                'gender' => $profile?->gender ?? null,
                'phone' => $profile?->phone ?? null,
            ]);
        }

        // Supprimer les doublons basés sur l'ID
        $members = $members->unique('id')->values();

        return Inertia::render('Family', [
            'family' => $family,
            'members' => $members->toArray(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Family/Create');
    }

    public function show(Family $family): Response
    {
        $members = $this->familyService->getFamilyMembers($family);

        return Inertia::render('Family/Show', [
            'family' => $family,
            'members' => $members,
        ]);
    }

    public function tree(Request $request): Response
    {
        $user = $request->user();
        $family = $this->familyService->getUserFamily($user);

        if (!$family) {
            return Inertia::render('Family/Empty');
        }

        $treeData = $this->familyService->getFamilyTree($family);

        return Inertia::render('FamilyTree', [
            'family' => $family,
            'treeData' => $treeData,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $this->familyService->createFamily($user, $validated);

        return redirect()->route('families.index')->with('success', 'Famille créée avec succès.');
    }

    /**
     * Obtenir le nom de la relation inverse
     */
    private function getReverseRelationName(string $relationName): string
    {
        $reverseMap = [
            'father' => 'son',
            'mother' => 'daughter',
            'son' => 'father',
            'daughter' => 'mother',
            'brother' => 'brother',
            'sister' => 'sister',
            'grandfather' => 'grandson',
            'grandmother' => 'granddaughter',
            'grandson' => 'grandfather',
            'granddaughter' => 'grandmother',
            'uncle' => 'nephew',
            'aunt' => 'niece',
            'nephew' => 'uncle',
            'niece' => 'aunt',
            'cousin' => 'cousin',
            'husband' => 'wife',
            'wife' => 'husband',
            'father_in_law' => 'son_in_law',
            'mother_in_law' => 'daughter_in_law',
            'son_in_law' => 'father_in_law',
            'daughter_in_law' => 'mother_in_law',
            'brother_in_law' => 'sister_in_law',
            'sister_in_law' => 'brother_in_law',
            'stepfather' => 'stepson',
            'stepmother' => 'stepdaughter',
            'stepson' => 'stepfather',
            'stepdaughter' => 'stepmother',
        ];

        return $reverseMap[$relationName] ?? $relationName;
    }

    public function addMember(Request $request, Family $family): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'relation' => 'required|string|max:100',
        ]);

        $this->familyService->addFamilyMember($family, $validated['user_id'], $validated['relation']);

        return back()->with('success', 'Membre ajouté avec succès.');
    }
}
