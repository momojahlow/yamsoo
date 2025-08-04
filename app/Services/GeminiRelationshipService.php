<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiRelationshipService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');
        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $this->baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    /**
     * Analyser la relation probable entre deux utilisateurs en utilisant Gemini AI
     */
    public function analyzeRelationship(User $user1, User $user2, array $familyContext = []): ?array
    {
        try {
            $prompt = $this->buildRelationshipPrompt($user1, $user2, $familyContext);
            
            $response = Http::timeout(30)->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return $this->parseGeminiResponse($result);
            }

            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Gemini Relationship Analysis Error', [
                'error' => $e->getMessage(),
                'user1' => $user1->name,
                'user2' => $user2->name
            ]);
            return null;
        }
    }

    /**
     * Construire le prompt pour l'analyse de relation
     */
    private function buildRelationshipPrompt(User $user1, User $user2, array $familyContext): string
    {
        $user1Profile = $user1->profile;
        $user2Profile = $user2->profile;

        $prompt = "Tu es un expert en analyse des relations familiales marocaines. Analyse la relation probable entre ces deux personnes.\n\n";
        
        $prompt .= "PERSONNE 1:\n";
        $prompt .= "- Nom: {$user1->name}\n";
        $prompt .= "- Email: {$user1->email}\n";
        if ($user1Profile) {
            $prompt .= "- Prénom: {$user1Profile->first_name}\n";
            $prompt .= "- Nom de famille: {$user1Profile->last_name}\n";
            $prompt .= "- Genre: {$user1Profile->gender}\n";
            $prompt .= "- Date de naissance: {$user1Profile->birth_date}\n";
            $prompt .= "- Adresse: {$user1Profile->address}\n";
        }

        $prompt .= "\nPERSONNE 2:\n";
        $prompt .= "- Nom: {$user2->name}\n";
        $prompt .= "- Email: {$user2->email}\n";
        if ($user2Profile) {
            $prompt .= "- Prénom: {$user2Profile->first_name}\n";
            $prompt .= "- Nom de famille: {$user2Profile->last_name}\n";
            $prompt .= "- Genre: {$user2Profile->gender}\n";
            $prompt .= "- Date de naissance: {$user2Profile->birth_date}\n";
            $prompt .= "- Adresse: {$user2Profile->address}\n";
        }

        if (!empty($familyContext)) {
            $prompt .= "\nCONTEXTE FAMILIAL EXISTANT:\n";

            // Traitement spécial pour le contexte structuré
            if (isset($familyContext['scenario'])) {
                $prompt .= "SCÉNARIO: {$familyContext['scenario']}\n";
                if (isset($familyContext['child'])) {
                    $prompt .= "ENFANT: {$familyContext['child']}\n";
                }
                if (isset($familyContext['parent'])) {
                    $prompt .= "PARENT: {$familyContext['parent']}\n";
                }
                if (isset($familyContext['spouse'])) {
                    $prompt .= "CONJOINT: {$familyContext['spouse']}\n";
                }
                if (isset($familyContext['context'])) {
                    $prompt .= "CONTEXTE: {$familyContext['context']}\n";
                }
                if (isset($familyContext['question'])) {
                    $prompt .= "QUESTION: {$familyContext['question']}\n";
                }
            } else {
                // Traitement classique pour les contextes simples
                foreach ($familyContext as $key => $context) {
                    if (is_string($context)) {
                        $prompt .= "- {$context}\n";
                    }
                }
            }
        }

        $prompt .= "\nTYPES DE RELATIONS DISPONIBLES:\n";
        $prompt .= "- father (Père)\n";
        $prompt .= "- mother (Mère)\n";
        $prompt .= "- son (Fils)\n";
        $prompt .= "- daughter (Fille)\n";
        $prompt .= "- brother (Frère)\n";
        $prompt .= "- sister (Sœur)\n";
        $prompt .= "- husband (Mari)\n";
        $prompt .= "- wife (Épouse)\n";
        $prompt .= "- grandfather (Grand-père)\n";
        $prompt .= "- grandmother (Grand-mère)\n";
        $prompt .= "- grandson (Petit-fils)\n";
        $prompt .= "- granddaughter (Petite-fille)\n";
        $prompt .= "- uncle (Oncle)\n";
        $prompt .= "- aunt (Tante)\n";
        $prompt .= "- nephew (Neveu)\n";
        $prompt .= "- niece (Nièce)\n";
        $prompt .= "- father_in_law (Beau-père)\n";
        $prompt .= "- mother_in_law (Belle-mère)\n";
        $prompt .= "- son_in_law (Gendre)\n";
        $prompt .= "- daughter_in_law (Belle-fille)\n";
        $prompt .= "- cousin (Cousin/Cousine)\n";

        $prompt .= "\nINSTRUCTIONS IMPORTANTES:\n";
        $prompt .= "1. Analyse les noms de famille, prénoms, âges, adresses\n";
        $prompt .= "2. Considère les conventions de nommage marocaines\n";
        $prompt .= "3. Prends en compte les différences d'âge\n";
        $prompt .= "4. Utilise le contexte familial existant\n";
        $prompt .= "5. RESPECTE ABSOLUMENT LES GENRES:\n";
        $prompt .= "   - Les hommes ne peuvent être que: father, son, brother, husband, grandfather, grandson, uncle, nephew, father_in_law, son_in_law\n";
        $prompt .= "   - Les femmes ne peuvent être que: mother, daughter, sister, wife, grandmother, granddaughter, aunt, niece, mother_in_law, daughter_in_law\n";
        $prompt .= "6. Pour les relations parent/conjoint:\n";
        $prompt .= "   - Si c'est une famille principale (couple + enfants), le conjoint = parent (mother/father)\n";
        $prompt .= "   - Si c'est une famille recomposée (enfants d'un mariage précédent), le conjoint = beau-parent (stepmother/stepfather)\n";
        $prompt .= "7. Réponds UNIQUEMENT au format JSON suivant:\n\n";
        
        $prompt .= "{\n";
        $prompt .= '  "relation_code": "code_de_relation",'."\n";
        $prompt .= '  "relation_name": "Nom en français",'."\n";
        $prompt .= '  "confidence": 0.85,'."\n";
        $prompt .= '  "reasoning": "Explication de ton analyse"'."\n";
        $prompt .= "}\n\n";
        
        $prompt .= "Si aucune relation familiale n'est probable, utilise:\n";
        $prompt .= '{"relation_code": null, "relation_name": null, "confidence": 0.0, "reasoning": "Aucune relation familiale détectée"}';

        return $prompt;
    }

    /**
     * Parser la réponse de Gemini
     */
    private function parseGeminiResponse(array $response): ?array
    {
        try {
            if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                return null;
            }

            $text = $response['candidates'][0]['content']['parts'][0]['text'];
            
            // Extraire le JSON de la réponse
            if (preg_match('/\{[^}]*\}/', $text, $matches)) {
                $jsonData = json_decode($matches[0], true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'relation_code' => $jsonData['relation_code'] ?? null,
                        'relation_name' => $jsonData['relation_name'] ?? null,
                        'confidence' => $jsonData['confidence'] ?? 0.0,
                        'reasoning' => $jsonData['reasoning'] ?? '',
                        'source' => 'gemini_ai'
                    ];
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Gemini Response Parsing Error', [
                'error' => $e->getMessage(),
                'response' => $response
            ]);
            return null;
        }
    }

    /**
     * Analyser plusieurs relations potentielles pour un utilisateur
     */
    public function analyzePotentialRelations(User $user, array $potentialRelatives): array
    {
        $results = [];
        
        // Construire le contexte familial existant
        $familyContext = $this->buildFamilyContext($user);
        
        foreach ($potentialRelatives as $relative) {
            // S'assurer que $relative est un objet User
            if (!($relative instanceof User)) {
                continue;
            }

            $analysis = $this->analyzeRelationship($user, $relative, $familyContext);
            
            if ($analysis && $analysis['confidence'] > 0.6) {
                $results[] = [
                    'user' => $relative,
                    'analysis' => $analysis
                ];
            }
        }

        // Trier par confiance décroissante
        usort($results, function($a, $b) {
            return $b['analysis']['confidence'] <=> $a['analysis']['confidence'];
        });

        return $results;
    }

    /**
     * Construire le contexte familial existant pour un utilisateur
     */
    private function buildFamilyContext(User $user): array
    {
        $context = [];
        
        $relations = $user->familyRelations()->with(['relatedUser.profile', 'relationshipType'])->get();
        
        foreach ($relations as $relation) {
            $context[] = "{$user->name} est {$relation->relationshipType->display_name_fr} de {$relation->relatedUser->name}";
        }

        return $context;
    }
}
