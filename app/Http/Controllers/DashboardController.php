<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use App\Services\NotificationService;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private ProfileService $profileService,
        private NotificationService $notificationService,
        private MessageService $messageService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $profile = $this->profileService->getProfile($user);
        $notifications = $this->notificationService->getUserNotifications($user);
        $messages = $this->messageService->getMessages($user);
        $unreadNotifications = $this->notificationService->getUnreadCount($user);

        return Inertia::render('Dashboard', [
            'user' => $user,
            'profile' => $profile,
            'notifications' => $notifications,
            'messages' => $messages,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }
}
