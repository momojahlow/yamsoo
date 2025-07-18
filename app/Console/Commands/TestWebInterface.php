<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;

class TestWebInterface extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:web-interface';

    /**
     * The description of the console command.
     */
    protected $description = 'Préparer des données de test pour l\'interface web';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌐 PRÉPARATION DES DONNÉES POUR L\'INTERFACE WEB');
        $this->info('═══════════════════════════════════════════════');
        $this->newLine();

        // Vérifier les utilisateurs existants
        $users = User::with('profile')->get();
        
        $this->info("👥 UTILISATEURS DISPONIBLES :");
        foreach ($users as $user) {
            $gender = $user->profile?->gender === 'female' ? '👩' : '👨';
            $this->line("   {$gender} {$user->name} (ID: {$user->id}) - Email: {$user->email}");
        }
        $this->newLine();

        // Vérifier les relations existantes
        $relations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
        $this->info("🔗 RELATIONS EXISTANTES : {$relations->count()}");
        foreach ($relations as $relation) {
            $auto = $relation->created_automatically ? ' 🤖' : ' 👤';
            $this->line("   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr}{$auto}");
        }
        $this->newLine();

        // Vérifier les suggestions existantes
        $suggestions = Suggestion::with(['user', 'suggestedUser'])->get();
        $this->info("💡 SUGGESTIONS EXISTANTES : {$suggestions->count()}");
        foreach ($suggestions as $suggestion) {
            $this->line("   - {$suggestion->user->name} ← {$suggestion->suggestedUser->name} ({$suggestion->suggested_relation_code})");
        }
        $this->newLine();

        // Créer quelques relations de test pour l'interface
        $this->info("🔧 CRÉATION DE RELATIONS DE TEST...");
        
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        
        if ($fatima && $ahmed) {
            // Créer une relation simple pour tester l'interface
            $this->call('debug:relationships');
        }

        $this->newLine();
        $this->info("✅ DONNÉES PRÊTES POUR L'INTERFACE WEB !");
        $this->info("🌐 Ouvrez http://localhost:8000 dans votre navigateur");
        $this->info("📧 Connectez-vous avec : fatima.zahra@example.com");
        $this->info("🔑 Mot de passe : password");
        
        return 0;
    }
}
