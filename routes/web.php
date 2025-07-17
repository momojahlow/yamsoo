<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\FamilyRelationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

// Routes d'authentification - utilisation de Laravel Breeze
// Les routes d'authentification sont définies dans routes/auth.php

Route::middleware('auth')->group(function () {
    Route::get('check-auth', [AuthController::class, 'checkAuth'])->name('check-auth');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route pour le profil
    Route::get('profil', [ProfileController::class, 'index'])->name('profile.index');
    Route::patch('profil', [ProfileController::class, 'update'])->name('profile.update');

    // Routes pour les familles
    Route::get('famille', [FamilyController::class, 'index'])->name('family');
    Route::get('famille/arbre', [FamilyController::class, 'tree'])->name('family.tree');
    Route::post('families/{family}/members', [FamilyController::class, 'addMember'])->name('families.add-member');

    // Routes pour les messages
    Route::get('messagerie', [MessageController::class, 'index'])->name('messages');
    Route::get('messagerie/{conversationId}', [MessageController::class, 'show'])->name('messages.conversation');

    // Routes pour les notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Routes pour les suggestions
    Route::get('suggestions', [SuggestionController::class, 'index'])->name('suggestions');
    Route::post('suggestions', [SuggestionController::class, 'store'])->name('suggestions.store');
    Route::patch('suggestions/{suggestion}', [SuggestionController::class, 'update'])->name('suggestions.update');
    Route::delete('suggestions/{suggestion}', [SuggestionController::class, 'destroy'])->name('suggestions.destroy');

    // Routes pour les réseaux
    Route::get('reseaux', [NetworkController::class, 'index'])->name('networks');
    Route::post('networks', [NetworkController::class, 'store'])->name('networks.store');
    Route::delete('networks/{network}', [NetworkController::class, 'destroy'])->name('networks.destroy');
    Route::get('networks/search', [NetworkController::class, 'search'])->name('networks.search');

    // Routes pour l'administration
    Route::get('admin', [AdminController::class, 'index'])->name('admin');
    Route::get('admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('admin/messages', [AdminController::class, 'messages'])->name('admin.messages');
    Route::get('admin/families', [AdminController::class, 'families'])->name('admin.families');
    Route::delete('admin/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::delete('admin/messages/{message}', [AdminController::class, 'deleteMessage'])->name('admin.messages.delete');
    Route::delete('admin/families/{family}', [AdminController::class, 'deleteFamily'])->name('admin.families.delete');

    // Routes pour les relations familiales
    Route::get('family-relations', [FamilyRelationController::class, 'index'])->name('family-relations.index');
    Route::post('family-relations', [FamilyRelationController::class, 'store'])->name('family-relations.store');
    Route::post('family-relations/{requestId}/accept', [FamilyRelationController::class, 'accept'])->name('family-relations.accept');
    Route::post('family-relations/{requestId}/reject', [FamilyRelationController::class, 'reject'])->name('family-relations.reject');
    Route::get('users/search', [FamilyRelationController::class, 'searchUserByEmail'])->name('users.search-by-email');

    // Routes CRUD pour les entités
    Route::resource('profiles', ProfileController::class);
    Route::resource('messages', MessageController::class);
    Route::resource('families', FamilyController::class);
    Route::resource('notifications', NotificationController::class);
    Route::resource('suggestions', SuggestionController::class);

    Route::resource('networks', NetworkController::class)->except(['index']);
});

require __DIR__.'/auth.php';

// Route de debug - à supprimer après résolution
Route::get('/debug/relations', function () {
    $user = Auth::user();

    $requests = \App\Models\RelationshipRequest::with(['requester', 'targetUser', 'relationshipType'])
        ->where('requester_id', $user->id)
        ->orWhere('target_user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    $relations = \App\Models\FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])
        ->where('user_id', $user->id)
        ->orWhere('related_user_id', $user->id)
        ->get();

    return response()->json([
        'user_id' => $user->id,
        'requests' => $requests,
        'relations' => $relations,
        'requests_count' => $requests->count(),
        'relations_count' => $relations->count()
    ]);
})->middleware('auth');



