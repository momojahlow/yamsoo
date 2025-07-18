<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class GenerateSuggestionsForMohammed extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'generate:suggestions-mohammed';

    /**
     * The console command description.
     */
    protected $description = 'Génère des suggestions pour Mohammed Alami';

    protected SuggestionService $suggestionService;

    public function __construct(SuggestionService $suggestionService)
    {
        parent::__construct();
        $this->suggestionService = $suggestionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🎯 Génération de suggestions pour Mohammed Alami");
        
        $mohammed = User::find(3); // Mohammed Alami
        
        if (!$mohammed) {
            $this->error("Mohammed Alami non trouvé");
            return;
        }
        
        $this->info("Utilisateur trouvé : {$mohammed->name}");
        
        // Nettoyer les anciennes suggestions
        \App\Models\Suggestion::where('user_id', $mohammed->id)->delete();
        $this->info("Anciennes suggestions supprimées");
        
        // Créer les suggestions manuellement
        $fatima = User::find(2); // Fatima Zahra
        $ahmed = User::find(1);  // Ahmed Benali
        $amina = User::find(4);  // Amina Tazi
        
        $suggestionsCreated = 0;
        
        if ($fatima) {
            $this->suggestionService->createSuggestion(
                $mohammed,
                $fatima->id,
                'family_connection',
                'Via Youssef Bennani - Parent par alliance - belle-mère',
                'mother'
            );
            $this->line("✅ Suggestion créée : {$fatima->name} comme mère");
            $suggestionsCreated++;
        }
        
        if ($ahmed) {
            $this->suggestionService->createSuggestion(
                $mohammed,
                $ahmed->id,
                'family_connection',
                'Via Youssef Bennani - Frère par alliance',
                'brother'
            );
            $this->line("✅ Suggestion créée : {$ahmed->name} comme frère");
            $suggestionsCreated++;
        }
        
        if ($amina) {
            $this->suggestionService->createSuggestion(
                $mohammed,
                $amina->id,
                'family_connection',
                'Via Youssef Bennani - Sœur par alliance',
                'sister'
            );
            $this->line("✅ Suggestion créée : {$amina->name} comme sœur");
            $suggestionsCreated++;
        }
        
        $this->newLine();
        $this->info("🎉 {$suggestionsCreated} suggestions générées avec succès !");
        $this->info("Mohammed Alami peut maintenant voir ses suggestions sur yamsoo.test/suggestions");
        
        // Vérifier les suggestions créées
        $suggestions = $this->suggestionService->getUserSuggestions($mohammed);
        $this->newLine();
        $this->info("📋 Vérification des suggestions créées :");
        
        foreach ($suggestions as $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $relationName = $suggestion->suggested_relation_name ?? 'Non définie';
            $this->line("• {$suggestedUser->name} - Relation: {$relationName}");
            $this->line("  Message: {$suggestion->message}");
        }
    }
}
