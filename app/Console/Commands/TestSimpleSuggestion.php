<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class TestSimpleSuggestion extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:simple-suggestion';

    /**
     * The console command description.
     */
    protected $description = 'Test simple de génération de suggestion familiale';

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
        $this->info("🧪 Test simple de suggestion familiale");
        $this->newLine();
        
        // Scénario : Ahmed Benali (fils de Youssef) devrait voir Fatima Zahra (épouse de Youssef) comme belle-mère
        
        $ahmed = User::find(1); // Ahmed Benali
        $youssef = User::find(5); // Youssef Bennani
        $fatima = User::find(2); // Fatima Zahra
        
        if (!$ahmed || !$youssef || !$fatima) {
            $this->error("Utilisateurs non trouvés");
            return;
        }
        
        $this->info("👨‍👦 Scénario :");
        $this->line("• Ahmed Benali (ID: {$ahmed->id}) - fils de Youssef");
        $this->line("• Youssef Bennani (ID: {$youssef->id}) - père d'Ahmed, mari de Fatima");
        $this->line("• Fatima Zahra (ID: {$fatima->id}) - épouse de Youssef");
        $this->newLine();
        
        // Vérifier les relations existantes
        $this->info("🔍 Relations existantes :");
        
        $ahmedYoussefRelation = FamilyRelationship::where('user_id', $ahmed->id)
            ->where('related_user_id', $youssef->id)
            ->with('relationshipType')
            ->first();
            
        if ($ahmedYoussefRelation) {
            $this->line("• Ahmed → Youssef : {$ahmedYoussefRelation->relationshipType->name_fr}");
        }
        
        $youssefFatimaRelation = FamilyRelationship::where('user_id', $youssef->id)
            ->where('related_user_id', $fatima->id)
            ->with('relationshipType')
            ->first();
            
        if ($youssefFatimaRelation) {
            $this->line("• Youssef → Fatima : {$youssefFatimaRelation->relationshipType->name_fr}");
        }
        
        $this->newLine();
        
        // Test manuel de l'inférence
        $this->info("🧠 Test d'inférence manuelle :");
        
        if ($ahmedYoussefRelation && $youssefFatimaRelation) {
            // Ahmed est fils de Youssef, Fatima est épouse de Youssef
            // Donc Ahmed devrait voir Fatima comme belle-mère (ou mère adoptive)
            
            $ahmedRelationType = $ahmedYoussefRelation->relationshipType; // son
            $fatimaRelationType = $youssefFatimaRelation->relationshipType; // wife
            
            $this->line("• Ahmed est '{$ahmedRelationType->code}' de Youssef");
            $this->line("• Fatima est '{$fatimaRelationType->code}' de Youssef");
            $this->line("• Donc Ahmed devrait voir Fatima comme : belle-mère/mère");
            
            // Test de la méthode d'inférence
            $reflection = new \ReflectionClass($this->suggestionService);
            $method = $reflection->getMethod('inferFamilyRelation');
            $method->setAccessible(true);
            
            $result = $method->invoke(
                $this->suggestionService,
                $ahmedRelationType,
                $fatimaRelationType,
                $ahmed,
                $fatima,
                $youssef
            );
            
            if ($result) {
                $this->info("✅ Inférence réussie : {$result['description']} (code: {$result['code']})");
            } else {
                $this->error("❌ Inférence échouée - aucun résultat");
            }
        }
        
        $this->newLine();
        
        // Test de génération de suggestions complète
        $this->info("🎯 Test de génération complète :");
        
        $suggestions = $this->suggestionService->generateSuggestions($ahmed);
        
        if ($suggestions->isEmpty()) {
            $this->warn("⚠️  Aucune suggestion générée");
        } else {
            foreach ($suggestions as $suggestion) {
                $suggestedUser = $suggestion->suggestedUser;
                $this->line("• {$suggestedUser->name} - {$suggestion->type}");
                $this->line("  Raison: {$suggestion->message}");
                if ($suggestion->suggested_relation_code) {
                    $this->line("  Relation: {$suggestion->suggested_relation_code}");
                }
            }
        }
    }
}
