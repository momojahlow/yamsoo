<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Family;
use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Ajouter middleware admin si nécessaire
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_messages' => Message::count(),
            'total_families' => Family::count(),
            'total_notifications' => Notification::count(),
        ];

        // Utilisateurs récents
        $recentUsers = User::with('profile')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Messages récents
        $recentMessages = Message::with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Admin', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentMessages' => $recentMessages,
        ]);
    }

    public function users(Request $request): Response
    {
        $users = User::with('profile')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    public function messages(Request $request): Response
    {
        $messages = Message::with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Messages', [
            'messages' => $messages,
        ]);
    }

    public function families(Request $request): Response
    {
        $families = Family::with(['user', 'relatedUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Families', [
            'families' => $families,
        ]);
    }

    public function deleteUser(User $user): \Illuminate\Http\RedirectResponse
    {
        $user->delete();
        return back()->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function deleteMessage(Message $message): \Illuminate\Http\RedirectResponse
    {
        $message->delete();
        return back()->with('success', 'Message supprimé avec succès.');
    }

    public function deleteFamily(Family $family): \Illuminate\Http\RedirectResponse
    {
        $family->delete();
        return back()->with('success', 'Relation familiale supprimée avec succès.');
    }
}
