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

        // Récupérer toutes les relations acceptées où l'utilisateur est user_id (éviter les doublons)
        $relations = \App\Models\FamilyRelationship::where('user_id', $user->id)
        ->where('status', 'accepted')
        ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
        ->get();

        // Construire la liste des membres à afficher
        $members = $relations->map(function($relation) use ($user) {
            $member = $relation->relatedUser;
            $profile = $member->profile;
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'relation' => $relation->relationshipType->name_fr ?? $relation->relationshipType->name ?? 'Relation',
                'status' => $relation->status,
                'avatar' => $profile?->avatar ?? null,
                'bio' => $profile?->bio ?? null,
                'birth_date' => $profile?->birth_date ?? null,
                'gender' => $profile?->gender ?? null,
                'phone' => $profile?->phone ?? null,
            ];
        })->values();

        return Inertia::render('Family', [
            'family' => $family,
            'members' => $members->toArray(), // S'assurer que c'est un tableau
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
