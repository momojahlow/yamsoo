<?php

namespace App\Console\Commands;

use App\Models\Suggestion;
use Illuminate\Console\Command;

class ShowSuggestions extends Command
{
    protected $signature = 'suggestions:show';
    protected $description = 'Afficher toutes les suggestions existantes';

    public function handle()
    {
        $this->info('📋 Suggestions existantes dans la base de données:');
        
        $suggestions = Suggestion::with(['user', 'suggestedUser'])
            ->orderBy('user_id')
            ->get();

        if ($suggestions->isEmpty()) {
            $this->info('(Aucune suggestion trouvée)');
            return;
        }

        $groupedSuggestions = $suggestions->groupBy('user.name');

        foreach ($groupedSuggestions as $userName => $userSuggestions) {
            $this->info("\n🌳 Suggestions pour {$userName}:");

            foreach ($userSuggestions as $suggestion) {
                $suggestedUser = $suggestion->suggestedUser;
                $relationName = $suggestion->suggested_relation_name ?? $suggestion->suggested_relation_code ?? 'Relation inconnue';
                $relationCode = $suggestion->suggested_relation_code ?? 'unknown';
                $status = $suggestion->status;

                $statusIcon = match($status) {
                    'pending' => '⏳',
                    'accepted' => '✅',
                    'rejected' => '❌',
                    default => '❓'
                };

                $this->info("  {$statusIcon} {$suggestedUser->name} comme {$relationName} ({$relationCode}) - {$status}");
            }
        }
        
        $this->info("\n📊 Statistiques:");
        $this->info("  Total: " . $suggestions->count());
        $this->info("  En attente: " . $suggestions->where('status', 'pending')->count());
        $this->info("  Acceptées: " . $suggestions->where('status', 'accepted')->count());
        $this->info("  Rejetées: " . $suggestions->where('status', 'rejected')->count());
    }
}
