<?php

namespace Database\Seeders;

use App\Models\RelationshipType;
use Illuminate\Database\Seeder;

class ComprehensiveRelationshipTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vider la table avant de la remplir
        RelationshipType::truncate();

        // Structure optimisée avec factory pattern
        $relationshipGroups = $this->getRelationshipGroups();
        $allRelationships = [];
        $sortOrder = 1;

        foreach ($relationshipGroups as $group) {
            foreach ($group['relationships'] as $relationship) {
                $allRelationships[] = array_merge([
                    'category' => $group['category'],
                    'generation_level' => $group['generation_level'],
                    'sort_order' => $sortOrder++
                ], $relationship);
            }
        }

        // Insertion en batch pour de meilleures performances
        RelationshipType::insert($allRelationships);

        $this->command->info('✅ Types de relations créés avec succès (' . count($allRelationships) . ' types)');
    }

    /**
     * Structure optimisée des relations par groupes
     */
    private function getRelationshipGroups(): array
    {
        return [
            // Relations directes - Parents (-1)
            [
                'category' => 'direct',
                'generation_level' => -1,
                'relationships' => [
                    $this->createRelation('parent', 'Parent', 'والد/والدة', 'Parent', 'Relation parent-enfant directe'),
                    $this->createRelation('father', 'Père', 'أب', 'Father', 'Père biologique ou adoptif'),
                    $this->createRelation('mother', 'Mère', 'أم', 'Mother', 'Mère biologique ou adoptive'),
                ]
            ],

            // Relations directes - Enfants (+1)
            [
                'category' => 'direct',
                'generation_level' => 1,
                'relationships' => [
                    $this->createRelation('child', 'Enfant', 'طفل/طفلة', 'Child', 'Enfant biologique ou adoptif'),
                    $this->createRelation('son', 'Fils', 'ابن', 'Son', 'Fils biologique ou adoptif'),
                    $this->createRelation('daughter', 'Fille', 'ابنة', 'Daughter', 'Fille biologique ou adoptive'),
                ]
            ],

            // Relations de mariage (0)
            [
                'category' => 'marriage',
                'generation_level' => 0,
                'relationships' => [
                    $this->createRelation('spouse', 'Époux/Épouse', 'زوج/زوجة', 'Spouse', 'Conjoint marié'),
                    $this->createRelation('husband', 'Mari', 'زوج', 'Husband', 'Époux masculin'),
                    $this->createRelation('wife', 'Épouse', 'زوجة', 'Wife', 'Épouse féminine'),
                ]
            ],

            // Relations directes - Fratrie (0)
            [
                'category' => 'direct',
                'generation_level' => 0,
                'relationships' => [
                    $this->createRelation('sibling', 'Frère/Sœur', 'أخ/أخت', 'Sibling', 'Frère ou sœur'),
                    $this->createRelation('brother', 'Frère', 'أخ', 'Brother', 'Frère biologique ou adoptif'),
                    $this->createRelation('sister', 'Sœur', 'أخت', 'Sister', 'Sœur biologique ou adoptive'),
                ]
            ],

            // Relations étendues - Grands-parents (-2)
            [
                'category' => 'extended',
                'generation_level' => -2,
                'relationships' => [
                    $this->createRelation('grandparent', 'Grand-parent', 'جد/جدة', 'Grandparent', 'Grand-père ou grand-mère'),
                    $this->createRelation('grandfather', 'Grand-père', 'جد', 'Grandfather', 'Père du père ou de la mère'),
                    $this->createRelation('grandmother', 'Grand-mère', 'جدة', 'Grandmother', 'Mère du père ou de la mère'),
                ]
            ],

            // Relations étendues - Petits-enfants (+2)
            [
                'category' => 'extended',
                'generation_level' => 2,
                'relationships' => [
                    $this->createRelation('grandchild', 'Petit-enfant', 'حفيد/حفيدة', 'Grandchild', 'Enfant de son enfant'),
                    $this->createRelation('grandson', 'Petit-fils', 'حفيد', 'Grandson', 'Fils de son enfant'),
                    $this->createRelation('granddaughter', 'Petite-fille', 'حفيدة', 'Granddaughter', 'Fille de son enfant'),
                ]
            ],

            // Relations étendues - Oncles/Tantes (-1)
            [
                'category' => 'extended',
                'generation_level' => -1,
                'relationships' => [
                    $this->createRelation('uncle', 'Oncle', 'عم/خال', 'Uncle', 'Frère du père ou de la mère'),
                    $this->createRelation('aunt', 'Tante', 'عمة/خالة', 'Aunt', 'Sœur du père ou de la mère'),
                ]
            ],

            // Relations étendues - Neveux/Nièces (+1)
            [
                'category' => 'extended',
                'generation_level' => 1,
                'relationships' => [
                    $this->createRelation('nephew', 'Neveu', 'ابن أخ/أخت', 'Nephew', 'Fils du frère ou de la sœur'),
                    $this->createRelation('niece', 'Nièce', 'ابنة أخ/أخت', 'Niece', 'Fille du frère ou de la sœur'),
                ]
            ],

            // Relations par alliance - Parents (-1)
            [
                'category' => 'marriage',
                'generation_level' => -1,
                'relationships' => [
                    $this->createRelation('father_in_law', 'Beau-père', 'حمو', 'Father-in-law', 'Père du conjoint'),
                    $this->createRelation('mother_in_law', 'Belle-mère', 'حماة', 'Mother-in-law', 'Mère du conjoint'),
                ]
            ],

            // Relations par alliance - Enfants (+1)
            [
                'category' => 'marriage',
                'generation_level' => 1,
                'relationships' => [
                    $this->createRelation('son_in_law', 'Gendre', 'صهر', 'Son-in-law', 'Mari de la fille'),
                    $this->createRelation('daughter_in_law', 'Belle-fille', 'كنة', 'Daughter-in-law', 'Épouse du fils'),
                ]
            ],

            // Relations par alliance - Fratrie (0)
            [
                'category' => 'marriage',
                'generation_level' => 0,
                'relationships' => [
                    $this->createRelation('brother_in_law', 'Beau-frère', 'صهر', 'Brother-in-law', 'Frère du conjoint ou mari de la sœur'),
                    $this->createRelation('sister_in_law', 'Belle-sœur', 'سلفة', 'Sister-in-law', 'Sœur du conjoint ou épouse du frère'),
                ]
            ],

            // Relations étendues - Cousins (0)
            [
                'category' => 'extended',
                'generation_level' => 0,
                'relationships' => [
                    $this->createRelation('cousin', 'Cousin/Cousine', 'ابن/ابنة عم/خال', 'Cousin', 'Enfant de l\'oncle ou de la tante'),
                ]
            ],

            // Relations d'adoption
            [
                'category' => 'adoption',
                'generation_level' => -1,
                'relationships' => [
                    $this->createRelation('adoptive_parent', 'Parent adoptif', 'والد/والدة بالتبني', 'Adoptive parent', 'Parent par adoption légale'),
                ]
            ],
            [
                'category' => 'adoption',
                'generation_level' => 1,
                'relationships' => [
                    $this->createRelation('adopted_child', 'Enfant adopté', 'طفل/طفلة بالتبني', 'Adopted child', 'Enfant par adoption légale'),
                ]
            ],

            // Relation générique
            [
                'category' => 'extended',
                'generation_level' => 0,
                'relationships' => [
                    $this->createRelation('family_member', 'Membre de la famille', 'فرد من العائلة', 'Family member', 'Membre de la famille (relation non spécifiée)'),
                ]
            ]
        ];
    }

    /**
     * Factory method pour créer une relation (optimisé sans reverse_relationship)
     */
    private function createRelation(string $name, string $fr, string $ar, string $en, string $description): array
    {
        return [
            'name' => $name,
            'display_name_fr' => $fr,
            'display_name_ar' => $ar,
            'display_name_en' => $en,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
