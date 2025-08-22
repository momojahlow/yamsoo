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

// Routes pour la messagerie moderne
Route::middleware(['auth:sanctum'])->group(function () {
    // Conversations
    Route::post('/conversations', [MessagingController::class, 'createConversation']);
    Route::get('/conversations/{conversation}/messages', [MessagingController::class, 'getMessages']);
    Route::post('/conversations/{conversation}/messages', [MessagingController::class, 'sendMessage']);
    Route::post('/conversations/{conversation}/read', [MessagingController::class, 'markAsRead']);
    
    // Groupes familiaux
    Route::post('/conversations/family-group', [MessagingController::class, 'createFamilyGroup']);
    Route::get('/conversations/family-suggestions', [MessagingController::class, 'getFamilySuggestions']);
    
    // Recherche d'utilisateurs
    Route::get('/users/search', [MessagingController::class, 'searchUsers']);
});
