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
        $members = $family ? $this->familyService->getFamilyMembers($family) : collect();

        return Inertia::render('Family', [
            'family' => $family,
            'members' => $members,
        ]);
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
