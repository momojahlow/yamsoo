<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use App\Models\Family;
use App\Models\Notification;
use App\Models\FamilyRelationship;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    public function getUserStats(User $user): array
    {
        $stats = [
            'total_messages' => Message::where('user_id', $user->id)
                ->orWhere('recipient_id', $user->id)
                ->count(),
            'total_family_members' => $this->getFamilyMembersCount($user),
            'total_notifications' => $user->notifications()->count(),
            'unread_notifications' => $user->notifications()->whereNull('read_at')->count(),
            'total_relationships' => $this->getRelationshipsCount($user),
            'profile_completion' => $this->getProfileCompletionPercentage($user),
        ];

        return $stats;
    }

    public function getFamilyStats(Family $family): array
    {
        $stats = [
            'total_members' => $family->members()->count(),
            'active_members' => $family->members()
                ->whereHas('messages', function ($query) {
                    $query->where('created_at', '>=', now()->subDays(30));
                })
                ->count(),
            'total_messages' => Message::whereIn('user_id', $family->members()->pluck('id'))
                ->orWhereIn('recipient_id', $family->members()->pluck('id'))
                ->count(),
            'recent_activity' => $this->getRecentFamilyActivity($family),
        ];

        return $stats;
    }

    public function getGlobalStats(): array
    {
        $stats = [
            'total_users' => User::count(),
            'total_families' => Family::count(),
            'total_messages' => Message::count(),
            'total_relationships' => FamilyRelationship::where('status', 'accepted')->count(),
            'active_users_this_month' => User::whereHas('messages', function ($query) {
                $query->where('created_at', '>=', now()->startOfMonth());
            })->count(),
            'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return $stats;
    }

    public function getUserActivity(User $user, int $days = 30): array
    {
        $activity = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $day = $date->format('Y-m-d');

            $activity[$day] = [
                'messages_sent' => Message::where('user_id', $user->id)
                    ->whereDate('created_at', $date)
                    ->count(),
                'messages_received' => Message::where('recipient_id', $user->id)
                    ->whereDate('created_at', $date)
                    ->count(),
                'notifications' => $user->notifications()
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }

        return $activity;
    }

    public function getPopularRelationshipTypes(): Collection
    {
        return DB::table('family_relationships')
            ->join('relationship_types', 'family_relationships.relationship_type_id', '=', 'relationship_types.id')
            ->select('relationship_types.name', DB::raw('count(*) as count'))
            ->where('family_relationships.status', 'accepted')
            ->groupBy('relationship_types.id', 'relationship_types.name')
            ->orderBy('count', 'desc')
            ->get();
    }

    public function getUserGrowthData(int $months = 12): array
    {
        $growth = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('Y-m');

            $growth[$month] = [
                'new_users' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'active_users' => User::whereHas('messages', function ($query) use ($date) {
                    $query->whereYear('created_at', $date->year)
                          ->whereMonth('created_at', $date->month);
                })->count(),
            ];
        }

        return $growth;
    }

    public function getMessageStats(User $user): array
    {
        $stats = [
            'total_sent' => Message::where('user_id', $user->id)->count(),
            'total_received' => Message::where('recipient_id', $user->id)->count(),
            'this_month_sent' => Message::where('user_id', $user->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'this_month_received' => Message::where('recipient_id', $user->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'average_response_time' => $this->getAverageResponseTime($user),
        ];

        return $stats;
    }

    public function getFamilyActivityHeatmap(Family $family): array
    {
        $heatmap = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $heatmap[$hour] = Message::whereIn('user_id', $family->members()->pluck('id'))
                ->whereHour('created_at', $hour)
                ->count();
        }

        return $heatmap;
    }

    public function getTopActiveUsers(int $limit = 10): Collection
    {
        return User::withCount(['messages as message_count' => function ($query) {
            $query->where('created_at', '>=', now()->subDays(30));
        }])
        ->orderBy('message_count', 'desc')
        ->limit($limit)
        ->get();
    }

    public function getFamilySizeDistribution(): array
    {
        $distribution = DB::table('families')
            ->select(DB::raw('COUNT(*) as family_count, member_count'))
            ->join(DB::raw('(SELECT family_id, COUNT(*) as member_count FROM family_user GROUP BY family_id) as member_counts'),
                   'families.id', '=', 'member_counts.family_id')
            ->groupBy('member_count')
            ->orderBy('member_count')
            ->get()
            ->toArray();

        return $distribution;
    }

    private function getFamilyMembersCount(User $user): int
    {
        if ($user->family) {
            return $user->family->members()->count();
        }
        return 0;
    }

    private function getRelationshipsCount(User $user): int
    {
        return FamilyRelationship::where('user_id', $user->id)
            ->orWhere('related_user_id', $user->id)
            ->where('status', 'accepted')
            ->count();
    }

    private function getProfileCompletionPercentage(User $user): int
    {
        $profile = $user->profile;
        if (!$profile) {
            return 0;
        }

        $fields = ['first_name', 'last_name', 'phone', 'address', 'birth_date', 'gender', 'bio'];
        $completed = 0;

        foreach ($fields as $field) {
            if (!empty($profile->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }

    private function getRecentFamilyActivity(Family $family): array
    {
        $recentMessages = Message::whereIn('user_id', $family->members()->pluck('id'))
            ->orWhereIn('recipient_id', $family->members()->pluck('id'))
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $recentRelationships = FamilyRelationship::whereIn('user_id', $family->members()->pluck('id'))
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return [
            'messages_last_7_days' => $recentMessages,
            'new_relationships_last_30_days' => $recentRelationships,
        ];
    }

    private function getAverageResponseTime(User $user): float
    {
        // Calculer le temps de réponse moyen pour les messages reçus
        $messages = Message::where('recipient_id', $user->id)
            ->whereHas('sender.messages', function ($query) use ($user) {
                $query->where('recipient_id', $user->id);
            })
            ->with('sender.messages')
            ->get();

        if ($messages->isEmpty()) {
            return 0;
        }

        $totalTime = 0;
        $responseCount = 0;

        foreach ($messages as $message) {
            $response = $message->sender->messages()
                ->where('recipient_id', $user->id)
                ->where('created_at', '>', $message->created_at)
                ->first();

            if ($response) {
                $totalTime += $message->created_at->diffInMinutes($response->created_at);
                $responseCount++;
            }
        }

        return $responseCount > 0 ? round($totalTime / $responseCount, 2) : 0;
    }
}
