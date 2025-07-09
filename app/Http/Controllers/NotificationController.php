<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $notifications = $this->notificationService->getUserNotifications($user);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return Inertia::render('Notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function show(Notification $notification): Response
    {
        return Inertia::render('Notifications/Show', [
            'notification' => $notification,
        ]);
    }

    public function markAsRead(Notification $notification): \Illuminate\Http\RedirectResponse
    {
        $this->notificationService->markAsRead($notification);
        return back()->with('success', 'Notification marquée comme lue.');
    }

    public function markAllAsRead(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $this->notificationService->markAllAsRead($user);
        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    public function destroy(Notification $notification): \Illuminate\Http\RedirectResponse
    {
        $notification->delete();
        return back()->with('success', 'Notification supprimée avec succès.');
    }
}
