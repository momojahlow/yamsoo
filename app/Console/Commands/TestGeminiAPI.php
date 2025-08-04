<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestGeminiAPI extends Command
{
    protected $signature = 'test:gemini-api';
    protected $description = 'Test direct de l\'API Gemini';

    public function handle()
    {
        $this->info('🤖 TEST DIRECT DE L\'API GEMINI');
        $this->info('================================');

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            $this->error('❌ GEMINI_API_KEY non configuré');
            return;
        }

        $this->info("✅ API Key: " . substr($apiKey, 0, 10) . "...");

        // Test simple
        $this->testSimpleRequest($apiKey);

        // Test avec prompt de relation
        $this->testRelationshipPrompt($apiKey);
    }

    private function testSimpleRequest($apiKey)
    {
        $this->info("\n🔍 TEST 1: Requête simple");
        
        try {
            $model = config('services.gemini.model', 'gemini-1.5-flash');
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => 'Dis bonjour en français']
                            ]
                        ]
                    ]
                ]
            );

            $this->info("Status: " . $response->status());
            
            if ($response->successful()) {
                $result = $response->json();
                $this->info("✅ Réponse reçue:");
                
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    $text = $result['candidates'][0]['content']['parts'][0]['text'];
                    $this->info("   Texte: " . $text);
                } else {
                    $this->warn("⚠️ Structure de réponse inattendue:");
                    $this->info(json_encode($result, JSON_PRETTY_PRINT));
                }
            } else {
                $this->error("❌ Erreur API:");
                $this->error("   Status: " . $response->status());
                $this->error("   Body: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
        }
    }

    private function testRelationshipPrompt($apiKey)
    {
        $this->info("\n🔍 TEST 2: Prompt de relation familiale");
        
        $prompt = "Tu es un expert en analyse des relations familiales marocaines. Analyse la relation probable entre ces deux personnes.

PERSONNE 1:
- Nom: Ahmed Benali
- Email: ahmed.benali@example.com
- Prénom: Ahmed
- Nom de famille: Benali
- Genre: male
- Date de naissance: 1980-05-15
- Adresse: Casablanca, Maroc

PERSONNE 2:
- Nom: Fatima Zahra
- Email: fatima.zahra@example.com
- Prénom: Fatima
- Nom de famille: Zahra
- Genre: female
- Date de naissance: 1985-08-20
- Adresse: Casablanca, Maroc

TYPES DE RELATIONS DISPONIBLES:
- father (Père)
- mother (Mère)
- son (Fils)
- daughter (Fille)
- brother (Frère)
- sister (Sœur)
- husband (Mari)
- wife (Épouse)

INSTRUCTIONS:
1. Analyse les noms de famille, prénoms, âges, adresses
2. Considère les conventions de nommage marocaines
3. Prends en compte les différences d'âge
4. Réponds UNIQUEMENT au format JSON suivant:

{
  \"relation_code\": \"code_de_relation\",
  \"relation_name\": \"Nom en français\",
  \"confidence\": 0.85,
  \"reasoning\": \"Explication de ton analyse\"
}

Si aucune relation familiale n'est probable, utilise:
{\"relation_code\": null, \"relation_name\": null, \"confidence\": 0.0, \"reasoning\": \"Aucune relation familiale détectée\"}";

        try {
            $model = config('services.gemini.model', 'gemini-1.5-flash');
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            );

            $this->info("Status: " . $response->status());
            
            if ($response->successful()) {
                $result = $response->json();
                $this->info("✅ Réponse reçue:");
                
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    $text = $result['candidates'][0]['content']['parts'][0]['text'];
                    $this->info("   Texte brut: " . $text);
                    
                    // Essayer d'extraire le JSON
                    if (preg_match('/\{[^}]*\}/', $text, $matches)) {
                        $jsonData = json_decode($matches[0], true);
                        
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $this->info("✅ JSON extrait:");
                            $this->info("   Relation: " . ($jsonData['relation_code'] ?? 'null'));
                            $this->info("   Nom: " . ($jsonData['relation_name'] ?? 'null'));
                            $this->info("   Confiance: " . ($jsonData['confidence'] ?? 0));
                            $this->info("   Raisonnement: " . ($jsonData['reasoning'] ?? ''));
                        } else {
                            $this->warn("⚠️ JSON invalide dans la réponse");
                        }
                    } else {
                        $this->warn("⚠️ Aucun JSON trouvé dans la réponse");
                    }
                } else {
                    $this->warn("⚠️ Structure de réponse inattendue:");
                    $this->info(json_encode($result, JSON_PRETTY_PRINT));
                }
            } else {
                $this->error("❌ Erreur API:");
                $this->error("   Status: " . $response->status());
                $this->error("   Body: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
        }
    }
}
