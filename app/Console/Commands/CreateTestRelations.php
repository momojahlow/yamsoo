<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;

class CreateTestRelations extends Command
{
    protected $signature = 'test:create-relations';
    protected $description = 'Créer des relations de test pour l\'utilisateur test';

    public function handle()
    {
        $this->info("🔍 Création de relations de test");
        
        try {
            // Récupérer l'utilisateur de test
            $testUser = User::where('email', 'test@example.com')->first();
            $amina = User::where('email', 'amina.tazi@example.com')->first();
            $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
            
            if (!$testUser || !$amina || !$ahmed) {
                $this->error("❌ Utilisateurs non trouvés");
                return;
            }
            
            // Récupérer les types de relations
            $fatherType = RelationshipType::where('name', 'father')->first();
            $sisterType = RelationshipType::where('name', 'sister')->first();
            
            if (!$fatherType || !$sisterType) {
                $this->error("❌ Types de relations non trouvés");
                return;
            }
            
            // Créer relation Test User → Ahmed (père)
            $relation1 = FamilyRelationship::firstOrCreate([
                'user_id' => $testUser->id,
                'related_user_id' => $ahmed->id,
                'relationship_type_id' => $fatherType->id,
            ], [
                'status' => 'accepted',
                'created_automatically' => false,
            ]);
            
            // Créer relation Test User → Amina (sœur)
            $relation2 = FamilyRelationship::firstOrCreate([
                'user_id' => $testUser->id,
                'related_user_id' => $amina->id,
                'relationship_type_id' => $sisterType->id,
            ], [
                'status' => 'accepted',
                'created_automatically' => false,
            ]);
            
            $this->info("✅ Relations créées:");
            $this->info("- Test User → Ahmed Benali (père)");
            $this->info("- Test User → Amina Tazi (sœur)");
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            $this->error("📍 Fichier: " . $e->getFile() . ":" . $e->getLine());
        }
    }
}
