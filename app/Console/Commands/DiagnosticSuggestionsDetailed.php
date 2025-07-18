<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;

class DiagnosticSuggestionsDetailed extends Command
{
    protected $signature = 'diagnostic:suggestions-detailed';
    protected $description = 'Diagnostic détaillé des suggestions pour Fatima';

    public function handle()
    {
        $this->info('🔍 DIAGNOSTIC DÉTAILLÉ DES SUGGESTIONS');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();

        if (!$fatima || !$youssef || !$mohammed || !$ahmed) {
            $this->error('❌ Utilisateurs manquants');
            return 1;
        }

        $this->info("👩 FATIMA ZAHRA :");
        $this->line("   Email : {$fatima->email}");
        $this->line("   Genre : {$fatima->profile?->gender}");
        $this->newLine();

        // Analyser les relations de Fatima
        $this->info("🔗 RELATIONS DE FATIMA :");
        $fatimaRelations = FamilyRelationship::where(function($query) use ($fatima) {
            $query->where('user_id', $fatima->id)
                  ->orWhere('related_user_id', $fatima->id);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        foreach ($fatimaRelations as $relation) {
            if ($relation->user_id === $fatima->id) {
                $this->line("   - Fatima → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr}");
            } else {
                $this->line("   - {$relation->user->name} → Fatima : {$relation->relationshipType->name_fr}");
            }
        }
        $this->newLine();

        // Analyser les relations de Youssef (mari de Fatima)
        $this->info("👨 RELATIONS DE YOUSSEF (MARI DE FATIMA) :");
        $youssefRelations = FamilyRelationship::where(function($query) use ($youssef) {
            $query->where('user_id', $youssef->id)
                  ->orWhere('related_user_id', $youssef->id);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        foreach ($youssefRelations as $relation) {
            if ($relation->user_id === $youssef->id) {
                $this->line("   - Youssef → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr}");
            } else {
                $this->line("   - {$relation->user->name} → Youssef : {$relation->relationshipType->name_fr}");
            }
        }
        $this->newLine();

        // Analyser la logique des suggestions
        $this->info("🧠 LOGIQUE DES SUGGESTIONS POUR FATIMA :");
        $this->line("   Fatima est mariée à Youssef (relation : Mari)");
        $this->line("   Youssef a des enfants : Mohammed (Fils), Ahmed (Fils), Amina (Fille)");
        $this->line("   DONC pour Fatima :");
        $this->line("      - Mohammed devrait être : Beau-fils (stepson)");
        $this->line("      - Ahmed devrait être : Beau-fils (stepson)");
        $this->line("      - Amina devrait être : Belle-fille (stepdaughter)");
        $this->newLine();

        // Vérifier les suggestions actuelles
        $this->info("💡 SUGGESTIONS ACTUELLES POUR FATIMA :");
        $suggestions = Suggestion::where('user_id', $fatima->id)
            ->with('suggestedUser')
            ->get();

        foreach ($suggestions as $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $gender = $suggestedUser->profile?->gender === 'female' ? '👩' : '👨';
            $type = $suggestion->type === 'intelligent' ? '🧠' : '📋';
            
            $this->line("   {$type} {$gender} {$suggestedUser->name} : {$suggestion->suggested_relation_code}");
            
            // Analyser si c'est correct
            if ($suggestedUser->id === $mohammed->id || $suggestedUser->id === $ahmed->id) {
                if ($suggestion->suggested_relation_code === 'father_in_law') {
                    $this->line("      ❌ ERREUR : Suggéré comme beau-père mais c'est un beau-fils !");
                } elseif ($suggestion->suggested_relation_code === 'stepson') {
                    $this->line("      ✅ CORRECT : Beau-fils");
                }
            }
        }
        $this->newLine();

        $this->info("🔧 PROBLÈME IDENTIFIÉ :");
        $this->line("   Le service de suggestions confond les générations !");
        $this->line("   Il suggère les ENFANTS du mari comme PARENTS (father_in_law)");
        $this->line("   au lieu de les suggérer comme ENFANTS (stepson/stepdaughter)");

        return 0;
    }
}
