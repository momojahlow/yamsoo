<?php

// Test simple avec artisan tinker
$commands = [
    'echo "🧪 TEST DU SYSTÈME DE RELATIONS"',
    'echo "=============================="',
    '',
    '// Vérifier les utilisateurs',
    '$users = App\Models\User::take(3)->get()',
    'foreach($users as $u) { echo "- " . $u->name . " (ID: " . $u->id . ")"; }',
    '',
    '// Vérifier les types de relations',
    '$types = App\Models\RelationshipType::take(5)->get()',
    'foreach($types as $t) { echo "- " . $t->code . " : " . $t->name_fr; }',
    '',
    '// Créer une demande de relation',
    '$ahmed = App\Models\User::where("name", "Ahmed Benali")->first()',
    '$fatima = App\Models\User::where("name", "Fatima Zahra")->first()',
    '$fatherType = App\Models\RelationshipType::where("code", "father")->first()',
    '',
    'if($ahmed && $fatima && $fatherType) {',
    '    echo "✅ Utilisateurs et type trouvés"',
    '    $service = app(App\Services\FamilyRelationService::class)',
    '    $request = $service->createRelationshipRequest($ahmed, $fatima->id, $fatherType->id, "Test")',
    '    echo "✅ Demande créée (ID: " . $request->id . ")"',
    '    ',
    '    // Accepter la demande',
    '    $relation = $service->acceptRelationshipRequest($request)',
    '    echo "✅ Demande acceptée (Relation ID: " . $relation->id . ")"',
    '    ',
    '    // Vérifier le statut',
    '    $updatedRequest = App\Models\RelationshipRequest::find($request->id)',
    '    echo "📊 Statut de la demande: " . $updatedRequest->status',
    '    ',
    '    // Vérifier les relations créées',
    '    $ahmedRels = App\Models\FamilyRelationship::where("user_id", $ahmed->id)->with("relationshipType", "relatedUser")->get()',
    '    echo "🔗 Relations d\'Ahmed:"',
    '    foreach($ahmedRels as $rel) { echo "  - " . $rel->relatedUser->name . " : " . $rel->relationshipType->name_fr . " (" . $rel->status . ")"; }',
    '    ',
    '    $fatimaRels = App\Models\FamilyRelationship::where("user_id", $fatima->id)->with("relationshipType", "relatedUser")->get()',
    '    echo "🔗 Relations de Fatima:"',
    '    foreach($fatimaRels as $rel) { echo "  - " . $rel->relatedUser->name . " : " . $rel->relationshipType->name_fr . " (" . $rel->status . ")"; }',
    '} else {',
    '    echo "❌ Utilisateurs ou type non trouvés"',
    '}',
    '',
    'echo "✅ Test terminé"',
    'exit()'
];

// Écrire le script pour tinker
file_put_contents('tinker_test.txt', implode("\n", $commands));

echo "Script de test créé dans tinker_test.txt\n";
echo "Exécutez: php artisan tinker < tinker_test.txt\n";
