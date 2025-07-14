<?php

namespace App\Services;

use App\Models\Network;
use App\Models\User;
use Illuminate\Support\Collection;

class NetworkService
{
    public function getUserNetwork(User $user): Collection
    {
        return Network::where('user_id', $user->id)
            ->orWhere('connected_user_id', $user->id)
            ->with(['user', 'connectedUser'])
            ->get();
    }

    public function getConnectedUsers(User $user): Collection
    {
        $networkIds = Network::where('user_id', $user->id)
            ->orWhere('connected_user_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('id');

        return User::whereIn('id', function ($query) use ($networkIds, $user) {
            $query->select('connected_user_id')
                ->from('networks')
                ->whereIn('id', $networkIds)
                ->where('user_id', $user->id);
        })->orWhereIn('id', function ($query) use ($networkIds, $user) {
            $query->select('user_id')
                ->from('networks')
                ->whereIn('id', $networkIds)
                ->where('connected_user_id', $user->id);
        })->with('profile')->get();
    }

    public function createConnection(User $user, int $connectedUserId, string $status = 'pending'): Network
    {
        return Network::create([
            'user_id' => $user->id,
            'connected_user_id' => $connectedUserId,
            'status' => $status,
        ]);
    }

    public function acceptConnection(Network $network): void
    {
        $network->update(['status' => 'accepted']);
    }

    public function rejectConnection(Network $network): void
    {
        $network->update(['status' => 'rejected']);
    }

    public function removeConnection(Network $network): void
    {
        $network->delete();
    }

    public function getPendingConnections(User $user): Collection
    {
        return Network::where('connected_user_id', $user->id)
            ->where('status', 'pending')
            ->with('user')
            ->get();
    }

    public function searchUsers(User $user, string $query): Collection
    {
        return User::where('id', '!=', $user->id)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->with('profile')
            ->limit(10)
            ->get();
    }
}
