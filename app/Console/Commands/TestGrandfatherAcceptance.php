<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Suggestion;
use App\Services\SuggestionService;

class TestGrandfatherAcceptance extends Command
{
    protected $signature = 'test:grandfather-acceptance';
    protected $description = 'Test l\'acceptation d\'une suggestion Grand-père';

    public function handle()
    {
        $this->info("🔍 Test d'acceptation suggestion Grand-père");
        
        try {
            // Récupérer Karim (qui devrait avoir une suggestion pour Ahmed comme Grand-père)
            $karim = User::where('email', 'karim.elfassi@example.com')->first();
            $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
            
            if (!$karim || !$ahmed) {
                $this->error("❌ Utilisateurs non trouvés");
                return;
            }
            
            $this->info("✅ Utilisateurs trouvés: Karim et Ahmed");
            
            // Récupérer les suggestions de Karim
            $suggestionService = app(SuggestionService::class);
            $suggestions = $suggestionService->getUserSuggestions($karim);
            
            $this->info("📋 Suggestions pour Karim: " . $suggestions->count());
            
            // Afficher toutes les suggestions
            foreach ($suggestions as $suggestion) {
                $this->line("- {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_name} (code: {$suggestion->suggested_relation_code})");
            }
            
            // Trouver la suggestion pour Ahmed comme Grand-père
            $grandfatherSuggestion = $suggestions->first(function($s) use ($ahmed) {
                return $s->suggested_user_id == $ahmed->id && $s->suggested_relation_code == 'grandfather';
            });
            
            if (!$grandfatherSuggestion) {
                $this->error("❌ Suggestion Grand-père non trouvée");
                return;
            }
            
            $this->info("🎯 Suggestion trouvée: Karim → Ahmed (Grand-père)");
            $this->info("📋 ID suggestion: {$grandfatherSuggestion->id}");
            $this->info("📋 Code relation: {$grandfatherSuggestion->suggested_relation_code}");
            $this->info("📋 Nom relation: {$grandfatherSuggestion->suggested_relation_name}");
            
            // Tester l'acceptation
            $this->info("🔄 Test d'acceptation...");
            
            $result = $suggestionService->acceptSuggestion($grandfatherSuggestion, $grandfatherSuggestion->suggested_relation_code);
            
            $this->info("✅ Suggestion acceptée avec succès !");
            $this->info("📋 Résultat: " . json_encode($result));
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            $this->error("📍 Fichier: " . $e->getFile() . ":" . $e->getLine());
            
            // Afficher la trace complète
            $this->error("🔍 Trace:");
            $trace = $e->getTrace();
            foreach (array_slice($trace, 0, 10) as $i => $frame) {
                $file = $frame['file'] ?? 'unknown';
                $line = $frame['line'] ?? 'unknown';
                $function = $frame['function'] ?? 'unknown';
                $this->error("#{$i} {$file}({$line}): {$function}()");
            }
        }
    }
}
