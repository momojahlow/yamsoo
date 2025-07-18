<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;

class VerifyStatistics extends Command
{
    protected $signature = 'verify:statistics';
    protected $description = 'Vérifier les statistiques des relations';

    protected FamilyRelationService $familyRelationService;

    public function __construct(FamilyRelationService $familyRelationService)
    {
        parent::__construct();
        $this->familyRelationService = $familyRelationService;
    }

    public function handle()
    {
        $this->info('📊 VÉRIFICATION DES STATISTIQUES');
        $this->info('═══════════════════════════════');
        $this->newLine();

        // Statistiques globales
        $totalRelations = FamilyRelationship::count();
        $autoRelations = FamilyRelationship::where('created_automatically', true)->count();
        $manualRelations = FamilyRelationship::where('created_automatically', false)->count();

        $this->info('🌐 STATISTIQUES GLOBALES :');
        $this->line("   - Total relations : {$totalRelations}");
        $this->line("   - Relations automatiques : {$autoRelations}");
        $this->line("   - Relations manuelles : {$manualRelations}");
        $this->newLine();

        // Vérifier pour chaque utilisateur principal
        $users = User::whereIn('email', [
            'ahmed.benali@example.com',
            'mohammed.alami@example.com',
            'amina.tazi@example.com',
            'youssef.bennani@example.com',
            'fatima.zahra@example.com'
        ])->get();

        foreach ($users as $user) {
            $this->info("👤 {$user->name} :");
            
            // Relations directes (où l'utilisateur est user_id)
            $directRelations = FamilyRelationship::where('user_id', $user->id)->get();
            $directAuto = $directRelations->where('created_automatically', true)->count();
            $directManual = $directRelations->where('created_automatically', false)->count();
            
            $this->line("   📋 Relations directes : {$directRelations->count()} (auto: {$directAuto}, manuel: {$directManual})");
            
            foreach ($directRelations as $rel) {
                $auto = $rel->created_automatically ? ' 🤖' : ' 👤';
                $this->line("      - {$rel->relatedUser->name} : {$rel->relationshipType->name_fr}{$auto}");
            }
            
            // Relations via le service
            $serviceRelations = $this->familyRelationService->getUserRelationships($user);
            $this->line("   🔧 Relations via service : {$serviceRelations->count()}");
            
            // Statistiques via le service
            $statistics = $this->familyRelationService->getFamilyStatistics($user);
            $this->line("   📊 Statistiques service :");
            $this->line("      - Total : {$statistics['total_relatives']}");
            $this->line("      - Auto : {$statistics['automatic_relations']}");
            $this->line("      - Manuel : {$statistics['manual_relations']}");
            
            $this->newLine();
        }

        return 0;
    }
}
