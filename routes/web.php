<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\FamilyTreeController;
use App\Http\Controllers\FamilyRelationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PhotoAlbumController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\LanguageController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
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
    ]);
})->name('home');

// Route pour récupérer le token CSRF
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

// Route temporaire pour debug
Route::get('/debug-relations', function () {
    $output = [];

    try {
        // 1. Lister tous les utilisateurs
        $output[] = "=== TOUS LES UTILISATEURS ===";
        $users = \App\Models\User::all();
        $amina_id = null;
        $fatima_id = null;
        $ahmed_id = null;
        $mohamed_id = null;

        foreach ($users as $user) {
            $output[] = "{$user->name} (ID: {$user->id}) - {$user->email}";

            if (stripos($user->name, 'Amina') !== false) $amina_id = $user->id;
            if (stripos($user->name, 'Fatima') !== false) $fatima_id = $user->id;
            if (stripos($user->name, 'Ahmed') !== false) $ahmed_id = $user->id;
            if (stripos($user->name, 'Mohammed') !== false) $mohamed_id = $user->id;
        }

        $output[] = "";
        $output[] = "=== UTILISATEURS CLÉS ===";
        $output[] = "Amina ID: " . ($amina_id ?? "NON TROUVÉ");
        $output[] = "Fatima ID: " . ($fatima_id ?? "NON TROUVÉ");
        $output[] = "Ahmed ID: " . ($ahmed_id ?? "NON TROUVÉ");
        $output[] = "Mohamed ID: " . ($mohamed_id ?? "NON TROUVÉ");

        // 2. Lister tous les types de relations
        $output[] = "";
        $output[] = "=== TYPES DE RELATIONS ===";
        $relationTypes = \App\Models\RelationshipType::all();
        foreach ($relationTypes as $type) {
            $output[] = "{$type->code} - {$type->name} ({$type->name_fr})";
        }

        // 3. Lister TOUTES les relations existantes
        $output[] = "";
        $output[] = "=== TOUTES LES RELATIONS EXISTANTES ===";
        $relations = \App\Models\FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();

        if ($relations->isEmpty()) {
            $output[] = "❌ AUCUNE RELATION TROUVÉE!";
        } else {
            foreach ($relations as $rel) {
                $output[] = "{$rel->user->name} → {$rel->relatedUser->name} : {$rel->relationshipType->code} ({$rel->relationshipType->name}) [{$rel->status}]";
            }
        }

        // 4. Relations spécifiques d'Amina
        if ($amina_id) {
            $output[] = "";
            $output[] = "=== RELATIONS D'AMINA ===";
            $aminaRelations = \App\Models\FamilyRelationship::where('user_id', $amina_id)
                ->orWhere('related_user_id', $amina_id)
                ->with(['user', 'relatedUser', 'relationshipType'])->get();

            if ($aminaRelations->isEmpty()) {
                $output[] = "❌ AUCUNE RELATION POUR AMINA!";
            } else {
                foreach ($aminaRelations as $rel) {
                    if ($rel->user_id === $amina_id) {
                        $output[] = "Amina → {$rel->relatedUser->name} : {$rel->relationshipType->code}";
                    } else {
                        $output[] = "{$rel->user->name} → Amina : {$rel->relationshipType->code}";
                    }
                }
            }
        }

        // 5. Relation Ahmed ↔ Fatima
        if ($ahmed_id && $fatima_id) {
            $output[] = "";
            $output[] = "=== RELATION AHMED ↔ FATIMA ===";
            $ahmedFatimaRelation = \App\Models\FamilyRelationship::where(function($query) use ($ahmed_id, $fatima_id) {
                $query->where('user_id', $ahmed_id)->where('related_user_id', $fatima_id);
            })->orWhere(function($query) use ($ahmed_id, $fatima_id) {
                $query->where('user_id', $fatima_id)->where('related_user_id', $ahmed_id);
            })->with(['user', 'relatedUser', 'relationshipType'])->get();

            if ($ahmedFatimaRelation->isEmpty()) {
                $output[] = "❌ AUCUNE RELATION AHMED ↔ FATIMA TROUVÉE!";
            } else {
                foreach ($ahmedFatimaRelation as $rel) {
                    $output[] = "✅ {$rel->user->name} → {$rel->relatedUser->name} : {$rel->relationshipType->code}";
                }
            }
        }

        // 6. Relation Amina ↔ Ahmed
        if ($amina_id && $ahmed_id) {
            $output[] = "";
            $output[] = "=== RELATION AMINA ↔ AHMED ===";
            $aminaAhmedRelation = \App\Models\FamilyRelationship::where(function($query) use ($amina_id, $ahmed_id) {
                $query->where('user_id', $amina_id)->where('related_user_id', $ahmed_id);
            })->orWhere(function($query) use ($amina_id, $ahmed_id) {
                $query->where('user_id', $ahmed_id)->where('related_user_id', $amina_id);
            })->with(['user', 'relatedUser', 'relationshipType'])->get();

            if ($aminaAhmedRelation->isEmpty()) {
                $output[] = "❌ AUCUNE RELATION AMINA ↔ AHMED TROUVÉE!";
            } else {
                foreach ($aminaAhmedRelation as $rel) {
                    $output[] = "✅ {$rel->user->name} → {$rel->relatedUser->name} : {$rel->relationshipType->code}";
                }
            }
        }

        // 7. Suggestions actuelles pour Amina
        if ($amina_id) {
            $output[] = "";
            $output[] = "=== SUGGESTIONS ACTUELLES POUR AMINA ===";
            $suggestions = \App\Models\Suggestion::where('user_id', $amina_id)
                ->with('suggestedUser')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($suggestions->isEmpty()) {
                $output[] = "❌ AUCUNE SUGGESTION POUR AMINA!";
            } else {
                foreach ($suggestions as $suggestion) {
                    $output[] = "- {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code}";
                    $output[] = "  Raison: {$suggestion->reason}";
                    $output[] = "  Type: {$suggestion->type}";

                    if (stripos($suggestion->suggestedUser->name, 'Fatima') !== false) {
                        $output[] = "  🎯 FATIMA TROUVÉE: {$suggestion->suggested_relation_code}";
                        if ($suggestion->suggested_relation_code === 'mother') {
                            $output[] = "  ✅ CORRECT!";
                        } else {
                            $output[] = "  ❌ INCORRECT! Devrait être 'mother'";
                        }
                    }
                }
            }
        }

        $output[] = "";
        $output[] = "=== ANALYSE ===";
        $output[] = "Pour que Fatima soit suggérée comme 'mother' à Amina, il faut:";
        $output[] = "1. ✓ Amina → Ahmed : daughter (fille)";
        $output[] = "2. ✓ Ahmed → Fatima : husband (mari)";
        $output[] = "3. ✓ DÉDUCTION: Amina (enfant) + Fatima (conjoint) = Fatima est mère";
        $output[] = "4. ✓ CAS 1 dans SuggestionService: enfant + conjoint → parent";
        $output[] = "5. ✓ RÉSULTAT ATTENDU: mother";

    } catch (\Exception $e) {
        $output[] = "❌ ERREUR: " . $e->getMessage();
        $output[] = "Trace: " . $e->getTraceAsString();
    }

    return response('<pre>' . implode("\n", $output) . '</pre>');
});

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
    Route::get('profil/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profil/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

    // Routes pour les familles
    Route::get('famille', [FamilyController::class, 'index'])->name('family');
    Route::get('famille/arbre', [FamilyTreeController::class, 'index'])->name('family.tree');
    Route::post('families/{family}/members', [FamilyController::class, 'addMember'])->name('families.add-member');

    // API pour l'arbre familial
    Route::get('api/family-relations', [FamilyTreeController::class, 'getFamilyRelations'])->name('api.family.relations');

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
    Route::post('suggestions/refresh', [SuggestionController::class, 'refresh'])->name('suggestions.refresh');
    Route::patch('suggestions/{suggestion}', [SuggestionController::class, 'update'])->name('suggestions.update');
    // Route principale
    Route::post('suggestions/{suggestion}/send-request', [SuggestionController::class, 'sendRelationRequest'])->name('suggestions.send-request');

    // Route alternative pour debug
    Route::post('suggestions/{id}/send-request-alt', function ($id) {
        try {
            $suggestion = \App\Models\Suggestion::findOrFail($id);

            // Simuler l'action du contrôleur
            return response()->json([
                'success' => true,
                'message' => 'Demande envoyée avec succès (route alternative)',
                'suggestion_id' => $suggestion->id,
                'user' => $suggestion->user->name,
                'suggested_user' => $suggestion->suggestedUser->name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    });

    // Route de test simple pour debug
    Route::post('suggestions/{id}/send-request-test', function ($id) {
        return response()->json([
            'success' => true,
            'message' => "Test réussi pour suggestion ID: {$id}",
            'data' => request()->all()
        ]);
    });
    Route::patch('suggestions/{suggestion}/accept-with-correction', [SuggestionController::class, 'acceptWithCorrection'])->name('suggestions.accept-with-correction');
    Route::delete('suggestions/{suggestion}', [SuggestionController::class, 'destroy'])->name('suggestions.destroy');

    // Route de test pour debug (à supprimer en production)
    Route::post('suggestions/{suggestion}/test-send-request', function (\App\Models\Suggestion $suggestion) {
        return response()->json([
            'success' => true,
            'message' => 'Route de test fonctionnelle',
            'suggestion_id' => $suggestion->id,
            'user' => $suggestion->user->name,
            'suggested_user' => $suggestion->suggestedUser->name,
            'relation' => $suggestion->suggested_relation_name,
            'url' => request()->url(),
            'method' => request()->method(),
        ]);
    })->name('suggestions.test-send-request');

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

    // Routes pour la démo des layouts
    Route::get('layout-demo', [App\Http\Controllers\LayoutDemoController::class, 'index'])->name('layout.demo');
    Route::get('layout-demo/kui', [App\Http\Controllers\LayoutDemoController::class, 'kuiLayout'])->name('layout.demo.kui');
    Route::get('layout-demo/starter', [App\Http\Controllers\LayoutDemoController::class, 'starterLayout'])->name('layout.demo.starter');
    Route::get('layout-demo/kwd', [App\Http\Controllers\LayoutDemoController::class, 'kwdLayout'])->name('layout.demo.kwd');
    Route::get('auth-layout-demo', function () {
        return Inertia::render('AuthLayoutDemo');
    })->name('auth.layout.demo');
    Route::get('layout-features', function () {
        return Inertia::render('LayoutFeatures');
    })->name('layout.features');
    Route::get('settings', function () {
        return Inertia::render('settings/index');
    })->name('settings');

    // Routes pour les relations familiales
    Route::get('family-relations', [FamilyRelationController::class, 'index'])->name('family-relations.index');
    Route::get('family-relations/suggestions', function () {
        return inertia('Relations/Suggestions');
    })->name('family-relations.suggestions');
    Route::post('family-relations', [FamilyRelationController::class, 'store'])->name('family-relations.store');
    Route::post('family-relations/{requestId}/accept', [FamilyRelationController::class, 'accept'])->name('family-relations.accept');
    Route::post('family-relations/{requestId}/reject', [FamilyRelationController::class, 'reject'])->name('family-relations.reject');
    Route::delete('family-relations/{requestId}', [FamilyRelationController::class, 'cancel'])->name('family-relations.cancel');
    Route::get('users/search', [FamilyRelationController::class, 'searchUserByEmail'])->name('users.search-by-email');

    // Routes pour l'analyse Yamsoo
    Route::prefix('yamsoo')->name('yamsoo.')->group(function () {
        Route::post('/analyze-relation', [App\Http\Controllers\YamsooAnalysisController::class, 'analyzeRelation'])->name('analyze-relation');
        Route::get('/relations-summary', [App\Http\Controllers\YamsooAnalysisController::class, 'getRelationsSummary'])->name('relations-summary');
        Route::post('/analyze-multiple', [App\Http\Controllers\YamsooAnalysisController::class, 'analyzeMultipleRelations'])->name('analyze-multiple');
        Route::get('/suggestions', [App\Http\Controllers\YamsooAnalysisController::class, 'getRelationSuggestions'])->name('suggestions');
    });

    // Route de test pour le dropdown utilisateur
    Route::get('/test-dropdown', function () {
        return inertia('TestDropdown');
    })->name('test.dropdown');

    // Route de test pour le middleware locale
    Route::get('/test-locale', function () {
        return inertia('TestLocale');
    })->name('test.locale');

    // Route de test pour le modèle Profile
    Route::get('/test-profile', function () {
        return inertia('TestProfile');
    })->name('test.profile');

    // Route de test pour le dashboard moderne
    Route::get('/modern-dashboard', [DashboardController::class, 'index'])->name('modern.dashboard');

    // Route de test pour les badges dynamiques
    Route::get('/test-badges', function () {
        return inertia('TestBadges');
    })->name('test.badges');

    // Route de test pour l'affichage des photos
    Route::get('/test-photo-display', function () {
        return inertia('TestPhotoDisplay');
    })->name('test.photo.display');

    // Route de test pour les suggestions
    Route::get('/test-suggestions', function () {
        return inertia('TestSuggestions');
    })->name('test.suggestions');

    // Route pour créer des données de test pour les photos
    Route::post('/test-photo-data', function () {
        try {
            // Exécuter le seeder
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PhotoAlbumTestSeeder']);

            return redirect()->back()->with('success', 'Données de test créées avec succès !');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erreur lors de la création des données : ' . $e->getMessage()]);
        }
    })->name('test.photo.data');

    // Route pour nettoyer les profils en double
    Route::post('/cleanup-profiles', function () {
        try {
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CleanupProfilesSeeder']);

            return redirect()->back()->with('success', 'Profils nettoyés avec succès !');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erreur lors du nettoyage : ' . $e->getMessage()]);
        }
    })->name('cleanup.profiles');

    // Route pour le seeding optimisé complet
    Route::post('/optimized-seed', function () {
        try {
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\OptimizedDatabaseSeeder']);

            return redirect()->back()->with('success', 'Seeding optimisé terminé avec succès !');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erreur lors du seeding : ' . $e->getMessage()]);
        }
    })->name('optimized.seed');

    // Route pour générer des suggestions de test
    Route::post('/generate-suggestions', function () {
        try {
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\SuggestionTestSeeder']);

            return redirect()->back()->with('success', 'Suggestions de test générées avec succès !');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erreur lors de la génération : ' . $e->getMessage()]);
        }
    })->name('generate.suggestions');

    // Route de test pour les albums photo modernes
    Route::get('/test-albums', function () {
        // Créer des albums de test si nécessaire
        $user = Auth::user();

        // Vérifier si l'utilisateur a des albums
        $albumsCount = \App\Models\PhotoAlbum::where('user_id', $user->id)->count();

        if ($albumsCount === 0) {
            // Créer quelques albums de test
            \App\Models\PhotoAlbum::create([
                'user_id' => $user->id,
                'title' => 'Vacances d\'été 2024',
                'description' => 'Nos meilleures photos de vacances en famille',
                'privacy' => 'family',
                'is_default' => false,
                'photos_count' => 15,
            ]);

            \App\Models\PhotoAlbum::create([
                'user_id' => $user->id,
                'title' => 'Moments en famille',
                'description' => 'Les petits moments du quotidien qui comptent',
                'privacy' => 'private',
                'is_default' => true,
                'photos_count' => 8,
            ]);

            \App\Models\PhotoAlbum::create([
                'user_id' => $user->id,
                'title' => 'Événements publics',
                'description' => 'Photos des événements familiaux à partager',
                'privacy' => 'public',
                'is_default' => false,
                'photos_count' => 23,
            ]);
        }

        return redirect()->route('photo-albums.index');
    })->name('test.albums');

    // Routes pour les albums photo
    Route::resource('photo-albums', PhotoAlbumController::class);
    Route::get('users/{user}/photo-albums', [PhotoAlbumController::class, 'index'])->name('users.photo-albums');

    // Routes pour les photos
    Route::get('photo-albums/{album}/photos', [PhotoController::class, 'index'])->name('albums.photos.index');
    Route::get('photo-albums/{album}/photos/create', [PhotoController::class, 'create'])->name('albums.photos.create');
    Route::post('photo-albums/{album}/photos', [PhotoController::class, 'store'])->name('albums.photos.store');
    Route::resource('photos', PhotoController::class)->except(['index', 'create', 'store']);

    // Routes CRUD pour les entités
    Route::resource('profiles', ProfileController::class);
    Route::resource('messages', MessageController::class);
    Route::resource('families', FamilyController::class);
    // Route::resource('notifications', NotificationController::class); // Supprimé - routes définies individuellement ci-dessus
    // Route::resource('suggestions', SuggestionController::class); // Supprimé - routes définies individuellement ci-dessus

    Route::resource('networks', NetworkController::class)->except(['index']);
});

require __DIR__.'/auth.php';

// Routes de debug (à supprimer en production)
if (app()->environment(['local', 'staging'])) {
    require __DIR__.'/debug.php';
}

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

// Routes de messagerie
Route::middleware('auth')->group(function () {
    // Interface de messagerie
    Route::get('/messages', [App\Http\Controllers\MessagingController::class, 'index'])->name('messages.index');

    // API Routes pour la messagerie
    Route::prefix('api')->group(function () {
        // Conversations
        Route::get('/conversations/{conversation}/messages', [App\Http\Controllers\MessagingController::class, 'getMessages']);
        Route::post('/conversations/{conversation}/messages', [App\Http\Controllers\MessagingController::class, 'sendMessage']);
        Route::post('/conversations', [App\Http\Controllers\MessagingController::class, 'createConversation']);

        // Recherche
        Route::get('/users/search', [App\Http\Controllers\MessagingController::class, 'searchUsers']);
        Route::get('/messages/search', [App\Http\Controllers\MessagingController::class, 'searchMessages']);

        // Statistiques
        Route::get('/messages/stats', [App\Http\Controllers\MessagingController::class, 'getStats']);

        // Fonctionnalités familiales
        Route::post('/conversations/family-group', [App\Http\Controllers\MessagingController::class, 'createFamilyGroup']);
        Route::get('/conversations/family-suggestions', [App\Http\Controllers\MessagingController::class, 'getFamilySuggestions']);
    });
});

// Routes de langue (sans préfixe pour la compatibilité)
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/api/languages', [LanguageController::class, 'getAvailableLanguages'])->name('language.available');

// Routes localisées
Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'fr|ar'], 'middleware' => 'setlocale'], function () {
    // Page d'accueil localisée
    Route::get('/', function () {
        return redirect('/');
    });
});

// Routes publiques
Route::get('/conditions-generales', function () {
    return Inertia::render('TermsOfService');
})->name('terms-of-service');

Route::get('/terms', function () {
    return redirect()->route('terms-of-service');
})->name('terms');
