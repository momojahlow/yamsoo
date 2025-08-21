<?php

use Illuminate\Support\Facades\Route;
use App\Models\Suggestion;
use App\Models\User;

// Route de diagnostic pour les suggestions
Route::get('/debug-suggestions', function () {
    $output = [];

    try {
        // 1. Compter les suggestions
        $suggestionCount = Suggestion::count();
        $output[] = "=== DIAGNOSTIC SUGGESTIONS ===";
        $output[] = "Total suggestions: {$suggestionCount}";

        // 2. Lister toutes les suggestions
        if ($suggestionCount > 0) {
            $suggestions = Suggestion::with(['user', 'suggestedUser'])->get();
            $output[] = "\n=== LISTE DES SUGGESTIONS ===";

            foreach ($suggestions as $suggestion) {
                $userName = $suggestion->user ? $suggestion->user->name : 'N/A';
                $suggestedUserName = $suggestion->suggestedUser ? $suggestion->suggestedUser->name : 'N/A';

                $output[] = "ID: {$suggestion->id}";
                $output[] = "  User: {$userName}";
                $output[] = "  Suggested: {$suggestedUserName}";
                $output[] = "  Relation: {$suggestion->suggested_relation_code}";
                $output[] = "  Status: {$suggestion->status}";
                $output[] = "  ---";
            }
        } else {
            $output[] = "❌ AUCUNE SUGGESTION TROUVÉE";

            // Créer une suggestion de test
            $users = User::limit(2)->get();
            if ($users->count() >= 2) {
                $suggestion = Suggestion::create([
                    'user_id' => $users[0]->id,
                    'suggested_user_id' => $users[1]->id,
                    'suggested_relation_code' => 'brother',
                    'suggested_relation_name' => 'Frère',
                    'reason' => 'Suggestion de test créée automatiquement',
                    'type' => 'automatic',
                    'status' => 'pending',
                ]);

                $output[] = "✅ Suggestion de test créée avec ID: {$suggestion->id}";
            } else {
                $output[] = "❌ Pas assez d'utilisateurs pour créer une suggestion";
            }
        }

        // 3. Vérifier les utilisateurs
        $userCount = User::count();
        $output[] = "\n=== UTILISATEURS ===";
        $output[] = "Total utilisateurs: {$userCount}";

        if ($userCount > 0) {
            $users = User::limit(5)->get();
            foreach ($users as $user) {
                $output[] = "- ID: {$user->id}, Name: {$user->name}, Email: {$user->email}";
            }
        }

        // 4. Test de la route send-request
        $output[] = "\n=== TEST ROUTE ===";
        $firstSuggestion = Suggestion::first();
        if ($firstSuggestion) {
            $output[] = "✅ Première suggestion trouvée: ID {$firstSuggestion->id}";
            $output[] = "Route à tester: /suggestions/{$firstSuggestion->id}/send-request";
        } else {
            $output[] = "❌ Aucune suggestion pour tester la route";
        }

    } catch (\Exception $e) {
        $output[] = "❌ ERREUR: " . $e->getMessage();
        $output[] = "Trace: " . $e->getTraceAsString();
    }

    return response('<pre>' . implode("\n", $output) . '</pre>');
});

// Route pour créer des suggestions de test rapidement
Route::get('/create-test-suggestions', function () {
    try {
        $users = User::limit(4)->get();

        if ($users->count() < 2) {
            return response('❌ Il faut au moins 2 utilisateurs');
        }

        // Nettoyer les anciennes suggestions
        Suggestion::truncate();

        $suggestions = [];
        $relations = ['father', 'mother', 'brother', 'sister', 'son', 'daughter'];
        $relationNames = [
            'father' => 'Père',
            'mother' => 'Mère',
            'brother' => 'Frère',
            'sister' => 'Sœur',
            'son' => 'Fils',
            'daughter' => 'Fille'
        ];

        // Créer des suggestions entre les utilisateurs
        for ($i = 0; $i < $users->count(); $i++) {
            for ($j = 0; $j < $users->count(); $j++) {
                if ($i !== $j) {
                    $relation = $relations[array_rand($relations)];
                    $suggestions[] = [
                        'user_id' => $users[$i]->id,
                        'suggested_user_id' => $users[$j]->id,
                        'suggested_relation_code' => $relation,
                        'suggested_relation_name' => $relationNames[$relation],
                        'reason' => 'Suggestion de test générée automatiquement',
                        'type' => 'automatic',
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        Suggestion::insert($suggestions);

        return response("✅ " . count($suggestions) . " suggestions créées avec succès!");

    } catch (\Exception $e) {
        return response("❌ Erreur: " . $e->getMessage());
    }
});

// Route pour lister toutes les routes disponibles
Route::get('/debug-routes', function () {
    $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())->map(function ($route) {
        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
            'middleware' => $route->middleware(),
        ];
    });

    // Filtrer les routes de suggestions
    $suggestionRoutes = $routes->filter(function ($route) {
        return str_contains($route['uri'], 'suggestions') || str_contains($route['name'] ?? '', 'suggestions');
    });

    $output = [];
    $output[] = "=== ROUTES DE SUGGESTIONS ===";

    foreach ($suggestionRoutes as $route) {
        $output[] = "Method: {$route['method']}";
        $output[] = "URI: {$route['uri']}";
        $output[] = "Name: {$route['name']}";
        $output[] = "Middleware: " . implode(', ', $route['middleware']);
        $output[] = "---";
    }

    return response('<pre>' . implode("\n", $output) . '</pre>');
});

// Route pour tester l'authentification
Route::get('/debug-auth', function () {
    $output = [];
    $output[] = "=== ÉTAT AUTHENTIFICATION ===";

    if (\Illuminate\Support\Facades\Auth::check()) {
        $user = \Illuminate\Support\Facades\Auth::user();
        $output[] = "✅ Utilisateur connecté: {$user->name} (ID: {$user->id})";
        $output[] = "Email: {$user->email}";
        $output[] = "Email vérifié: " . ($user->email_verified_at ? 'Oui' : 'Non');
        $output[] = "Créé le: {$user->created_at}";
    } else {
        $output[] = "❌ Aucun utilisateur connecté";
    }

    return response('<pre>' . implode("\n", $output) . '</pre>');
});

// Route pour tester directement l'URL problématique
Route::post('/test-suggestion-request/{id}', function ($id) {
    return response()->json([
        'success' => true,
        'message' => "Route de test fonctionnelle pour suggestion ID: {$id}",
        'url_called' => request()->url(),
        'method' => request()->method(),
    ]);
});

// Route pour créer une suggestion et tester immédiatement
Route::get('/create-and-test-suggestion', function () {
    try {
        // S'assurer qu'un utilisateur est connecté
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return response('❌ Vous devez être connecté pour tester');
        }

        $user = \Illuminate\Support\Facades\Auth::user();

        // Créer une suggestion de test
        $otherUser = \App\Models\User::where('id', '!=', $user->id)->first();

        if (!$otherUser) {
            return response('❌ Il faut au moins 2 utilisateurs');
        }

        // Supprimer les anciennes suggestions de test
        \App\Models\Suggestion::where('user_id', $user->id)->delete();

        $suggestion = \App\Models\Suggestion::create([
            'user_id' => $user->id,
            'suggested_user_id' => $otherUser->id,
            'suggested_relation_code' => 'brother',
            'suggested_relation_name' => 'Frère',
            'reason' => 'Suggestion de test pour debug',
            'type' => 'automatic',
            'confidence_score' => 85,
            'status' => 'pending',
        ]);

        $output = [];
        $output[] = "✅ Suggestion créée avec ID: {$suggestion->id}";
        $output[] = "User: {$user->name}";
        $output[] = "Suggested: {$otherUser->name}";
        $output[] = "Relation: {$suggestion->suggested_relation_name}";
        $output[] = "";
        $output[] = "🔗 URL à tester: /suggestions/{$suggestion->id}/send-request";
        $output[] = "📝 Méthode: POST";
        $output[] = "📋 Données: {\"relation_code\": \"brother\"}";

        return response('<pre>' . implode("\n", $output) . '</pre>');

    } catch (\Exception $e) {
        return response('❌ Erreur: ' . $e->getMessage());
    }
});

// Route pour vérifier les types de relations disponibles
Route::get('/debug-relation-types', function () {
    try {
        $relations = \App\Models\RelationshipType::all(['name', 'display_name_fr', 'category']);

        $output = [];
        $output[] = "=== TYPES DE RELATIONS DISPONIBLES ===";
        $output[] = "";

        $categories = $relations->groupBy('category');

        foreach ($categories as $category => $categoryRelations) {
            $output[] = "📂 " . strtoupper($category);
            foreach ($categoryRelations as $relation) {
                $output[] = "  • {$relation->name} => {$relation->display_name_fr}";
            }
            $output[] = "";
        }

        // Vérifier spécifiquement les relations par alliance
        $output[] = "=== VÉRIFICATION RELATIONS PAR ALLIANCE ===";
        $inLawRelations = $relations->filter(function($r) {
            return str_contains($r->name, '_in_law');
        });

        if ($inLawRelations->count() > 0) {
            $output[] = "✅ Relations par alliance trouvées:";
            foreach ($inLawRelations as $relation) {
                $output[] = "  • {$relation->name} => {$relation->display_name_fr}";
            }
        } else {
            $output[] = "❌ Aucune relation par alliance trouvée";
        }

        return response('<pre>' . implode("\n", $output) . '</pre>');

    } catch (\Exception $e) {
        return response('❌ Erreur: ' . $e->getMessage());
    }
});

// Route pour générer l'URL correcte
Route::get('/debug-suggestion-url/{id}', function ($id) {
    $output = [];
    $output[] = "=== URL DE SUGGESTION ===";
    $output[] = "ID testé: {$id}";

    // Tester différentes façons de générer l'URL
    try {
        $routeUrl = route('suggestions.send-request', ['suggestion' => $id]);
        $output[] = "✅ URL via route(): {$routeUrl}";
    } catch (\Exception $e) {
        $output[] = "❌ Erreur route(): " . $e->getMessage();
    }

    // URL manuelle
    $manualUrl = url("/suggestions/{$id}/send-request");
    $output[] = "URL manuelle: {$manualUrl}";

    // Vérifier si la suggestion existe
    try {
        $suggestion = \App\Models\Suggestion::find($id);
        if ($suggestion) {
            $output[] = "✅ Suggestion {$id} existe";
            $output[] = "  User: {$suggestion->user->name}";
            $output[] = "  Suggested: {$suggestion->suggestedUser->name}";
        } else {
            $output[] = "❌ Suggestion {$id} n'existe pas";
        }
    } catch (\Exception $e) {
        $output[] = "❌ Erreur vérification suggestion: " . $e->getMessage();
    }

    return response('<pre>' . implode("\n", $output) . '</pre>');
});
