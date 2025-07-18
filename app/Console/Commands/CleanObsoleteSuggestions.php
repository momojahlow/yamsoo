<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;

class CleanObsoleteSuggestions extends Command
{
    protected $signature = 'clean:obsolete-suggestions';
    protected $description = 'Nettoyer les suggestions obsolètes';

    public function handle()
    {
        $this->info('🧹 NETTOYAGE DES SUGGESTIONS OBSOLÈTES');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        $deletedCount = 0;

        // Récupérer toutes les suggestions
        $suggestions = Suggestion::with(['user', 'suggestedUser'])->get();
        $this->info("📋 Suggestions à vérifier : {$suggestions->count()}");
        $this->newLine();

        foreach ($suggestions as $suggestion) {
            $user = $suggestion->user;
            $suggestedUser = $suggestion->suggestedUser;

            // Vérifier si une relation existe déjà entre ces deux utilisateurs
            $relationExists = FamilyRelationship::where(function($query) use ($user, $suggestedUser) {
                $query->where('user_id', $user->id)->where('related_user_id', $suggestedUser->id);
            })->orWhere(function($query) use ($user, $suggestedUser) {
                $query->where('user_id', $suggestedUser->id)->where('related_user_id', $user->id);
            })->exists();

            if ($relationExists) {
                $this->line("❌ Suppression : {$user->name} ↔ {$suggestedUser->name} (relation existante)");
                $suggestion->delete();
                $deletedCount++;
            } else {
                $this->line("✅ Conservation : {$user->name} → {$suggestedUser->name} (pas de relation)");
            }
        }

        $this->newLine();
        $this->info("🗑️  Suggestions supprimées : {$deletedCount}");
        $this->info("💡 Suggestions conservées : " . ($suggestions->count() - $deletedCount));

        // Vérifier le résultat
        $remainingSuggestions = Suggestion::count();
        $this->info("📊 Total suggestions restantes : {$remainingSuggestions}");

        return 0;
    }
}
