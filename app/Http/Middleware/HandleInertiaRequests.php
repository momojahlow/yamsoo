<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Services\NotificationService;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();
        $unreadNotifications = 0;

        if ($user) {
            $notificationService = app(NotificationService::class);
            $unreadNotifications = $notificationService->getUnreadCount($user);
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    'unreadNotifications' => $unreadNotifications
                ]) : null,
            ],
            'ziggy' => fn (): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',

            // Token CSRF pour les formulaires
            'csrf_token' => csrf_token(),

            // Données de traduction
            'locale' => app()->getLocale(),
            'available_locales' => config('app.available_locales', ['fr' => 'Français', 'ar' => 'العربية']),
            'translations' => $this->getTranslations(),
        ];
    }

    /**
     * Charger les traductions pour la langue actuelle
     */
    private function getTranslations(): array
    {
        $locale = app()->getLocale();
        $translationPath = lang_path("{$locale}/common.php");

        if (file_exists($translationPath)) {
            return require $translationPath;
        }

        // Fallback vers le français si le fichier n'existe pas
        $fallbackPath = lang_path("fr/common.php");
        if (file_exists($fallbackPath)) {
            return require $fallbackPath;
        }

        return [];
    }
}
