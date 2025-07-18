<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FamilyRelationship;

class FixAutomaticFlags extends Command
{
    protected $signature = 'fix:automatic-flags';
    protected $description = 'Corriger les flags automatiques des relations';

    public function handle()
    {
        $this->info('🔧 CORRECTION DES FLAGS AUTOMATIQUES');
        $this->info('═══════════════════════════════════');
        $this->newLine();

        // Marquer les relations frères/sœurs comme automatiques
        $siblingRelations = FamilyRelationship::whereHas('relationshipType', function($query) {
            $query->whereIn('code', ['brother', 'sister']);
        })->get();

        $this->info("👫 Relations frères/sœurs trouvées : {$siblingRelations->count()}");

        $updated = 0;
        foreach ($siblingRelations as $relation) {
            if (!$relation->created_automatically) {
                $relation->update(['created_automatically' => true]);
                $this->line("   ✅ {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr}");
                $updated++;
            } else {
                $this->line("   ⚠️  {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr} (déjà automatique)");
            }
        }

        $this->newLine();
        $this->info("🔄 Relations mises à jour : {$updated}");

        // Vérifier le résultat
        $totalAuto = FamilyRelationship::where('created_automatically', true)->count();
        $totalManual = FamilyRelationship::where('created_automatically', false)->count();

        $this->info("📊 RÉSULTAT FINAL :");
        $this->line("   - Relations automatiques : {$totalAuto}");
        $this->line("   - Relations manuelles : {$totalManual}");

        return 0;
    }
}
