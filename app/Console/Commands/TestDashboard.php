<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;

class TestDashboard extends Command
{
    protected $signature = 'test:dashboard';
    protected $description = 'Tester les données du dashboard';

    public function handle()
    {
        $this->info('🏠 TEST DU DASHBOARD AMÉLIORÉ');
        $this->info('═══════════════════════════');
        $this->newLine();

        // Tester avec Fatima qui a des relations
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        
        if (!$fatima) {
            $this->error('❌ Utilisateur Fatima non trouvé');
            return 1;
        }

        $this->info("👩 Test avec l'utilisatrice : {$fatima->name}");
        $this->newLine();

        try {
            // Créer une instance du contrôleur
            $controller = app(DashboardController::class);
            
            // Créer une requête mock
            $request = Request::create('/dashboard', 'GET');
            $request->setUserResolver(function () use ($fatima) {
                return $fatima;
            });

            // Appeler la méthode index
            $response = $controller->index($request);
            
            $this->info('✅ Contrôleur appelé avec succès');
            
            // Vérifier les données retournées
            $props = $response->toResponse($request)->getData()['props'] ?? [];
            
            $this->info('📊 DONNÉES DU DASHBOARD :');
            
            if (isset($props['user'])) {
                $this->line("   👤 Utilisateur : {$props['user']['name']}");
            }
            
            if (isset($props['dashboardStats'])) {
                $stats = $props['dashboardStats'];
                $this->line("   📈 Statistiques :");
                $this->line("      - Membres famille : {$stats['total_family_members']}");
                $this->line("      - Nouveaux ce mois : {$stats['new_members_this_month']}");
                $this->line("      - Suggestions en attente : {$stats['pending_suggestions']}");
                $this->line("      - Relations automatiques : {$stats['automatic_relations']}");
                $this->line("      - Relations manuelles : {$stats['manual_relations']}");
            }
            
            if (isset($props['recentActivities'])) {
                $activities = $props['recentActivities'];
                $this->line("   🎯 Activités récentes : " . count($activities));
                
                foreach (array_slice($activities, 0, 3) as $activity) {
                    $this->line("      - {$activity['text']} ({$activity['time']})");
                }
            }
            
            if (isset($props['prioritySuggestions'])) {
                $suggestions = $props['prioritySuggestions'];
                $this->line("   💡 Suggestions prioritaires : " . count($suggestions));
                
                foreach ($suggestions as $suggestion) {
                    $this->line("      - {$suggestion['suggested_user']['name']} ({$suggestion['relation_name']})");
                }
            }
            
            if (isset($props['upcomingBirthdays'])) {
                $birthdays = $props['upcomingBirthdays'];
                $this->line("   🎂 Anniversaires à venir : " . count($birthdays));
                
                foreach ($birthdays as $birthday) {
                    $this->line("      - {$birthday['name']} dans {$birthday['days_until']} jour(s)");
                }
            }
            
            $this->newLine();
            $this->info('✅ Test du dashboard réussi !');
            $this->info('🌐 Vous pouvez maintenant visiter : https://yamsoo.test/dashboard');
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du test du dashboard :');
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
