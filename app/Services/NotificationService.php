<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function getUserNotifications(User $user): Collection
    {
        return $user->notifications()->orderBy('created_at', 'desc')->get();
    }

    public function createNotification(User $user, string $type, string $message, array $data = []): \App\Models\Notification
    {
        return $user->notifications()->create([
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'read_at' => null,
        ]);
    }

    public function markAsRead(\App\Models\Notification $notification): void
    {
        $notification->update(['read_at' => now()]);
    }

    public function markAllAsRead(User $user): void
    {
        $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function getUnreadCount(User $user): int
    {
        return $user->notifications()->whereNull('read_at')->count();
    }
}
