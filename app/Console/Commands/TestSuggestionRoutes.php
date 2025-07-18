<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class TestSuggestionRoutes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:suggestion-routes';

    /**
     * The console command description.
     */
    protected $description = 'Teste les routes des suggestions pour vérifier qu\'il n\'y a pas de conflit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🧪 Test des routes de suggestions");
        $this->newLine();
        
        // Récupérer toutes les routes liées aux suggestions
        $suggestionRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_contains($route->uri(), 'suggestions');
        });
        
        $this->info("📋 Routes trouvées pour 'suggestions' :");
        $this->newLine();
        
        foreach ($suggestionRoutes as $route) {
            $methods = implode('|', $route->methods());
            $uri = $route->uri();
            $action = $route->getActionName();
            
            // Extraire le nom de la méthode du contrôleur
            if (str_contains($action, '@')) {
                $methodName = explode('@', $action)[1];
            } else {
                $methodName = 'Closure';
            }
            
            $this->line("   {$methods} /{$uri} → {$methodName}");
        }
        
        $this->newLine();
        
        // Vérifier s'il y a des routes en double
        $uris = $suggestionRoutes->pluck('uri')->toArray();
        $duplicates = array_count_values($uris);
        $hasDuplicates = false;
        
        foreach ($duplicates as $uri => $count) {
            if ($count > 1) {
                $this->warn("⚠️  Route en double détectée : /{$uri} ({$count} fois)");
                $hasDuplicates = true;
            }
        }
        
        if (!$hasDuplicates) {
            $this->info("✅ Aucune route en double détectée");
        }
        
        // Vérifier les méthodes du contrôleur
        $this->newLine();
        $this->info("🔍 Vérification des méthodes du contrôleur :");
        
        $controllerClass = \App\Http\Controllers\SuggestionController::class;
        $methods = get_class_methods($controllerClass);
        
        $expectedMethods = ['index', 'store', 'update', 'destroy', 'acceptWithCorrection'];
        $missingMethods = [];
        
        foreach ($expectedMethods as $method) {
            if (in_array($method, $methods)) {
                $this->line("   ✅ {$method}() - Présente");
            } else {
                $this->line("   ❌ {$method}() - Manquante");
                $missingMethods[] = $method;
            }
        }
        
        // Vérifier s'il y a des méthodes inattendues qui pourraient causer des problèmes
        $unexpectedMethods = ['show', 'create', 'edit'];
        foreach ($unexpectedMethods as $method) {
            if (in_array($method, $methods)) {
                $this->warn("   ⚠️  {$method}() - Présente mais non utilisée");
            }
        }
        
        $this->newLine();
        
        if (empty($missingMethods)) {
            $this->info("🎯 Test terminé avec succès !");
            $this->info("✅ Toutes les méthodes requises sont présentes");
            $this->info("✅ Aucune route en double");
            $this->info("💡 L'erreur 'Call to undefined method show()' devrait être corrigée");
        } else {
            $this->error("❌ Méthodes manquantes : " . implode(', ', $missingMethods));
        }
    }
}
