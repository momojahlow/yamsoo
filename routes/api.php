<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessagingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API pour le polling des messages (fallback si Reverb ne fonctionne pas)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/conversations/{conversation}/messages/since/{messageId}', [App\Http\Controllers\SimpleMessagingController::class, 'getMessagesSince']);
});

// Routes pour la messagerie moderne unifiée
Route::middleware(['auth:sanctum'])->prefix('conversations')->group(function () {
    // Liste des conversations
    Route::get('/', [App\Http\Controllers\Api\ConversationController::class, 'index']);

    // Membres de famille disponibles pour les groupes
    Route::get('/family-members', [App\Http\Controllers\Api\ConversationController::class, 'getFamilyMembers']);

    // Créer des conversations
    Route::post('/private', [App\Http\Controllers\Api\ConversationController::class, 'createPrivate']);
    Route::post('/group', [App\Http\Controllers\Api\ConversationController::class, 'createGroup']);

    // Messages
    Route::get('/{conversation}/messages', [App\Http\Controllers\Api\ConversationController::class, 'getMessages']);
    Route::post('/{conversation}/messages', [App\Http\Controllers\Api\ConversationController::class, 'sendMessage']);

    // Gestion des groupes
    Route::post('/{conversation}/participants', [App\Http\Controllers\Api\ConversationController::class, 'addParticipant']);
    Route::delete('/{conversation}/participants/{user}', [App\Http\Controllers\Api\ConversationController::class, 'removeParticipant']);
    Route::patch('/{conversation}/participants/{user}/admin', [App\Http\Controllers\Api\ConversationController::class, 'toggleAdmin']);

    // Actions sur les conversations
    Route::patch('/{conversation}', [App\Http\Controllers\Api\ConversationController::class, 'update']);
    Route::post('/{conversation}/leave', [App\Http\Controllers\Api\ConversationController::class, 'leave']);
    Route::post('/{conversation}/read', [App\Http\Controllers\Api\ConversationController::class, 'markAsRead']);

    // Préférences de notification
    Route::get('/{conversation}/notification-settings', [App\Http\Controllers\SimpleMessagingController::class, 'getNotificationSettings']);
    Route::patch('/{conversation}/notification-settings', [App\Http\Controllers\SimpleMessagingController::class, 'updateNotificationSettings']);
});

// Routes pour les réactions aux messages
Route::middleware(['auth:sanctum'])->prefix('messages')->group(function () {
    Route::post('/{message}/reactions', [App\Http\Controllers\Api\MessageReactionController::class, 'toggle']);
    Route::get('/{message}/reactions', [App\Http\Controllers\Api\MessageReactionController::class, 'index']);
});

// API pour l'arbre familial
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('family-relations', [App\Http\Controllers\FamilyTreeController::class, 'getFamilyRelations']);
});

// Anciennes routes pour compatibilité
Route::middleware(['auth:sanctum'])->group(function () {
    // Groupes familiaux (compatibilité)
    Route::post('/conversations/family-group', [MessagingController::class, 'createFamilyGroup']);
    Route::get('/conversations/family-suggestions', [MessagingController::class, 'getFamilySuggestions']);

    // Recherche d'utilisateurs
    Route::get('/users/search', [MessagingController::class, 'searchUsers']);
});
