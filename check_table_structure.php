<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 VÉRIFICATION DE LA STRUCTURE DE LA TABLE relationship_types\n";
echo "=============================================================\n\n";

// Vérifier si la table existe
if (Schema::hasTable('relationship_types')) {
    echo "✅ La table 'relationship_types' existe\n\n";
    
    // Obtenir les colonnes
    $columns = Schema::getColumnListing('relationship_types');
    echo "📋 Colonnes disponibles:\n";
    foreach ($columns as $column) {
        echo "   - {$column}\n";
    }
    
    echo "\n📊 Contenu actuel de la table:\n";
    $records = DB::table('relationship_types')->get();
    
    if ($records->count() > 0) {
        foreach ($records as $record) {
            echo "   ID: {$record->id}\n";
            foreach ($columns as $column) {
                if ($column !== 'id') {
                    $value = $record->$column ?? 'NULL';
                    echo "     {$column}: '{$value}'\n";
                }
            }
            echo "\n";
        }
    } else {
        echo "   ❌ Aucun enregistrement trouvé\n";
    }
    
} else {
    echo "❌ La table 'relationship_types' n'existe pas\n";
    echo "💡 Exécutez les migrations : php artisan migrate\n";
}

echo "\n✅ Vérification terminée\n";
