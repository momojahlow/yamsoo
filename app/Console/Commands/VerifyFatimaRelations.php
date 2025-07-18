<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;

class VerifyFatimaRelations extends Command
{
    protected $signature = 'verify:fatima-relations';
    protected $description = 'Vérifier les vraies relations de Fatima';

    public function handle()
    {
        $this->info('🔍 VÉRIFICATION DES RELATIONS DE FATIMA');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        $amina = User::where('email', 'amina.tazi@example.com')->first();
        $leila = User::where('email', 'leila.mansouri@example.com')->first();

        if (!$fatima) {
            $this->error('❌ Fatima non trouvée');
            return 1;
        }

        $this->info("👩 FATIMA ZAHRA :");
        $this->line("   Email : {$fatima->email}");
        $this->line("   ID : {$fatima->id}");
        $this->newLine();

        // Vérifier TOUTES les relations de Fatima
        $this->info("🔗 TOUTES LES RELATIONS DE FATIMA :");
        $allRelations = FamilyRelationship::where(function($query) use ($fatima) {
            $query->where('user_id', $fatima->id)
                  ->orWhere('related_user_id', $fatima->id);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        foreach ($allRelations as $relation) {
            if ($relation->user_id === $fatima->id) {
                // Fatima → Autre personne
                $this->line("   👩 Fatima → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr} ({$relation->relationshipType->code})");
            } else {
                // Autre personne → Fatima
                $this->line("   👤 {$relation->user->name} → Fatima : {$relation->relationshipType->name_fr} ({$relation->relationshipType->code})");
            }
        }
        $this->newLine();

        // Vérifier spécifiquement les relations avec Mohammed, Ahmed, Amina, Leila
        $this->info("🎯 RELATIONS SPÉCIFIQUES :");
        
        $people = [
            'Mohammed' => $mohammed,
            'Ahmed' => $ahmed,
            'Amina' => $amina,
            'Leila' => $leila
        ];

        foreach ($people as $name => $person) {
            if (!$person) {
                $this->line("   ❌ {$name} : Non trouvé");
                continue;
            }

            // Chercher relation Fatima → Personne
            $relationFromFatima = FamilyRelationship::where('user_id', $fatima->id)
                ->where('related_user_id', $person->id)
                ->with('relationshipType')
                ->first();

            // Chercher relation Personne → Fatima
            $relationToFatima = FamilyRelationship::where('user_id', $person->id)
                ->where('related_user_id', $fatima->id)
                ->with('relationshipType')
                ->first();

            $this->line("   👤 {$name} ({$person->email}) :");
            
            if ($relationFromFatima) {
                $this->line("      👩 Fatima → {$name} : {$relationFromFatima->relationshipType->name_fr}");
            } else {
                $this->line("      ❌ Aucune relation Fatima → {$name}");
            }

            if ($relationToFatima) {
                $this->line("      👤 {$name} → Fatima : {$relationToFatima->relationshipType->name_fr}");
            } else {
                $this->line("      ❌ Aucune relation {$name} → Fatima");
            }
        }
        $this->newLine();

        // Vérifier pourquoi le système suggère ces personnes
        $this->info("💡 SUGGESTIONS ACTUELLES POUR FATIMA :");
        $suggestions = Suggestion::where('user_id', $fatima->id)
            ->with('suggestedUser')
            ->get();

        foreach ($suggestions as $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $this->line("   💡 {$suggestedUser->name} : {$suggestion->suggested_relation_code} ({$suggestion->type})");
            
            // Vérifier si cette personne a déjà une relation avec Fatima
            $existingRelation = FamilyRelationship::where(function($query) use ($fatima, $suggestedUser) {
                $query->where('user_id', $fatima->id)->where('related_user_id', $suggestedUser->id)
                      ->orWhere('user_id', $suggestedUser->id)->where('related_user_id', $fatima->id);
            })->with('relationshipType')->first();

            if ($existingRelation) {
                $this->line("      ❌ ERREUR : Relation existante ! {$existingRelation->relationshipType->name_fr}");
            } else {
                $this->line("      ✅ Pas de relation existante");
            }
        }

        $this->newLine();
        $this->info("🔧 CONCLUSION :");
        $this->line("   Si Fatima est déjà la mère de Mohammed/Ahmed/Amina/Leila,");
        $this->line("   alors le système NE DEVRAIT PAS les suggérer !");
        $this->line("   Il faut corriger le service de suggestions pour exclure");
        $this->line("   les personnes qui ont déjà une relation avec l'utilisateur.");

        return 0;
    }
}
