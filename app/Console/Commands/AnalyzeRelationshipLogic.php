<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;

class AnalyzeRelationshipLogic extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'analyze:relationship-logic';

    /**
     * The description of the console command.
     */
    protected $description = 'Analyser la logique des relations et corriger l\'affichage';

    protected FamilyRelationService $familyRelationService;

    public function __construct(FamilyRelationService $familyRelationService)
    {
        parent::__construct();
        $this->familyRelationService = $familyRelationService;
    }

    public function handle()
    {
        $this->info('🔍 ANALYSE DE LA LOGIQUE DES RELATIONS');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        // Analyser chaque utilisateur et ses relations
        $users = User::whereIn('email', [
            'fatima.zahra@example.com',
            'ahmed.benali@example.com', 
            'youssef.bennani@example.com',
            'amina.tazi@example.com'
        ])->get();

        foreach ($users as $user) {
            $this->analyzeUserRelationships($user);
            $this->newLine();
        }

        return 0;
    }

    private function analyzeUserRelationships(User $user): void
    {
        $gender = $user->profile?->gender === 'female' ? '👩' : '👨';
        $this->info("{$gender} {$user->name} (ID: {$user->id}) :");

        // Obtenir toutes les relations de cet utilisateur
        $relationships = $this->familyRelationService->getUserRelationships($user);
        
        if ($relationships->count() === 0) {
            $this->line("   (Aucune relation)");
            return;
        }

        $this->line("   📋 Relations selon le service :");
        foreach ($relationships as $relation) {
            $relatedUser = $relation->relatedUser;
            $type = $relation->relationshipType;
            $auto = $relation->created_automatically ? ' 🤖' : ' 👤';
            $relatedGender = $relatedUser->profile?->gender === 'female' ? '👩' : '👨';
            
            $this->line("      - {$type->name_fr} : {$relatedGender} {$relatedUser->name}{$auto}");
        }

        // Vérifier aussi les relations inverses dans la base
        $this->line("   📋 Relations brutes en base :");
        $rawRelations = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })->with(['relationshipType', 'user', 'relatedUser'])->get();

        foreach ($rawRelations as $relation) {
            if ($relation->user_id === $user->id) {
                // L'utilisateur est le sujet de la relation
                $this->line("      - {$user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr}");
            } else {
                // L'utilisateur est l'objet de la relation
                $this->line("      - {$relation->user->name} → {$user->name} : {$relation->relationshipType->name_fr}");
            }
        }
    }
}
