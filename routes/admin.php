<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ContentModerationController;
use App\Http\Controllers\Admin\SystemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes d'Administration
|--------------------------------------------------------------------------
|
| Routes pour l'administration de Yamsoo avec authentification séparée
| Les administrateurs ont leur propre système d'authentification
|
*/

// Routes d'authentification admin (non protégées)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Routes protégées par l'authentification admin
Route::middleware(['admin.auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Tableau de bord principal
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats/system', [AdminDashboardController::class, 'systemStats'])->name('stats.system');

    // Route de test temporaire
    Route::get('/test', function () {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        return \Inertia\Inertia::render('Admin/Test', ['admin' => $admin]);
    })->name('test');

    // Profil admin
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('change-password');
    Route::put('/change-password', [AuthController::class, 'changePassword'])->name('change-password.update');
    
    // Gestion des utilisateurs
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/stats', [UserManagementController::class, 'stats'])->name('stats');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::patch('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{user}/change-role', [UserManagementController::class, 'changeRole'])->name('change-role');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });
    
    // Modération de contenu
    Route::prefix('moderation')->name('moderation.')->group(function () {
        Route::get('/messages', [ContentModerationController::class, 'messages'])->name('messages');
        Route::get('/photos', [ContentModerationController::class, 'photos'])->name('photos');
        Route::get('/reports', [ContentModerationController::class, 'reports'])->name('reports');
        Route::patch('/messages/{message}/moderate', [ContentModerationController::class, 'moderateMessage'])->name('moderate-message');
        Route::patch('/photos/{photo}/moderate', [ContentModerationController::class, 'moderatePhoto'])->name('moderate-photo');
    });
    
    // Système et maintenance
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/info', [SystemController::class, 'info'])->name('info');
        Route::get('/logs', [SystemController::class, 'logs'])->name('logs');
        Route::post('/cache/clear', [SystemController::class, 'clearCache'])->name('cache.clear');
        Route::post('/maintenance/enable', [SystemController::class, 'enableMaintenance'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [SystemController::class, 'disableMaintenance'])->name('maintenance.disable');
    });
    
});

// Routes pour super administrateurs uniquement
Route::middleware(['auth', 'admin:super_admin'])->prefix('admin/super')->name('admin.super.')->group(function () {
    
    // Gestion des administrateurs
    Route::get('/admins', [UserManagementController::class, 'admins'])->name('admins');
    Route::post('/admins/{user}/promote', [UserManagementController::class, 'promoteToAdmin'])->name('promote-admin');
    Route::post('/admins/{user}/demote', [UserManagementController::class, 'demoteFromAdmin'])->name('demote-admin');
    
    // Configuration système
    Route::get('/config', [SystemController::class, 'config'])->name('config');
    Route::put('/config', [SystemController::class, 'updateConfig'])->name('config.update');
    
    // Sauvegarde et restauration
    Route::post('/backup', [SystemController::class, 'createBackup'])->name('backup');
    Route::get('/backups', [SystemController::class, 'listBackups'])->name('backups');
    Route::post('/restore/{backup}', [SystemController::class, 'restore'])->name('restore');
    
});
