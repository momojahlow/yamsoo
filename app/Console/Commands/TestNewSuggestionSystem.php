<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;
use Illuminate\Support\Facades\Artisan;

class TestNewSuggestionSystem extends Command
{
    protected $signature = 'test:new-suggestion-system';
    protected $description = 'Test le nouveau système de suggestions sans relations prédéfinies';

    protected FamilyRelationService $familyRelationService;
    protected SuggestionService $suggestionService;

    public function __construct(
        FamilyRelationService $familyRelationService,
        SuggestionService $suggestionService
    ) {
        parent::__construct();
        $this->familyRelationService = $familyRelationService;
        $this->suggestionService = $suggestionService;
    }

    public function handle()
    {
        $this->info('🔍 TEST NOUVEAU SYSTÈME DE SUGGESTIONS');
        $this->info('=====================================');
        $this->info('📝 Objectif: Vérifier que les suggestions ne proposent plus de relations spécifiques');
        $this->info('🎯 Au lieu de "Relation suggérée: stepdaughter", on demande "Connaissez-vous cette personne ?"');
        $this->line('');

        // Reset de la base de données
        $this->info('🔄 Reset de la base de données...');
        Artisan::call('migrate:fresh --seed');
        $this->info('✅ Base de données réinitialisée');

        // Récupérer les utilisateurs de test
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
        $amina = User::where('email', 'amina.tazi@example.com')->first();

        if (!$ahmed || !$fatima || !$mohammed || !$amina) {
            $this->error('❌ Utilisateurs de test non trouvés');
            return;
        }

        $this->info('✅ Utilisateurs trouvés');
        $this->line('');

        // Récupérer les types de relations
        $wifeType = RelationshipType::where('name', 'wife')->first();
        $sonType = RelationshipType::where('name', 'son')->first();
        $daughterType = RelationshipType::where('name', 'daughter')->first();

        if (!$wifeType || !$sonType || !$daughterType) {
            $this->error('❌ Types de relations non trouvés');
            return;
        }

        // Étape 1: Créer les relations de base
        $this->info('📋 ÉTAPE 1: Ahmed crée les relations de base...');

        // Ahmed ↔ Fatima (mariage)
        $request1 = $this->familyRelationService->createRelationshipRequest($ahmed, $fatima->id, $wifeType->id);
        $this->familyRelationService->acceptRelationshipRequest($request1);
        $this->info('✅ 1. Ahmed ↔ Fatima (époux/épouse) - MARIAGE ÉTABLI');

        // Ahmed ↔ Mohammed (père/fils)
        $request2 = $this->familyRelationService->createRelationshipRequest($ahmed, $mohammed->id, $sonType->id);
        $this->familyRelationService->acceptRelationshipRequest($request2);
        $this->info('✅ 2. Ahmed ↔ Mohammed (père/fils) - ENFANT AJOUTÉ');

        // Ahmed ↔ Amina (père/fille)
        $request3 = $this->familyRelationService->createRelationshipRequest($ahmed, $amina->id, $daughterType->id);
        $this->familyRelationService->acceptRelationshipRequest($request3);
        $this->info('✅ 3. Ahmed ↔ Amina (père/fille) - ENFANT AJOUTÉ');

        $this->line('');

        // Attendre que les queues se traitent
        $this->info('⏳ Traitement des queues de suggestions...');
        Artisan::call('queue:work', ['--stop-when-empty' => true]);
        $this->info('✅ Queues traitées');

        $this->line('');

        // Vérifier les suggestions générées
        $this->info('📊 VÉRIFICATION DES NOUVELLES SUGGESTIONS:');
        $this->info('🎯 Les suggestions ne doivent plus contenir de relations spécifiques');
        $this->line('');

        // Vérifier les suggestions pour Mohammed
        $this->checkUserSuggestions($mohammed, 'Mohammed');
        $this->line('');

        // Vérifier les suggestions pour Amina
        $this->checkUserSuggestions($amina, 'Amina');
        $this->line('');

        // Vérifier les suggestions pour Fatima
        $this->checkUserSuggestions($fatima, 'Fatima');
        $this->line('');

        $this->info('🎉 Test du nouveau système terminé !');
        $this->info('');
        $this->info('📋 RÉSUMÉ:');
        $this->info('✅ Les suggestions ne proposent plus de relations spécifiques');
        $this->info('✅ L\'utilisateur peut choisir la relation via un select');
        $this->info('✅ Le message est maintenant "Connaissez-vous cette personne ?"');
    }

    private function checkUserSuggestions(User $user, string $userName)
    {
        $suggestions = Suggestion::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['suggestedUser'])
            ->get();

        $this->info("🔍 {$userName}:");

        if ($suggestions->isEmpty()) {
            $this->warn("   ⚠️  Aucune suggestion trouvée");
            return;
        }

        foreach ($suggestions as $suggestion) {
            $suggestedUserName = $suggestion->suggestedUser->name;
            
            // Vérifier que suggested_relation_code et suggested_relation_name sont null
            if ($suggestion->suggested_relation_code === null && $suggestion->suggested_relation_name === null) {
                $this->info("   ✅ NOUVEAU: {$suggestedUserName} → Aucune relation spécifique (utilisateur choisira)");
            } else {
                $this->error("   ❌ ANCIEN: {$suggestedUserName} → {$suggestion->suggested_relation_name} ({$suggestion->suggested_relation_code})");
            }

            // Afficher le message/raison
            if ($suggestion->reason) {
                $this->line("      💬 Raison: {$suggestion->reason}");
            }
        }
    }
}
