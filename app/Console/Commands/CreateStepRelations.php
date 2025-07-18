<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Models\Suggestion;

class CreateStepRelations extends Command
{
    protected $signature = 'create:step-relations';
    protected $description = 'Créer les relations belle-mère/beau-fils manquantes';

    public function handle()
    {
        $this->info('🔧 CRÉATION DES RELATIONS BELLE-MÈRE/BEAU-FILS');
        $this->info('═══════════════════════════════════════════════');
        $this->newLine();

        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();

        if (!$fatima || !$youssef) {
            $this->error('❌ Utilisateurs non trouvés');
            return 1;
        }

        // Récupérer les types de relations
        $stepmotherType = RelationshipType::where('code', 'stepmother')->first();
        $stepsonType = RelationshipType::where('code', 'stepson')->first();
        $stepdaughterType = RelationshipType::where('code', 'stepdaughter')->first();

        if (!$stepmotherType || !$stepsonType || !$stepdaughterType) {
            $this->error('❌ Types de relations step* non trouvés');
            $this->line('Types disponibles :');
            RelationshipType::whereIn('code', ['stepmother', 'stepson', 'stepdaughter'])
                ->get(['code', 'name_fr'])
                ->each(function($type) {
                    $this->line("   - {$type->code} : {$type->name_fr}");
                });
            return 1;
        }

        // Récupérer les enfants de Youssef
        $youssefChildren = FamilyRelationship::where('user_id', $youssef->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('code', ['son', 'daughter']);
            })
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        $this->info("👨 ENFANTS DE YOUSSEF :");
        foreach ($youssefChildren as $relation) {
            $child = $relation->relatedUser;
            $gender = $child->profile?->gender;
            $genderIcon = $gender === 'female' ? '👩' : '👨';
            $this->line("   {$genderIcon} {$child->name} ({$relation->relationshipType->name_fr})");
        }
        $this->newLine();

        // Créer les relations entre Fatima et les enfants de Youssef
        $this->info("🔗 CRÉATION DES RELATIONS BELLE-MÈRE/BEAU-FILS :");
        $createdRelations = 0;

        foreach ($youssefChildren as $relation) {
            $child = $relation->relatedUser;
            $childGender = $child->profile?->gender;

            // Vérifier si la relation existe déjà
            $existingRelation = FamilyRelationship::where(function($query) use ($fatima, $child) {
                $query->where('user_id', $fatima->id)->where('related_user_id', $child->id)
                      ->orWhere('user_id', $child->id)->where('related_user_id', $fatima->id);
            })->first();

            if ($existingRelation) {
                $this->line("   ⚠️  Relation déjà existante : Fatima ↔ {$child->name}");
                continue;
            }

            // Déterminer le type de relation selon le genre de l'enfant
            if ($childGender === 'male') {
                $childToFatimaType = $stepmotherType;
                $fatimaToChildType = $stepsonType;
                $relationName = 'beau-fils';
            } else {
                $childToFatimaType = $stepmotherType;
                $fatimaToChildType = $stepdaughterType;
                $relationName = 'belle-fille';
            }

            // Créer la relation Enfant → Fatima (belle-mère)
            FamilyRelationship::create([
                'user_id' => $child->id,
                'related_user_id' => $fatima->id,
                'relationship_type_id' => $childToFatimaType->id,
                'created_automatically' => true,
            ]);

            // Créer la relation Fatima → Enfant (beau-fils/belle-fille)
            FamilyRelationship::create([
                'user_id' => $fatima->id,
                'related_user_id' => $child->id,
                'relationship_type_id' => $fatimaToChildType->id,
                'created_automatically' => true,
            ]);

            $genderIcon = $childGender === 'female' ? '👩' : '👨';
            $this->line("   ✅ {$genderIcon} {$child->name} ↔ Fatima : {$relationName}/belle-mère");
            $createdRelations += 2;
        }

        $this->newLine();
        $this->info("✅ {$createdRelations} relations créées");
        $this->newLine();

        // Supprimer les suggestions obsolètes
        $this->info("🗑️  SUPPRESSION DES SUGGESTIONS OBSOLÈTES :");
        $childrenIds = $youssefChildren->pluck('relatedUser.id')->toArray();
        
        $obsoleteSuggestions = Suggestion::where('user_id', $fatima->id)
            ->whereIn('suggested_user_id', $childrenIds)
            ->get();

        foreach ($obsoleteSuggestions as $suggestion) {
            $this->line("   ❌ Suppression : {$suggestion->suggestedUser->name} ({$suggestion->suggested_relation_code})");
            $suggestion->delete();
        }

        $this->newLine();
        $this->info("🎉 CORRECTION TERMINÉE !");
        $this->line("   - Relations belle-mère/beau-fils créées");
        $this->line("   - Suggestions obsolètes supprimées");
        $this->line("   - Fatima ne devrait plus avoir de suggestions erronées");

        return 0;
    }
}
