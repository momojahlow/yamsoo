<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function __construct()
    {
        // Le middleware sera appliqué via les routes
    }

    /**
     * Liste des utilisateurs avec filtres et recherche
     */
    public function index(Request $request): Response
    {
        $query = User::with(['profile', 'roleAssignedBy']);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(20)->withQueryString();

        // Transformer les données pour l'interface
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_name' => $user->role_name,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
                'last_seen_at' => $user->last_seen_at,
                'last_login_at' => $user->last_login_at,
                'role_assigned_at' => $user->role_assigned_at,
                'role_assigned_by' => $user->roleAssignedBy?->name,
                'profile' => $user->profile ? [
                    'first_name' => $user->profile->first_name,
                    'last_name' => $user->profile->last_name,
                    'birth_date' => $user->profile->birth_date,
                    'gender' => $user->profile->gender,
                    'avatar_url' => $user->profile->avatar_url,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status', 'sort_by', 'sort_order']),
            'roles' => [
                'user' => 'Utilisateur',
                'moderator' => 'Modérateur',
                'admin' => 'Administrateur',
                'super_admin' => 'Super Administrateur',
            ],
        ]);
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show(User $user): Response
    {
        $user->load(['profile', 'roleAssignedBy', 'messages', 'photos', 'photoAlbums']);

        $userStats = [
            'messages_count' => $user->messages()->count(),
            'conversations_count' => $user->conversations()->count(),
            'photos_count' => $user->photos()->count(),
            'albums_count' => $user->photoAlbums()->count(),
            'family_relationships' => $user->familyRelationships()->count(),
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'role' => $user->role,
                'role_name' => $user->role_name,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
                'last_seen_at' => $user->last_seen_at,
                'last_login_at' => $user->last_login_at,
                'last_login_ip' => $user->last_login_ip,
                'role_assigned_at' => $user->role_assigned_at,
                'role_assigned_by' => $user->roleAssignedBy?->name,
                'profile' => $user->profile,
            ],
            'stats' => $userStats,
        ]);
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['user', 'moderator', 'admin', 'super_admin'])],
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->get('is_active', true),
            'role_assigned_at' => now(),
            'role_assigned_by' => Auth::guard('admin')->id(),
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
        ], 201);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['sometimes', 'required', Rule::in(['user', 'moderator', 'admin', 'super_admin'])],
            'is_active' => 'sometimes|boolean',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ]);

        $updateData = $request->only(['name', 'email', 'is_active']);

        // Mise à jour du mot de passe si fourni
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Mise à jour du rôle si changé
        if ($request->filled('role') && $request->role !== $user->role) {
            $updateData['role'] = $request->role;
            $updateData['role_assigned_at'] = now();
            $updateData['role_assigned_by'] = Auth::guard('admin')->id();
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Activer/désactiver un utilisateur
     */
    public function toggleStatus(User $user): JsonResponse
    {
        $user->setActive(!$user->is_active);

        return response()->json([
            'message' => $user->is_active ? 'Utilisateur activé' : 'Utilisateur désactivé',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Changer le rôle d'un utilisateur
     */
    public function changeRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => ['required', Rule::in(['user', 'moderator', 'admin', 'super_admin'])],
        ]);

        // Vérifier que l'admin actuel peut assigner ce rôle
        $currentUser = Auth::guard('admin')->user();
        if ($request->role === 'super_admin' && $currentUser->role !== 'super_admin') {
            return response()->json([
                'message' => 'Seul un super administrateur peut assigner ce rôle',
            ], 403);
        }

        $user->assignRole($request->role, $currentUser);

        return response()->json([
            'message' => 'Rôle mis à jour avec succès',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Supprimer un utilisateur (soft delete)
     */
    public function destroy(User $user): JsonResponse
    {
        // Empêcher la suppression de son propre compte
        if ($user->id === Auth::guard('admin')->id()) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte',
            ], 403);
        }

        // Empêcher la suppression du dernier super admin
        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() <= 1) {
            return response()->json([
                'message' => 'Impossible de supprimer le dernier super administrateur',
            ], 403);
        }

        $user->setActive(false);

        return response()->json([
            'message' => 'Utilisateur désactivé avec succès',
        ]);
    }

    /**
     * Statistiques des utilisateurs
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'by_role' => User::selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role'),
            'recent' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'online' => User::where('last_seen_at', '>=', now()->subHour())->count(),
        ];

        return response()->json($stats);
    }
}
