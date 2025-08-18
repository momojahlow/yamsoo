<?php

namespace App\Http\Controllers;

use App\Services\FamilyRelationService;
use App\Services\SuggestionService;
use App\Services\NotificationService;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private FamilyRelationService $familyRelationService,
        private SuggestionService $suggestionService,
        private NotificationService $notificationService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        // Obtenir les données familiales
        $relationships = $this->familyRelationService->getUserRelationships($user);
        $statistics = $this->familyRelationService->getFamilyStatistics($user);
        $suggestions = $this->suggestionService->getUserSuggestions($user);

        // Calculer les statistiques du dashboard
        $dashboardStats = $this->calculateDashboardStats($user, $relationships, $suggestions);

        // Obtenir les activités récentes
        $recentActivities = $this->getRecentActivities($user);

        // Obtenir les suggestions prioritaires
        $prioritySuggestions = $this->getPrioritySuggestions($user);

        // Obtenir les membres de famille récents
        $recentFamilyMembers = $this->getRecentFamilyMembers($user);

        // Obtenir les anniversaires à venir
        $upcomingBirthdays = $this->getUpcomingBirthdays($user);

        // Obtenir les données de notifications
        $notifications = $this->notificationService->getUserNotifications($user);
        $unreadNotifications = $this->notificationService->getUnreadCount($user);

        // Obtenir les demandes de relation reçues
        $pendingRequests = $this->familyRelationService->getPendingRequests($user);

        // Calculer les badges dynamiques
        $badges = $this->calculateDynamicBadges($user);

        return Inertia::render('ModernDashboard', [
            'auth' => ['user' => $user->load('profile')],
            'stats' => $dashboardStats,
            'recent_activities' => $recentActivities,
            'family_members' => $recentFamilyMembers,
            'pending_suggestions' => $prioritySuggestions,
            'upcoming_birthdays' => $upcomingBirthdays,
            'notifications' => $notifications->take(5),
            'unread_notifications' => $unreadNotifications,
            'pending_requests' => $pendingRequests->take(3),
            'badges' => $badges,
        ]);
    }

    private function calculateDashboardStats(User $user, $relationships, $suggestions): array
    {
        // Compter les relations par période
        $thisMonth = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })
        ->where('created_at', '>=', Carbon::now()->startOfMonth())
        ->count();

        $thisWeek = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })
        ->where('created_at', '>=', Carbon::now()->startOfWeek())
        ->count();

        // Compter les suggestions en attente
        $pendingSuggestions = $suggestions->where('status', 'pending')->count();

        // Compter les nouvelles suggestions cette semaine
        $newSuggestions = Suggestion::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->count();

        // Statistiques des photos et albums
        $totalPhotos = \App\Models\Photo::where('user_id', $user->id)->count();
        $totalAlbums = \App\Models\PhotoAlbum::where('user_id', $user->id)->count();

        // Statistiques des messages (si le modèle existe)
        $totalMessages = 0;
        $activeConversations = 0;
        if (class_exists('\App\Models\Message')) {
            $totalMessages = \App\Models\Message::where('user_id', $user->id)->count();
            $activeConversations = \App\Models\Message::where('user_id', $user->id)
                ->distinct('conversation_id')
                ->count('conversation_id');
        }

        return [
            'total_family_members' => $relationships->count(),
            'new_members_this_month' => $thisMonth,
            'new_members_this_week' => $thisWeek,
            'pending_suggestions' => $pendingSuggestions,
            'new_suggestions_this_week' => $newSuggestions,
            'total_suggestions' => $suggestions->count(),
            'automatic_relations' => $relationships->where('created_automatically', true)->count(),
            'manual_relations' => $relationships->where('created_automatically', false)->count(),
            'total_photos' => $totalPhotos,
            'total_albums' => $totalAlbums,
            'total_messages' => $totalMessages,
            'active_conversations' => $activeConversations,
        ];
    }

    private function calculateDynamicBadges(User $user): array
    {
        // Badge pour les notifications
        $totalNotifications = $this->notificationService->getUnreadCount($user);

        // Badge pour les suggestions en attente
        $pendingSuggestions = Suggestion::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Badge pour les nouvelles suggestions cette semaine
        $newSuggestionsThisWeek = Suggestion::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->count();

        // Badge pour les demandes de relation en attente
        $pendingRequests = \App\Models\RelationshipRequest::where('target_user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Badge pour les albums photo
        $totalAlbums = \App\Models\PhotoAlbum::where('user_id', $user->id)->count();

        // Badge pour les conversations actives (messages non lus)
        $unreadMessages = 0;
        $activeConversations = 0;
        if (class_exists('\App\Models\Message')) {
            $unreadMessages = \App\Models\Message::where('recipient_id', $user->id)
                ->where('read_at', null)
                ->count();

            $activeConversations = \App\Models\Message::where(function($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
            })
            ->distinct('conversation_id')
            ->count('conversation_id');
        }

        // Badge pour les événements à venir
        $upcomingEvents = 0;
        if (class_exists('\App\Models\Event')) {
            $upcomingEvents = \App\Models\Event::where('user_id', $user->id)
                ->where('date', '>=', Carbon::now())
                ->where('date', '<=', Carbon::now()->addDays(7))
                ->count();
        }

        return [
            'notifications' => $totalNotifications,
            'suggestions' => $pendingSuggestions,
            'new_suggestions' => $newSuggestionsThisWeek,
            'pending_requests' => $pendingRequests,
            'albums' => $totalAlbums,
            'unread_messages' => $unreadMessages,
            'active_conversations' => $activeConversations,
            'upcoming_events' => $upcomingEvents,
            'total_badges' => $totalNotifications + $pendingSuggestions + $pendingRequests + $unreadMessages,
        ];
    }

    private function getRecentActivities(User $user): array
    {
        $activities = [];

        // Activités des relations récentes
        $recentRelations = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        foreach ($recentRelations as $relation) {
            $relatedUser = $relation->user_id === $user->id ? $relation->relatedUser : $relation->user;
            $activities[] = [
                'id' => 'relation_' . $relation->id,
                'type' => 'family',
                'text' => "Nouvelle relation : {$relatedUser->name} ({$relation->relationshipType->name_fr})",
                'time' => $this->formatTimeAgo($relation->created_at),
                'avatar' => $this->getInitials($relatedUser->name),
                'icon' => 'Users',
                'color' => 'blue',
                'created_at' => $relation->created_at,
            ];
        }

        // Activités des suggestions récentes
        $recentSuggestions = Suggestion::where('user_id', $user->id)
            ->with('suggestedUser')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentSuggestions as $suggestion) {
            $activities[] = [
                'id' => 'suggestion_' . $suggestion->id,
                'type' => 'suggestion',
                'text' => "Nouvelle suggestion : {$suggestion->suggestedUser->name}",
                'time' => $this->formatTimeAgo($suggestion->created_at),
                'avatar' => $this->getInitials($suggestion->suggestedUser->name),
                'icon' => 'Heart',
                'color' => 'pink',
                'created_at' => $suggestion->created_at,
            ];
        }

        // Trier par date et limiter
        usort($activities, function($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return array_slice($activities, 0, 8);
    }

    private function getPrioritySuggestions(User $user): array
    {
        return $this->suggestionService->getUserSuggestions($user)
            ->where('status', 'pending')
            ->take(3)
            ->map(function ($suggestion) {
                return [
                    'id' => $suggestion->id,
                    'suggested_user' => [
                        'id' => $suggestion->suggestedUser->id,
                        'name' => $suggestion->suggestedUser->name,
                        'profile' => $suggestion->suggestedUser->profile,
                    ],
                    'relation_name' => $suggestion->suggested_relation_name ?: $suggestion->suggested_relation_code,
                    'type' => $suggestion->type,
                    'created_at' => $suggestion->created_at,
                ];
            })
            ->values()
            ->toArray();
    }

    private function getRecentFamilyMembers(User $user): array
    {
        return $this->familyRelationService->getUserRelationships($user)
            ->sortByDesc('created_at')
            ->take(4)
            ->map(function ($relationship) {
                return [
                    'id' => $relationship->relatedUser->id,
                    'name' => $relationship->relatedUser->name,
                    'profile' => $relationship->relatedUser->profile,
                    'relation_type' => $relationship->relationshipType->name_fr,
                    'created_automatically' => $relationship->created_automatically,
                    'created_at' => $relationship->created_at,
                ];
            })
            ->values()
            ->toArray();
    }

    private function getUpcomingBirthdays(User $user): array
    {
        $relationships = $this->familyRelationService->getUserRelationships($user);
        $birthdays = [];

        foreach ($relationships as $relationship) {
            $relatedUser = $relationship->relatedUser;
            if ($relatedUser->profile && $relatedUser->profile->birth_date) {
                $birthDate = Carbon::parse($relatedUser->profile->birth_date);
                $nextBirthday = $birthDate->copy()->year(Carbon::now()->year);

                // Si l'anniversaire est passé cette année, prendre l'année suivante
                if ($nextBirthday->isPast()) {
                    $nextBirthday->addYear();
                }

                // Prendre seulement les anniversaires dans les 30 prochains jours
                if ($nextBirthday->diffInDays(Carbon::now()) <= 30) {
                    $birthdays[] = [
                        'id' => $relatedUser->id,
                        'name' => $relatedUser->name,
                        'profile' => $relatedUser->profile,
                        'relation_type' => $relationship->relationshipType->name_fr,
                        'birth_date' => $birthDate->format('Y-m-d'),
                        'next_birthday' => $nextBirthday->format('Y-m-d'),
                        'days_until' => $nextBirthday->diffInDays(Carbon::now()),
                        'age_turning' => $nextBirthday->year - $birthDate->year,
                    ];
                }
            }
        }

        // Trier par proximité de l'anniversaire
        usort($birthdays, function($a, $b) {
            return $a['days_until'] <=> $b['days_until'];
        });

        return array_slice($birthdays, 0, 5);
    }

    private function formatTimeAgo(Carbon $date): string
    {
        $diff = $date->diffInMinutes(Carbon::now());

        if ($diff < 60) {
            return "Il y a {$diff} min";
        } elseif ($diff < 1440) { // 24 heures
            $hours = intval($diff / 60);
            return "Il y a {$hours}h";
        } elseif ($diff < 10080) { // 7 jours
            $days = intval($diff / 1440);
            return "Il y a {$days}j";
        } else {
            return $date->format('d/m/Y');
        }
    }

    private function getInitials(string $name): string
    {
        $words = explode(' ', $name);
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }

        return substr($initials, 0, 2);
    }
}
