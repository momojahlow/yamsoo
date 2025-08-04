<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RelationshipType;

class ListRelationshipTypes extends Command
{
    protected $signature = 'list:relationship-types';
    protected $description = 'List all relationship types in database';

    public function handle()
    {
        $this->info('📋 TYPES DE RELATIONS DISPONIBLES');
        $this->info('==================================');

        $relationshipTypes = RelationshipType::all();

        foreach ($relationshipTypes as $type) {
            $this->info("- {$type->display_name_fr} (code: {$type->name}, catégorie: {$type->category})");
        }

        $this->info("\n🔍 RECHERCHE DE RELATIONS STEP/BELLE-FAMILLE:");
        
        $stepRelations = RelationshipType::where('name', 'like', '%step%')
            ->orWhere('display_name_fr', 'like', '%beau%')
            ->orWhere('display_name_fr', 'like', '%belle%')
            ->get();

        if ($stepRelations->count() > 0) {
            foreach ($stepRelations as $type) {
                $this->info("✅ {$type->display_name_fr} (code: {$type->name})");
            }
        } else {
            $this->error("❌ Aucune relation step/belle-famille trouvée");
        }
    }
}
