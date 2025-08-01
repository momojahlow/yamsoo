<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Models\User;

// Bootstrap Laravel
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 VÉRIFICATION DES PROFILS UTILISATEURS\n";
echo "========================================\n\n";

$users = User::with('profile')->take(5)->get();

foreach ($users as $user) {
    echo "👤 {$user->name} (ID: {$user->id})\n";
    if ($user->profile) {
        echo "   - Genre: " . ($user->profile->gender ?? 'non défini') . "\n";
        echo "   - Profil ID: {$user->profile->id}\n";
    } else {
        echo "   - ❌ Aucun profil trouvé\n";
    }
    echo "\n";
}

echo "✅ Vérification terminée\n";
