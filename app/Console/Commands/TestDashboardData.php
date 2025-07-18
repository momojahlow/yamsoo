<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;

class TestDashboardData extends Command
{
    protected $signature = 'test:dashboard-data';
    protected $description = 'Tester les données du dashboard directement';

    protected FamilyRelationService $familyRelationService;
    protected SuggestionService $suggestionService;

    public function __construct(FamilyRelationService $familyRelationService, SuggestionService $suggestionService)
    {
        parent::__construct();
        $this->familyRelationService = $familyRelationService;
        $this->suggestionService = $suggestionService;
    }

    public function handle()
    {
        $this->info('🏠 TEST DES DONNÉES DU DASHBOARD');
        $this->info('═══════════════════════════════');
        $this->newLine();

        // Tester avec Fatima
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        
        if (!$fatima) {
            $this->error('❌ Utilisateur Fatima non trouvé');
            return 1;
        }

        $this->info("👩 Test avec : {$fatima->name}");
        $this->line("   Email : {$fatima->email}");
        $this->line("   Profil : " . ($fatima->profile ? 'Oui' : 'Non'));
        if ($fatima->profile) {
            $this->line("   Prénom : {$fatima->profile->first_name}");
            $this->line("   Genre : {$fatima->profile->gender}");
        }
        $this->newLine();

        // Tester les services
        $relationships = $this->familyRelationService->getUserRelationships($fatima);
        $statistics = $this->familyRelationService->getFamilyStatistics($fatima);
        $suggestions = $this->suggestionService->getUserSuggestions($fatima);

        $this->info('📊 DONNÉES POUR LE DASHBOARD :');
        $this->line("   👥 Relations : {$relationships->count()}");
        $this->line("   💡 Suggestions : {$suggestions->count()}");
        $this->line("   🤖 Relations automatiques : {$statistics['automatic_relations']}");
        $this->line("   👤 Relations manuelles : {$statistics['manual_relations']}");
        $this->newLine();

        $this->info('🔗 DÉTAIL DES RELATIONS :');
        foreach ($relationships as $relation) {
            $auto = $relation->created_automatically ? ' 🤖' : ' 👤';
            $this->line("   - {$relation->relatedUser->name} : {$relation->relationshipType->name_fr}{$auto}");
        }
        $this->newLine();

        $this->info('💡 DÉTAIL DES SUGGESTIONS :');
        foreach ($suggestions->take(5) as $suggestion) {
            $relationName = $suggestion->suggested_relation_name ?: $suggestion->suggested_relation_code;
            $this->line("   - {$suggestion->suggestedUser->name} : {$relationName}");
        }
        $this->newLine();

        $this->info('✅ Le dashboard devrait maintenant afficher :');
        $this->line("   🏠 Salutation personnalisée avec emoji de genre");
        $this->line("   📊 Statistiques réelles des relations");
        $this->line("   🎯 Activités récentes basées sur les vraies données");
        $this->line("   💡 Suggestions intelligentes filtrées");
        $this->line("   🎨 Interface moderne avec dégradés et animations");
        $this->newLine();

        $this->info('🌐 Visitez maintenant : https://yamsoo.test/dashboard');

        return 0;
    }
}
