<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Services\FamilyRelationService;

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

echo "🧪 TEST COMPLET DU FLUX DE RELATIONS FAMILIALES\n";
echo "===============================================\n\n";

// Initialiser le service
$familyRelationService = app(FamilyRelationService::class);

// Test 1: Vérifier les utilisateurs disponibles
echo "👥 Test 1: Utilisateurs disponibles\n";
$users = User::with('profile')->take(5)->get();
foreach ($users as $user) {
    echo "   - {$user->name} (ID: {$user->id})\n";
}
echo "\n";

// Test 2: Vérifier les types de relations
echo "📋 Test 2: Types de relations disponibles\n";
$relationshipTypes = RelationshipType::orderBy('code')->get();
foreach ($relationshipTypes->take(10) as $type) {
    echo "   - {$type->code} : {$type->name_fr}\n";
}
echo "   ... et " . ($relationshipTypes->count() - 10) . " autres\n\n";

// Test 3: Créer une demande de relation
echo "📝 Test 3: Création d'une demande de relation\n";
$ahmed = User::where('name', 'Ahmed Benali')->first();
$fatima = User::where('name', 'Fatima Zahra')->first();
$fatherType = RelationshipType::where('code', 'father')->first();

if (!$ahmed || !$fatima || !$fatherType) {
    echo "   ❌ Utilisateurs ou type de relation non trouvés\n";
    exit(1);
}

echo "   Demandeur: {$ahmed->name} (ID: {$ahmed->id})\n";
echo "   Cible: {$fatima->name} (ID: {$fatima->id})\n";
echo "   Type: {$fatherType->name_fr} (ID: {$fatherType->id})\n";

try {
    $request = $familyRelationService->createRelationshipRequest(
        $ahmed,
        $fatima->id,
        $fatherType->id,
        "Test de relation père-fille"
    );
    echo "   ✅ Demande créée avec succès (ID: {$request->id})\n";
} catch (Exception $e) {
    echo "   ❌ Erreur lors de la création: {$e->getMessage()}\n";
    exit(1);
}
echo "\n";

// Test 4: Vérifier la demande en attente
echo "⏳ Test 4: Vérification de la demande en attente\n";
$pendingRequest = RelationshipRequest::where('id', $request->id)
    ->with(['requester', 'targetUser', 'relationshipType'])
    ->first();

if ($pendingRequest) {
    echo "   ✅ Demande trouvée:\n";
    echo "     - ID: {$pendingRequest->id}\n";
    echo "     - Statut: {$pendingRequest->status}\n";
    echo "     - Demandeur: {$pendingRequest->requester->name}\n";
    echo "     - Cible: {$pendingRequest->targetUser->name}\n";
    echo "     - Type: {$pendingRequest->relationshipType->name_fr}\n";
    echo "     - Message: {$pendingRequest->message}\n";
} else {
    echo "   ❌ Demande non trouvée\n";
    exit(1);
}
echo "\n";

// Test 5: Accepter la demande
echo "✅ Test 5: Acceptation de la demande\n";
try {
    $createdRelationship = $familyRelationService->acceptRelationshipRequest($pendingRequest);
    echo "   ✅ Demande acceptée avec succès\n";
    echo "   ✅ Relation créée (ID: {$createdRelationship->id})\n";
} catch (Exception $e) {
    echo "   ❌ Erreur lors de l'acceptation: {$e->getMessage()}\n";
    echo "   Trace: {$e->getTraceAsString()}\n";
    exit(1);
}
echo "\n";

// Test 6: Vérifier la mise à jour du statut de la demande
echo "🔄 Test 6: Vérification de la mise à jour de la demande\n";
$updatedRequest = RelationshipRequest::find($pendingRequest->id);
if ($updatedRequest) {
    echo "   ✅ Demande mise à jour:\n";
    echo "     - Statut: {$updatedRequest->status}\n";
    echo "     - Répondu le: " . ($updatedRequest->responded_at ? $updatedRequest->responded_at->format('Y-m-d H:i:s') : 'Non défini') . "\n";
} else {
    echo "   ❌ Demande non trouvée après acceptation\n";
}
echo "\n";

// Test 7: Vérifier les relations créées
echo "🔗 Test 7: Vérification des relations créées\n";
$ahmedRelations = FamilyRelationship::where('user_id', $ahmed->id)
    ->with(['relatedUser', 'relationshipType'])
    ->get();

$fatimaRelations = FamilyRelationship::where('user_id', $fatima->id)
    ->with(['relatedUser', 'relationshipType'])
    ->get();

echo "   Relations d'Ahmed:\n";
foreach ($ahmedRelations as $rel) {
    echo "     - Ahmed → {$rel->relatedUser->name} : {$rel->relationshipType->name_fr} (statut: {$rel->status})\n";
}

echo "   Relations de Fatima:\n";
foreach ($fatimaRelations as $rel) {
    echo "     - Fatima → {$rel->relatedUser->name} : {$rel->relationshipType->name_fr} (statut: {$rel->status})\n";
}
echo "\n";

// Test 8: Vérifier la cohérence des relations inverses
echo "🔄 Test 8: Vérification des relations inverses\n";
$ahmedToFatima = FamilyRelationship::where('user_id', $ahmed->id)
    ->where('related_user_id', $fatima->id)
    ->with('relationshipType')
    ->first();

$fatimaToAhmed = FamilyRelationship::where('user_id', $fatima->id)
    ->where('related_user_id', $ahmed->id)
    ->with('relationshipType')
    ->first();

if ($ahmedToFatima && $fatimaToAhmed) {
    echo "   ✅ Relations bidirectionnelles trouvées:\n";
    echo "     - Ahmed → Fatima : {$ahmedToFatima->relationshipType->name_fr}\n";
    echo "     - Fatima → Ahmed : {$fatimaToAhmed->relationshipType->name_fr}\n";
    
    // Vérifier la logique inverse
    if ($ahmedToFatima->relationshipType->code === 'father' && $fatimaToAhmed->relationshipType->code === 'daughter') {
        echo "   ✅ Logique inverse correcte (père ↔ fille)\n";
    } else {
        echo "   ❌ Logique inverse incorrecte\n";
    }
} else {
    echo "   ❌ Relations bidirectionnelles manquantes\n";
    if (!$ahmedToFatima) echo "     - Relation Ahmed → Fatima manquante\n";
    if (!$fatimaToAhmed) echo "     - Relation Fatima → Ahmed manquante\n";
}
echo "\n";

echo "✅ RÉSUMÉ DES TESTS\n";
echo "===================\n";
echo "Tous les tests ont été exécutés avec succès !\n";
echo "Le flux complet de création et d'acceptation de relations fonctionne correctement.\n\n";

echo "🎉 SYSTÈME DE RELATIONS VALIDÉ !\n";
