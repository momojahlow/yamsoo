<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

echo "🔍 VÉRIFICATION DE LA STRUCTURE DE LA BASE DE DONNÉES\n";
echo "====================================================\n\n";

// Vérifier la table relationship_types
echo "📋 Table relationship_types:\n";
if (Schema::hasTable('relationship_types')) {
    $columns = Schema::getColumnListing('relationship_types');
    echo "   Colonnes: " . implode(', ', $columns) . "\n";
    
    $count = DB::table('relationship_types')->count();
    echo "   Nombre d'enregistrements: {$count}\n";
    
    if ($count > 0) {
        $sample = DB::table('relationship_types')->first();
        echo "   Premier enregistrement:\n";
        foreach ($sample as $key => $value) {
            echo "     - {$key}: " . ($value ?? 'NULL') . "\n";
        }
    }
} else {
    echo "   ❌ Table non trouvée\n";
}
echo "\n";

// Vérifier la table users
echo "👥 Table users:\n";
if (Schema::hasTable('users')) {
    $count = DB::table('users')->count();
    echo "   Nombre d'utilisateurs: {$count}\n";
    
    if ($count > 0) {
        $users = DB::table('users')->select('id', 'name', 'email')->take(3)->get();
        foreach ($users as $user) {
            echo "   - {$user->name} (ID: {$user->id})\n";
        }
    }
} else {
    echo "   ❌ Table non trouvée\n";
}
echo "\n";

// Vérifier la table family_relationships
echo "🔗 Table family_relationships:\n";
if (Schema::hasTable('family_relationships')) {
    $columns = Schema::getColumnListing('family_relationships');
    echo "   Colonnes: " . implode(', ', $columns) . "\n";
    
    $count = DB::table('family_relationships')->count();
    echo "   Nombre d'enregistrements: {$count}\n";
} else {
    echo "   ❌ Table non trouvée\n";
}
echo "\n";

// Vérifier la table relationship_requests
echo "📝 Table relationship_requests:\n";
if (Schema::hasTable('relationship_requests')) {
    $columns = Schema::getColumnListing('relationship_requests');
    echo "   Colonnes: " . implode(', ', $columns) . "\n";
    
    $count = DB::table('relationship_requests')->count();
    echo "   Nombre d'enregistrements: {$count}\n";
} else {
    echo "   ❌ Table non trouvée\n";
}
echo "\n";

echo "✅ Vérification terminée\n";
