<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Suggestion;
use App\Services\SuggestionService;

class SuggestionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Génération de suggestions de test...');

        // Récupérer tous les utilisateurs
        $users = User::all();
        
        if ($users->count() < 2) {
            $this->command->error('Il faut au moins 2 utilisateurs pour générer des suggestions.');
            return;
        }

        // Nettoyer les anciennes suggestions
        Suggestion::truncate();

        // Générer des suggestions pour chaque utilisateur
        $suggestionService = app(SuggestionService::class);
        $totalSuggestions = 0;

        foreach ($users as $user) {
            try {
                // Générer des suggestions pour cet utilisateur
                $suggestionService->generateSuggestions($user);
                
                $userSuggestions = Suggestion::where('user_id', $user->id)->count();
                $totalSuggestions += $userSuggestions;
                
                $this->command->info("- {$user->name}: {$userSuggestions} suggestions générées");
                
            } catch (\Exception $e) {
                $this->command->warn("Erreur pour {$user->name}: " . $e->getMessage());
            }
        }

        // Si aucune suggestion automatique, créer des suggestions manuelles
        if ($totalSuggestions === 0) {
            $this->createManualSuggestions($users);
        }

        $this->command->info("✅ Total: {$totalSuggestions} suggestions créées");
    }

    /**
     * Créer des suggestions manuelles si le service automatique ne fonctionne pas
     */
    private function createManualSuggestions($users): void
    {
        $this->command->info('Création de suggestions manuelles...');

        $suggestions = [];
        $now = now();

        // Créer des suggestions entre les utilisateurs
        foreach ($users as $user) {
            $otherUsers = $users->where('id', '!=', $user->id)->take(3);
            
            foreach ($otherUsers as $suggestedUser) {
                $suggestions[] = [
                    'user_id' => $user->id,
                    'suggested_user_id' => $suggestedUser->id,
                    'suggested_relation_code' => $this->getRandomRelationCode(),
                    'suggested_relation_name' => $this->getRelationName($this->getRandomRelationCode()),
                    'reason' => 'Suggestion générée automatiquement pour les tests',
                    'type' => 'automatic',
                    'confidence_score' => rand(60, 95),
                    'status' => 'pending',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insérer en batch
        if (!empty($suggestions)) {
            Suggestion::insert($suggestions);
            $this->command->info("✅ " . count($suggestions) . " suggestions manuelles créées");
        }
    }

    /**
     * Obtenir un code de relation aléatoire
     */
    private function getRandomRelationCode(): string
    {
        $relations = [
            'father', 'mother', 'son', 'daughter', 
            'brother', 'sister', 'husband', 'wife',
            'grandfather', 'grandmother', 'uncle', 'aunt',
            'nephew', 'niece', 'cousin'
        ];

        return $relations[array_rand($relations)];
    }

    /**
     * Obtenir le nom de la relation
     */
    private function getRelationName(string $code): string
    {
        $names = [
            'father' => 'Père',
            'mother' => 'Mère',
            'son' => 'Fils',
            'daughter' => 'Fille',
            'brother' => 'Frère',
            'sister' => 'Sœur',
            'husband' => 'Mari',
            'wife' => 'Épouse',
            'grandfather' => 'Grand-père',
            'grandmother' => 'Grand-mère',
            'uncle' => 'Oncle',
            'aunt' => 'Tante',
            'nephew' => 'Neveu',
            'niece' => 'Nièce',
            'cousin' => 'Cousin/Cousine',
        ];

        return $names[$code] ?? ucfirst($code);
    }
}
