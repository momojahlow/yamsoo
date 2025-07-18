<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class TestAutomaticSuggestions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:automatic-suggestions {user-id : ID de l\'utilisateur}';

    /**
     * The console command description.
     */
    protected $description = 'Teste la génération automatique de suggestions';

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
        $userId = $this->argument('user-id');
        
        $user = User::with('profile')->find($userId);
        
        if (!$user) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");
            return;
        }
        
        $this->info("🧪 Test de génération automatique pour : {$user->name}");
        $this->newLine();
        
        // Nettoyer les anciennes suggestions
        \App\Models\Suggestion::where('user_id', $user->id)->delete();
        $this->info("🧹 Anciennes suggestions supprimées");
        
        // Tester la génération automatique
        $this->info("🔄 Génération automatique en cours...");
        
        try {
            $suggestions = $this->suggestionService->generateSuggestions($user);
            
            $this->info("📊 Résultats :");
            $this->line("   • Suggestions générées : {$suggestions->count()}");
            
            if ($suggestions->isNotEmpty()) {
                $this->newLine();
                $this->info("📋 Détails des suggestions :");
                
                foreach ($suggestions as $index => $suggestion) {
                    $suggestedUser = $suggestion->suggestedUser;
                    $relationName = $suggestion->suggested_relation_name ?? 'Non définie';
                    
                    $this->line("   " . ($index + 1) . ". {$suggestedUser->name}");
                    $this->line("      Type: {$suggestion->type}");
                    $this->line("      Relation suggérée: {$relationName}");
                    $this->line("      Message: {$suggestion->message}");
                    $this->newLine();
                }
                
                // Sauvegarder les suggestions générées
                $this->info("💾 Sauvegarde des suggestions...");
                
                foreach ($suggestions as $suggestion) {
                    $this->suggestionService->createSuggestion(
                        $user,
                        $suggestion->suggestedUser->id,
                        $suggestion->type,
                        $suggestion->message,
                        $suggestion->suggested_relation_code
                    );
                }
                
                $this->info("✅ {$suggestions->count()} suggestions sauvegardées avec succès !");
                
            } else {
                $this->warn("⚠️  Aucune suggestion générée automatiquement");
                $this->line("   Cela peut arriver si :");
                $this->line("   • L'utilisateur n'a pas de relations familiales");
                $this->line("   • Tous les contacts potentiels sont déjà liés");
                $this->line("   • Il n'y a pas assez de données pour l'inférence");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la génération : " . $e->getMessage());
            $this->line("Trace : " . $e->getTraceAsString());
        }
        
        $this->newLine();
        $this->info("🎯 Test terminé. Vérifiez les suggestions sur yamsoo.test/suggestions");
    }
}
