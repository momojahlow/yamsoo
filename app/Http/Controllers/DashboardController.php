<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use App\Services\NotificationService;
use App\Services\MessageService;
use App\Services\AnalyticsService;
use App\Services\FamilyService;
use App\Services\NetworkService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private ProfileService $profileService,
        private NotificationService $notificationService,
        private MessageService $messageService,
        private AnalyticsService $analyticsService,
        private FamilyService $familyService,
        private NetworkService $networkService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $profile = $this->profileService->getProfile($user);
        $notifications = $this->notificationService->getUserNotifications($user);
        $messages = $this->messageService->getMessages($user);
        $unreadNotifications = $this->notificationService->getUnreadCount($user);

        // Nouvelles données avec les services ajoutés
        $userStats = $this->analyticsService->getUserStats($user);
        $family = $this->familyService->getUserFamily($user);
        $familyStats = $family ? $this->analyticsService->getFamilyStats($family) : null;
        $network = $this->networkService->getUserNetwork($user);
        $recentActivity = $this->analyticsService->getUserActivity($user, 7);

        return Inertia::render('dashboard', [
            'user' => $user,
            'profile' => $profile,
            'notifications' => $notifications,
            'messages' => $messages,
            'unreadNotifications' => $unreadNotifications,
            'userStats' => $userStats,
            'family' => $family,
            'familyStats' => $familyStats,
            'network' => $network,
            'recentActivity' => $recentActivity,
        ]);
    }
}
