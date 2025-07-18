<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;

class CreateMissingRelations extends Command
{
    protected $signature = 'create:missing-relations';
    protected $description = 'Créer les relations familiales manquantes';

    public function handle()
    {
        $this->info('🔗 CRÉATION DES RELATIONS MANQUANTES');
        $this->info('═══════════════════════════════════');
        $this->newLine();

        // Récupérer les utilisateurs principaux
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
        $amina = User::where('email', 'amina.tazi@example.com')->first();

        if (!$youssef || !$ahmed || !$mohammed || !$amina) {
            $this->error('❌ Utilisateurs manquants');
            return 1;
        }

        $this->info('👥 Utilisateurs trouvés :');
        $this->line("   - Youssef (père)");
        $this->line("   - Ahmed (fils)");
        $this->line("   - Mohammed (fils)");
        $this->line("   - Amina (fille)");
        $this->newLine();

        // Créer les relations frères/sœurs manquantes
        $this->createSiblingRelations($ahmed, $mohammed, $amina);

        $this->newLine();
        $this->info('✅ Relations manquantes créées !');

        return 0;
    }

    private function createSiblingRelations($ahmed, $mohammed, $amina): void
    {
        $this->info('👫 CRÉATION DES RELATIONS FRÈRES/SŒURS :');

        $relations = [
            // Ahmed et Mohammed sont frères
            [$ahmed, $mohammed, 'brother', 'Frère'],
            [$mohammed, $ahmed, 'brother', 'Frère'],
            
            // Ahmed et Amina sont frère/sœur
            [$ahmed, $amina, 'brother', 'Frère'],
            [$amina, $ahmed, 'sister', 'Sœur'],
            
            // Mohammed et Amina sont frère/sœur
            [$mohammed, $amina, 'brother', 'Frère'],
            [$amina, $mohammed, 'sister', 'Sœur'],
        ];

        foreach ($relations as [$user, $relatedUser, $relationCode, $relationName]) {
            // Vérifier si la relation existe déjà
            $exists = FamilyRelationship::where('user_id', $user->id)
                ->where('related_user_id', $relatedUser->id)
                ->exists();

            if (!$exists) {
                $relationType = RelationshipType::where('code', $relationCode)->first();
                
                if ($relationType) {
                    FamilyRelationship::create([
                        'user_id' => $user->id,
                        'related_user_id' => $relatedUser->id,
                        'relationship_type_id' => $relationType->id,
                        'status' => 'accepted',
                        'created_automatically' => true,
                        'accepted_at' => now(),
                    ]);
                    
                    $this->line("   ✅ {$user->name} → {$relatedUser->name} : {$relationName}");
                } else {
                    $this->line("   ❌ Type de relation '{$relationCode}' non trouvé");
                }
            } else {
                $this->line("   ⚠️  {$user->name} → {$relatedUser->name} : {$relationName} (existe déjà)");
            }
        }
    }
}
