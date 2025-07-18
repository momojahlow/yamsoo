<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;

class FixSiblingRelations extends Command
{
    protected $signature = 'fix:sibling-relations';
    protected $description = 'Corriger les relations frère/sœur basées sur le genre';

    public function handle()
    {
        $this->info('🔧 CORRECTION DES RELATIONS FRÈRE/SŒUR');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        // Récupérer les types de relations
        $brotherType = RelationshipType::where('code', 'brother')->first();
        $sisterType = RelationshipType::where('code', 'sister')->first();

        if (!$brotherType || !$sisterType) {
            $this->error('❌ Types de relations brother/sister non trouvés');
            return 1;
        }

        // Trouver toutes les relations frère/sœur incorrectes
        $incorrectRelations = FamilyRelationship::whereIn('relationship_type_id', [$brotherType->id, $sisterType->id])
            ->with(['user', 'relatedUser', 'relationshipType'])
            ->get();

        $this->info("🔍 RELATIONS FRÈRE/SŒUR À VÉRIFIER : {$incorrectRelations->count()}");
        $this->newLine();

        $correctedCount = 0;

        foreach ($incorrectRelations as $relation) {
            $user = $relation->user;
            $relatedUser = $relation->relatedUser;
            $currentType = $relation->relationshipType;

            // Déterminer le type correct basé sur le genre de la personne CIBLÉE
            $targetGender = $relatedUser->profile?->gender;
            $correctType = $targetGender === 'female' ? $sisterType : $brotherType;
            $correctName = $targetGender === 'female' ? 'Sœur' : 'Frère';

            $this->line("👤 {$user->name} → {$relatedUser->name} :");
            $this->line("   Genre cible : {$targetGender}");
            $this->line("   Actuel : {$currentType->name_fr} ({$currentType->code})");
            $this->line("   Correct : {$correctName} ({$correctType->code})");

            // Corriger si nécessaire
            if ($relation->relationship_type_id !== $correctType->id) {
                $relation->update(['relationship_type_id' => $correctType->id]);
                $this->line("   ✅ CORRIGÉ !");
                $correctedCount++;
            } else {
                $this->line("   ✅ Déjà correct");
            }

            $this->newLine();
        }

        $this->info("🎉 CORRECTION TERMINÉE !");
        $this->line("   Relations corrigées : {$correctedCount}");
        $this->newLine();

        // Vérifier le résultat pour Amina
        $this->info("🔍 VÉRIFICATION POUR AMINA :");
        $amina = User::where('email', 'amina.tazi@example.com')->first();
        
        if ($amina) {
            $aminaRelations = FamilyRelationship::where('user_id', $amina->id)
                ->whereIn('relationship_type_id', [$brotherType->id, $sisterType->id])
                ->with(['relatedUser', 'relationshipType'])
                ->get();

            foreach ($aminaRelations as $relation) {
                $relatedUser = $relation->relatedUser;
                $gender = $relatedUser->profile?->gender;
                $genderIcon = $gender === 'female' ? '👩' : '👨';
                $relationType = $relation->relationshipType;

                $this->line("   {$genderIcon} Amina → {$relatedUser->name} : {$relationType->name_fr}");

                // Vérifier la cohérence
                $isCorrect = ($gender === 'female' && $relationType->code === 'sister') ||
                           ($gender === 'male' && $relationType->code === 'brother');

                if ($isCorrect) {
                    $this->line("      ✅ CORRECT");
                } else {
                    $this->line("      ❌ ENCORE INCORRECT");
                }
            }
        }

        $this->newLine();
        $this->info("🌐 Maintenant, l'interface web devrait afficher :");
        $this->line("   👩 Amina → Ahmed : Frère (au lieu de Sœur)");
        $this->line("   👩 Amina → Mohammed : Frère (au lieu de Sœur)");

        return 0;
    }
}
