<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Suggestion;
use App\Services\IntelligentSuggestionService;

class FixKarimSuggestion extends Command
{
    protected $signature = 'fix:karim-suggestion';
    protected $description = 'Corriger la suggestion de Karim El Fassi pour qu\'il soit suggéré comme fils au lieu de beau-fils';

    public function handle()
    {
        $this->info('🔧 CORRECTION DE LA SUGGESTION KARIM EL FASSI');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Trouver Nadia et Karim
        $nadia = User::where('name', 'like', '%Nadia%')->where('name', 'like', '%Berrada%')->first();
        $karim = User::where('name', 'like', '%Karim%')->where('name', 'like', '%El Fassi%')->first();
        
        if (!$nadia || !$karim) {
            $this->error('❌ Utilisateurs non trouvés');
            return 1;
        }

        $this->info("👤 UTILISATEURS :");
        $this->line("   • Nadia Berrada (ID: {$nadia->id})");
        $this->line("   • Karim El Fassi (ID: {$karim->id})");
        $this->newLine();

        // Supprimer l'ancienne suggestion de Karim
        $this->info('1️⃣ SUPPRESSION DE L\'ANCIENNE SUGGESTION :');
        $oldSuggestion = Suggestion::where('user_id', $nadia->id)
            ->where('suggested_user_id', $karim->id)
            ->first();

        if ($oldSuggestion) {
            $this->line("   📋 Ancienne suggestion trouvée :");
            $this->line("      • Code relation: {$oldSuggestion->suggested_relation_code}");
            $this->line("      • Nom relation: " . ($oldSuggestion->suggested_relation_name ?: 'NON DÉFINI'));
            $this->line("      • Statut: {$oldSuggestion->status}");
            
            $oldSuggestion->delete();
            $this->line("   ✅ Ancienne suggestion supprimée");
        } else {
            $this->line("   ⚠️  Aucune suggestion existante trouvée");
        }
        $this->newLine();

        // Régénérer les suggestions pour Nadia
        $this->info('2️⃣ RÉGÉNÉRATION DES SUGGESTIONS :');
        $suggestionService = app(IntelligentSuggestionService::class);
        $newSuggestions = $suggestionService->generateIntelligentSuggestions($nadia);
        $this->line("   ✨ {$newSuggestions} nouvelles suggestions générées");
        $this->newLine();

        // Vérifier la nouvelle suggestion de Karim
        $this->info('3️⃣ VÉRIFICATION DE LA NOUVELLE SUGGESTION :');
        $newSuggestion = Suggestion::where('user_id', $nadia->id)
            ->where('suggested_user_id', $karim->id)
            ->first();

        if ($newSuggestion) {
            $this->line("   📋 Nouvelle suggestion trouvée :");
            $this->line("      • Code relation: {$newSuggestion->suggested_relation_code}");
            $this->line("      • Nom relation: " . ($newSuggestion->suggested_relation_name ?: 'NON DÉFINI'));
            $this->line("      • Statut: {$newSuggestion->status}");
            $this->line("      • Message: " . ($newSuggestion->message ?: 'NON DÉFINI'));
            
            if ($newSuggestion->suggested_relation_code === 'son') {
                $this->line("   ✅ SUCCÈS ! Karim est maintenant suggéré comme 'Fils'");
            } elseif ($newSuggestion->suggested_relation_code === 'stepson') {
                $this->line("   ❌ PROBLÈME PERSISTANT : Karim est encore suggéré comme 'Beau-fils'");
                $this->line("   🔍 Vérifiez la logique de suggestion dans IntelligentSuggestionService");
            } else {
                $this->line("   🤔 RELATION INATTENDUE : {$newSuggestion->suggested_relation_code}");
            }
        } else {
            $this->line("   ⚠️  Aucune nouvelle suggestion générée pour Karim");
            $this->line("   🔍 Vérifiez pourquoi Karim n'est plus suggéré");
        }
        $this->newLine();

        // Afficher toutes les suggestions en attente pour Nadia
        $this->info('4️⃣ TOUTES LES SUGGESTIONS EN ATTENTE POUR NADIA :');
        $allSuggestions = Suggestion::where('user_id', $nadia->id)
            ->where('status', 'pending')
            ->with('suggestedUser')
            ->get();

        if ($allSuggestions->isEmpty()) {
            $this->line("   ⚠️  Aucune suggestion en attente");
        } else {
            foreach ($allSuggestions as $suggestion) {
                $user = $suggestion->suggestedUser;
                $relationName = $suggestion->suggested_relation_name ?: $suggestion->suggested_relation_code;
                $this->line("   • {$user->name} → {$relationName} ({$suggestion->suggested_relation_code})");
            }
        }

        $this->newLine();
        $this->info('🎯 CORRECTION TERMINÉE !');

        return 0;
    }
}
