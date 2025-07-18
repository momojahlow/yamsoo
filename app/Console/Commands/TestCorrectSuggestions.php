<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Suggestion;
use App\Services\IntelligentSuggestionService;

class TestCorrectSuggestions extends Command
{
    protected $signature = 'test:correct-suggestions';
    protected $description = 'Tester les suggestions corrigées (mère au lieu de belle-mère)';

    protected IntelligentSuggestionService $intelligentSuggestionService;

    public function __construct(IntelligentSuggestionService $intelligentSuggestionService)
    {
        parent::__construct();
        $this->intelligentSuggestionService = $intelligentSuggestionService;
    }

    public function handle()
    {
        $this->info('🔧 TEST DES SUGGESTIONS CORRIGÉES');
        $this->info('═══════════════════════════════');
        $this->newLine();

        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();

        if (!$fatima || !$youssef) {
            $this->error('❌ Utilisateurs non trouvés');
            return 1;
        }

        // Supprimer les anciennes suggestions intelligentes
        $this->info('🗑️  Suppression des anciennes suggestions...');
        $oldSuggestions = Suggestion::where('user_id', $fatima->id)
            ->where('type', 'intelligent')
            ->get();

        foreach ($oldSuggestions as $suggestion) {
            $this->line("   ❌ {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code}");
            $suggestion->delete();
        }

        $this->newLine();

        // Régénérer les suggestions avec la nouvelle logique
        $this->info('🧠 GÉNÉRATION DES NOUVELLES SUGGESTIONS...');
        $newSuggestions = $this->intelligentSuggestionService->generateIntelligentSuggestions($fatima);
        
        $this->info("✅ {$newSuggestions} nouvelles suggestions créées");
        $this->newLine();

        // Vérifier les nouvelles suggestions
        $this->info('💡 NOUVELLES SUGGESTIONS POUR FATIMA :');
        $suggestions = Suggestion::where('user_id', $fatima->id)
            ->where('type', 'intelligent')
            ->with('suggestedUser')
            ->get();

        foreach ($suggestions as $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $gender = $suggestedUser->profile?->gender;
            $genderIcon = $gender === 'female' ? '👩' : '👨';
            
            $this->line("   {$genderIcon} {$suggestedUser->name} : {$suggestion->suggested_relation_code} ({$suggestion->suggested_relation_name})");
            
            // Vérifier si c'est correct maintenant
            if (in_array($suggestedUser->email, ['mohammed.alami@example.com', 'ahmed.benali@example.com'])) {
                if ($suggestion->suggested_relation_code === 'son') {
                    $this->line("      ✅ CORRECT : Suggéré comme fils (plus comme beau-père !)");
                } else {
                    $this->line("      ❌ ERREUR : Encore mal suggéré");
                }
            }
            
            if (in_array($suggestedUser->email, ['amina.tazi@example.com', 'leila.mansouri@example.com'])) {
                if ($suggestion->suggested_relation_code === 'daughter') {
                    $this->line("      ✅ CORRECT : Suggérée comme fille");
                } else {
                    $this->line("      ❌ ERREUR : Encore mal suggérée");
                }
            }
        }

        $this->newLine();

        // Tester aussi les suggestions réciproques
        $this->info('🔄 VÉRIFICATION DES SUGGESTIONS RÉCIPROQUES :');
        
        $children = User::whereIn('email', [
            'mohammed.alami@example.com',
            'ahmed.benali@example.com',
            'amina.tazi@example.com',
            'leila.mansouri@example.com'
        ])->get();

        foreach ($children as $child) {
            $childSuggestions = Suggestion::where('user_id', $child->id)
                ->where('suggested_user_id', $fatima->id)
                ->where('type', 'intelligent')
                ->get();

            if ($childSuggestions->count() > 0) {
                foreach ($childSuggestions as $suggestion) {
                    $this->line("   👤 {$child->name} → Fatima : {$suggestion->suggested_relation_code}");
                    
                    if ($suggestion->suggested_relation_code === 'mother') {
                        $this->line("      ✅ CORRECT : Fatima suggérée comme mère");
                    } else {
                        $this->line("      ❌ ERREUR : {$suggestion->suggested_relation_code} au lieu de mother");
                    }
                }
            } else {
                $this->line("   ⚠️  {$child->name} : Aucune suggestion vers Fatima");
            }
        }

        $this->newLine();
        $this->info('🎯 RÉSULTAT ATTENDU :');
        $this->line('   ✅ Fatima → Mohammed : Fils (son)');
        $this->line('   ✅ Fatima → Ahmed : Fils (son)');
        $this->line('   ✅ Fatima → Amina : Fille (daughter)');
        $this->line('   ✅ Fatima → Leila : Fille (daughter)');
        $this->line('   ✅ Mohammed/Ahmed/Amina/Leila → Fatima : Mère (mother)');

        return 0;
    }
}
