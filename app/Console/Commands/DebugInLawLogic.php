<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Services\IntelligentSuggestionService;

class DebugInLawLogic extends Command
{
    protected $signature = 'debug:inlaw-logic';
    protected $description = 'Debug la logique de détermination des relations par alliance';

    public function handle()
    {
        $this->info('🔍 DEBUG LOGIQUE RELATIONS PAR ALLIANCE');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Trouver les utilisateurs
        $nadia = User::where('name', 'like', '%Nadia%')->where('name', 'like', '%Berrada%')->first();
        $mohammed = User::where('name', 'like', '%Mohammed%')->where('name', 'like', '%Alami%')->first();
        $youssef = User::where('name', 'like', '%Youssef%')->where('name', 'like', '%Bennani%')->first();
        
        if (!$nadia || !$mohammed || !$youssef) {
            $this->error('❌ Utilisateurs non trouvés');
            return 1;
        }

        $this->info("👤 NADIA : {$nadia->name} (ID: {$nadia->id})");
        $this->info("👤 MOHAMMED (mari) : {$mohammed->name} (ID: {$mohammed->id})");
        $this->info("👤 YOUSSEF (père du mari) : {$youssef->name} (ID: {$youssef->id})");
        $this->newLine();

        // Analyser les relations entre Mohammed et Youssef
        $this->info('1️⃣ RELATIONS MOHAMMED ↔ YOUSSEF :');
        $relations = FamilyRelationship::where(function($query) use ($mohammed, $youssef) {
            $query->where(function($subQuery) use ($mohammed, $youssef) {
                $subQuery->where('user_id', $mohammed->id)
                         ->where('related_user_id', $youssef->id);
            })->orWhere(function($subQuery) use ($mohammed, $youssef) {
                $subQuery->where('user_id', $youssef->id)
                         ->where('related_user_id', $mohammed->id);
            });
        })
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

        foreach ($relations as $relation) {
            $from = $relation->user;
            $to = $relation->relatedUser;
            $relationType = $relation->relationshipType;
            
            $this->line("   📋 {$from->name} → {$to->name} : {$relationType->name_fr} ({$relationType->code})");
        }
        $this->newLine();

        // Simuler la logique du service
        $this->info('2️⃣ SIMULATION DE LA LOGIQUE DU SERVICE :');
        
        $suggestionService = new IntelligentSuggestionService();
        
        foreach ($relations as $familyRelation) {
            $familyMember = $familyRelation->user_id === $mohammed->id 
                ? $familyRelation->relatedUser 
                : $familyRelation->user;

            if ($familyMember->id === $youssef->id) {
                $this->line("   🎯 TRAITEMENT DE YOUSSEF :");
                $this->line("      Relation trouvée : {$familyRelation->user->name} → {$familyRelation->relatedUser->name} : {$familyRelation->relationshipType->code}");
                
                // Déterminer la relation du conjoint avec ce membre de famille
                $spouseRelationCode = null;
                
                if ($familyRelation->user_id === $mohammed->id) {
                    // Mohammed est l'utilisateur de la relation: Mohammed -> Youssef
                    $spouseRelationCode = $familyRelation->relationshipType->code;
                    $this->line("      Mohammed → Youssef : {$spouseRelationCode}");
                } else {
                    // Youssef est l'utilisateur de la relation: Youssef -> Mohammed
                    // Nous devons inverser pour avoir Mohammed -> Youssef
                    $originalCode = $familyRelation->relationshipType->code;
                    $spouseRelationCode = $this->getInverseRelationCode($originalCode, $youssef, $mohammed);
                    $this->line("      Youssef → Mohammed : {$originalCode}");
                    $this->line("      Inverse (Mohammed → Youssef) : {$spouseRelationCode}");
                }

                // Déterminer la relation par alliance
                $inLawRelation = $this->determineInLawRelation($spouseRelationCode, $youssef);
                
                if ($inLawRelation) {
                    $this->line("      ✅ Relation par alliance : {$inLawRelation['relation_name']} ({$inLawRelation['relation_code']})");
                } else {
                    $this->line("      ❌ Aucune relation par alliance déterminée");
                }
                $this->newLine();
            }
        }

        return 0;
    }

    /**
     * Copie de la méthode du service pour debug
     */
    private function getInverseRelationCode(string $relationCode, User $fromUser, User $toUser): string
    {
        $fromGender = $fromUser->profile?->gender;
        $toGender = $toUser->profile?->gender;

        $inverseMappings = [
            'father' => $fromGender === 'female' ? 'daughter' : 'son',
            'mother' => $fromGender === 'female' ? 'daughter' : 'son',
            'son' => $toGender === 'female' ? 'mother' : 'father',
            'daughter' => $toGender === 'female' ? 'mother' : 'father',
            'brother' => $fromGender === 'female' ? 'sister' : 'brother',
            'sister' => $fromGender === 'female' ? 'sister' : 'brother',
            'husband' => 'wife',
            'wife' => 'husband',
        ];

        return $inverseMappings[$relationCode] ?? $relationCode;
    }

    /**
     * Copie de la méthode du service pour debug
     */
    private function determineInLawRelation(string $spouseRelationCode, User $familyMember): ?array
    {
        $memberGender = $familyMember->profile?->gender;
        
        $inLawMappings = [
            'father' => ['relation_code' => 'father_in_law', 'relation_name' => 'Beau-père'],
            'mother' => ['relation_code' => 'mother_in_law', 'relation_name' => 'Belle-mère'],
            'brother' => ['relation_code' => 'brother_in_law', 'relation_name' => 'Beau-frère'],
            'sister' => ['relation_code' => 'sister_in_law', 'relation_name' => 'Belle-sœur'],
            'son' => [
                'relation_code' => $memberGender === 'female' ? 'stepdaughter' : 'stepson',
                'relation_name' => $memberGender === 'female' ? 'Belle-fille' : 'Beau-fils'
            ],
            'daughter' => [
                'relation_code' => $memberGender === 'female' ? 'stepdaughter' : 'stepson',
                'relation_name' => $memberGender === 'female' ? 'Belle-fille' : 'Beau-fils'
            ]
        ];

        return $inLawMappings[$spouseRelationCode] ?? null;
    }
}
