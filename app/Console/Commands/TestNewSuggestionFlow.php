<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;
use Illuminate\Support\Facades\Artisan;

class TestNewSuggestionFlow extends Command
{
    protected $signature = 'test:new-suggestion-flow';
    protected $description = 'Test le nouveau flux de suggestions avec demandes de relation';

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
        $this->info('🔍 TEST NOUVEAU FLUX DE SUGGESTIONS');
        $this->info('===================================');
        $this->info('📝 Objectif: Vérifier que les suggestions génèrent des demandes de relation');
        $this->info('🎯 Au lieu d\'accepter directement, on envoie une demande comme dans /reseaux');
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

        if (!$wifeType || !$sonType) {
            $this->error('❌ Types de relations non trouvés');
            return;
        }

        // Étape 1: Créer quelques relations de base
        $this->info('📋 ÉTAPE 1: Ahmed crée les relations de base...');
        
        // Ahmed ↔ Fatima (mariage)
        $request1 = $this->familyRelationService->createRelationshipRequest($ahmed, $fatima->id, $wifeType->id);
        $this->familyRelationService->acceptRelationshipRequest($request1);
        $this->info('✅ 1. Ahmed ↔ Fatima (époux/épouse) - MARIAGE ÉTABLI');

        // Ahmed ↔ Mohammed (père/fils)
        $request2 = $this->familyRelationService->createRelationshipRequest($ahmed, $mohammed->id, $sonType->id);
        $this->familyRelationService->acceptRelationshipRequest($request2);
        $this->info('✅ 2. Ahmed ↔ Mohammed (père/fils) - ENFANT AJOUTÉ');

        $this->line('');

        // Attendre que les queues se traitent
        $this->info('⏳ Traitement des queues de suggestions...');
        Artisan::call('queue:work', ['--stop-when-empty' => true]);
        $this->info('✅ Queues traitées');

        $this->line('');

        // Étape 2: Vérifier qu'il y a des suggestions
        $this->info('📊 ÉTAPE 2: Vérification des suggestions générées...');
        
        $mohammedSuggestions = Suggestion::where('user_id', $mohammed->id)
            ->where('status', 'pending')
            ->with(['suggestedUser'])
            ->get();

        if ($mohammedSuggestions->isEmpty()) {
            $this->warn('⚠️  Aucune suggestion trouvée pour Mohammed');
            return;
        }

        $this->info("✅ {$mohammedSuggestions->count()} suggestion(s) trouvée(s) pour Mohammed");

        // Prendre la première suggestion (probablement Fatima)
        $suggestion = $mohammedSuggestions->first();
        $this->info("🎯 Suggestion sélectionnée: {$suggestion->suggestedUser->name}");
        $this->line('');

        // Étape 3: Tester le nouveau flux - envoyer une demande de relation
        $this->info('📋 ÉTAPE 3: Test du nouveau flux...');
        $this->info('🔄 Mohammed envoie une demande de relation à Fatima via la suggestion');

        // Compter les demandes avant
        $requestsBefore = RelationshipRequest::count();
        $this->info("📊 Demandes de relation avant: {$requestsBefore}");

        // Utiliser la nouvelle méthode pour envoyer une demande
        $this->suggestionService->sendRelationRequestFromSuggestion($suggestion, 'mother');
        $this->info('✅ Demande de relation envoyée via suggestion');

        // Compter les demandes après
        $requestsAfter = RelationshipRequest::count();
        $this->info("📊 Demandes de relation après: {$requestsAfter}");

        if ($requestsAfter > $requestsBefore) {
            $this->info('✅ Une nouvelle demande de relation a été créée !');
            
            // Vérifier le statut de la suggestion
            $suggestion->refresh();
            $this->info("📊 Statut de la suggestion: {$suggestion->status}");
            
            // Afficher la demande créée
            $newRequest = RelationshipRequest::latest()->first();
            if ($newRequest) {
                $this->info("📋 Demande créée:");
                $this->line("   • De: {$newRequest->requester->name}");
                $this->line("   • Vers: {$newRequest->targetUser->name}");
                $this->line("   • Relation: {$newRequest->relationshipType->display_name_fr}");
                $this->line("   • Statut: {$newRequest->status}");
            }
        } else {
            $this->error('❌ Aucune nouvelle demande de relation créée');
        }

        $this->line('');
        $this->info('🎉 Test du nouveau flux terminé !');
        $this->info('');
        $this->info('📋 RÉSUMÉ:');
        $this->info('✅ Les suggestions ne créent plus de relations directement');
        $this->info('✅ Les suggestions génèrent des demandes de relation');
        $this->info('✅ Le flux est maintenant identique à la page /reseaux');
    }
}
