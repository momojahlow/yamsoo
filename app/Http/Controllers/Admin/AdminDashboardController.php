<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\Family;
use App\Models\Conversation;
use App\Models\Photo;
use App\Models\PhotoAlbum;
use App\Models\FamilyRelationship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        // Le middleware sera appliqué via les routes
    }

    /**
     * Tableau de bord administrateur principal
     */
    public function index(): Response
    {
        // Récupérer l'admin connecté
        $admin = Auth::guard('admin')->user();

        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'total_messages' => Message::count(),
            'total_conversations' => Conversation::count(),
            'total_families' => Family::count(),
            'total_photos' => Photo::count(),
            'total_albums' => PhotoAlbum::count(),
            'total_relationships' => FamilyRelationship::where('status', 'accepted')->count(),
            'pending_relationships' => FamilyRelationship::where('status', 'pending')->count(),
        ];

        // Statistiques par rôle
        $roleStats = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        // Activité récente (dernières 24h)
        $recentActivity = [
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'messages_today' => Message::whereDate('created_at', today())->count(),
            'photos_uploaded_today' => Photo::whereDate('created_at', today())->count(),
            'active_users_today' => User::whereDate('last_seen_at', today())->count(),
        ];

        // Utilisateurs récents (7 derniers jours)
        $recentUsers = User::with(['profile', 'roleAssignedBy'])
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_name' => $user->role_name,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at,
                    'last_seen_at' => $user->last_seen_at,
                    'profile' => $user->profile,
                ];
            });

        // Utilisateurs en ligne (dernière heure)
        $onlineUsers = User::where('last_seen_at', '>=', now()->subHour())
            ->with('profile')
            ->orderBy('last_seen_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'role_name' => $user->role_name,
                    'last_seen_at' => $user->last_seen_at,
                    'profile' => $user->profile,
                ];
            });

        // Statistiques d'utilisation par mois (6 derniers mois)
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $date->format('Y-m'),
                'month_name' => $date->format('M Y'),
                'new_users' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'messages' => Message::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'photos' => Photo::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }

        // Top utilisateurs par activité
        $topActiveUsers = User::with('profile')
            ->where('last_seen_at', '>=', now()->subDays(30))
            ->orderBy('last_seen_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role_name' => $user->role_name,
                    'last_seen_at' => $user->last_seen_at,
                    'messages_count' => $user->messages()->count(),
                    'photos_count' => $user->photos()->count(),
                    'profile' => $user->profile,
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'role_name' => $admin->role_name,
                'permissions' => $admin->permissions ?? [],
            ],
            'stats' => $stats,
            'roleStats' => $roleStats,
            'recentActivity' => $recentActivity,
            'recentUsers' => $recentUsers,
            'onlineUsers' => $onlineUsers,
            'monthlyStats' => $monthlyStats,
            'topActiveUsers' => $topActiveUsers,
        ]);
    }

    /**
     * Statistiques système en temps réel
     */
    public function systemStats(): array
    {
        return [
            'server_time' => now()->toISOString(),
            'database_size' => $this->getDatabaseSize(),
            'storage_usage' => $this->getStorageUsage(),
            'cache_status' => $this->getCacheStatus(),
        ];
    }

    /**
     * Obtenir la taille de la base de données
     */
    private function getDatabaseSize(): string
    {
        try {
            $size = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ")[0]->size_mb ?? 0;
            
            return $size . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Obtenir l'utilisation du stockage
     */
    private function getStorageUsage(): array
    {
        $storagePath = storage_path('app/public');
        $totalSize = 0;
        
        if (is_dir($storagePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storagePath)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $totalSize += $file->getSize();
                }
            }
        }
        
        return [
            'total_size' => round($totalSize / 1024 / 1024, 2) . ' MB',
            'photos_count' => Photo::count(),
            'albums_count' => PhotoAlbum::count(),
        ];
    }

    /**
     * Obtenir le statut du cache
     */
    private function getCacheStatus(): array
    {
        return [
            'enabled' => config('cache.default') !== 'array',
            'driver' => config('cache.default'),
        ];
    }
}
