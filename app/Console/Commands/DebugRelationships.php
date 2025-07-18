<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;

class DebugRelationships extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'debug:relationships';

    /**
     * The description of the console command.
     */
    protected $description = 'Debug des relations étape par étape';

    protected FamilyRelationService $familyRelationService;

    public function __construct(FamilyRelationService $familyRelationService)
    {
        parent::__construct();
        $this->familyRelationService = $familyRelationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 DEBUG DES RELATIONS ÉTAPE PAR ÉTAPE');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        // Nettoyer d'abord
        $this->call('db:seed', ['--class' => 'CleanDatabaseSeeder']);
        $this->newLine();

        // Récupérer les utilisateurs
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();

        $this->info("👥 Utilisateurs de test :");
        $this->line("   👨 Ahmed Benali (ID: {$ahmed->id})");
        $this->line("   👩 Fatima Zahra (ID: {$fatima->id})");
        $this->line("   👨 Mohammed Alami (ID: {$mohammed->id})");
        $this->line("   👨 Youssef Bennani (ID: {$youssef->id})");
        $this->newLine();

        // Test 1: Ahmed devient le père de Fatima
        $this->info('🔗 TEST 1: Ahmed devient le père de Fatima');
        $this->line('─────────────────────────────────────────');
        $this->createAndAcceptRelation($ahmed, $fatima, 'father');
        $this->displayAllRelations();
        $this->newLine();

        // Test 2: Ahmed devient le père de Mohammed
        $this->info('🔗 TEST 2: Ahmed devient le père de Mohammed');
        $this->line('─────────────────────────────────────────────');
        $this->createAndAcceptRelation($ahmed, $mohammed, 'father');
        $this->displayAllRelations();
        $this->newLine();

        // Test 3: Youssef devient le frère d'Ahmed
        $this->info('🔗 TEST 3: Youssef devient le frère d\'Ahmed');
        $this->line('─────────────────────────────────────────────');
        $this->createAndAcceptRelation($youssef, $ahmed, 'brother');
        $this->displayAllRelations();

        $this->newLine();
        $this->info('✅ Debug terminé');

        return 0;
    }

    private function createAndAcceptRelation(User $requester, User $target, string $relationCode): void
    {
        $relationType = RelationshipType::where('code', $relationCode)->first();
        $this->info("   📋 Type de relation : {$relationType->name_fr}");

        $request = $this->familyRelationService->createRelationshipRequest(
            $requester,
            $target->id,
            $relationType->id,
            "Debug - {$requester->name} {$relationType->name_fr} de {$target->name}"
        );

        $relationship = $this->familyRelationService->acceptRelationshipRequest($request);
        $this->info("   ✅ Relation créée et acceptée");
    }

    private function displayAllRelations(): void
    {
        $allRelations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();

        if ($allRelations->count() === 0) {
            $this->line("      (Aucune relation dans la base)");
            return;
        }

        foreach ($allRelations as $relation) {
            $user = $relation->user;
            $relatedUser = $relation->relatedUser;
            $type = $relation->relationshipType;
            $auto = $relation->created_automatically ? ' 🤖' : ' 👤';

            $this->line("      - {$user->name} → {$relatedUser->name} : {$type->name_fr}{$auto} (ID: {$relation->id})");
        }
    }
}
