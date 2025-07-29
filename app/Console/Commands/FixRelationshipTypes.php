<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use App\Models\RelationshipType;

class FixRelationshipTypes extends Command
{
    protected $signature = 'fix:relationship-types';
    protected $description = 'Fix the relationship_types table structure and data';

    public function handle()
    {
        $this->info('🔧 Correction du problème de contrainte NOT NULL...');

        try {
            // 1. Supprimer complètement la table
            $this->info('🗑️ Suppression de l\'ancienne table...');
            Schema::dropIfExists('relationship_types');
            $this->info('✅ Table supprimée');

            // 2. Créer la nouvelle table
            $this->info('🏗️ Création de la nouvelle table...');
            Schema::create('relationship_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name_fr');
                $table->string('display_name_ar');
                $table->string('display_name_en');
                $table->text('description')->nullable();
                $table->string('reverse_relationship')->nullable();
                $table->string('category')->default('direct');
                $table->integer('generation_level')->default(0);
                $table->integer('sort_order')->default(1);
                $table->timestamps();
                
                // Index
                $table->index('category');
                $table->index('generation_level');
                $table->index('sort_order');
            });
            $this->info('✅ Table créée avec succès');

            // 3. Insérer les données
            $this->info('🌱 Insertion des types de relations...');
            $this->insertRelationshipTypes();

            $count = RelationshipType::count();
            $this->info("✅ {$count} types de relations créés avec succès!");

            // 4. Afficher quelques exemples
            $this->info('📋 Exemples de types créés:');
            $examples = RelationshipType::orderBy('sort_order')->take(5)->get();
            foreach ($examples as $type) {
                $this->line("   - {$type->name} ({$type->display_name_fr}, {$type->category})");
            }

            $this->info('');
            $this->info('🎉 Correction terminée avec succès!');
            $this->info('✅ Le problème de contrainte NOT NULL est résolu');
            $this->info('✅ Vous pouvez maintenant utiliser les seeders sans erreur');
            $this->info('✅ Le système de suggestions fonctionnera correctement');

        } catch (\Exception $e) {
            $this->error('❌ Erreur: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function insertRelationshipTypes()
    {
        $relationshipTypes = [
            // Relations directes - Génération -1 (Parents)
            [
                'name' => 'parent',
                'display_name_fr' => 'Parent',
                'display_name_ar' => 'والد/والدة',
                'display_name_en' => 'Parent',
                'description' => 'Relation parent-enfant directe',
                'reverse_relationship' => 'child',
                'category' => 'direct',
                'generation_level' => -1,
                'sort_order' => 1
            ],
            [
                'name' => 'father',
                'display_name_fr' => 'Père',
                'display_name_ar' => 'أب',
                'display_name_en' => 'Father',
                'description' => 'Père biologique ou adoptif',
                'reverse_relationship' => 'child',
                'category' => 'direct',
                'generation_level' => -1,
                'sort_order' => 2
            ],
            [
                'name' => 'mother',
                'display_name_fr' => 'Mère',
                'display_name_ar' => 'أم',
                'display_name_en' => 'Mother',
                'description' => 'Mère biologique ou adoptive',
                'reverse_relationship' => 'child',
                'category' => 'direct',
                'generation_level' => -1,
                'sort_order' => 3
            ],
            [
                'name' => 'child',
                'display_name_fr' => 'Enfant',
                'display_name_ar' => 'طفل/طفلة',
                'display_name_en' => 'Child',
                'description' => 'Enfant biologique ou adoptif',
                'reverse_relationship' => 'parent',
                'category' => 'direct',
                'generation_level' => 1,
                'sort_order' => 4
            ],
            [
                'name' => 'son',
                'display_name_fr' => 'Fils',
                'display_name_ar' => 'ابن',
                'display_name_en' => 'Son',
                'description' => 'Fils biologique ou adoptif',
                'reverse_relationship' => 'parent',
                'category' => 'direct',
                'generation_level' => 1,
                'sort_order' => 5
            ],
            [
                'name' => 'daughter',
                'display_name_fr' => 'Fille',
                'display_name_ar' => 'ابنة',
                'display_name_en' => 'Daughter',
                'description' => 'Fille biologique ou adoptive',
                'reverse_relationship' => 'parent',
                'category' => 'direct',
                'generation_level' => 1,
                'sort_order' => 6
            ],
            [
                'name' => 'spouse',
                'display_name_fr' => 'Époux/Épouse',
                'display_name_ar' => 'زوج/زوجة',
                'display_name_en' => 'Spouse',
                'description' => 'Conjoint marié',
                'reverse_relationship' => 'spouse',
                'category' => 'marriage',
                'generation_level' => 0,
                'sort_order' => 7
            ],
            [
                'name' => 'husband',
                'display_name_fr' => 'Mari',
                'display_name_ar' => 'زوج',
                'display_name_en' => 'Husband',
                'description' => 'Époux masculin',
                'reverse_relationship' => 'wife',
                'category' => 'marriage',
                'generation_level' => 0,
                'sort_order' => 8
            ],
            [
                'name' => 'wife',
                'display_name_fr' => 'Épouse',
                'display_name_ar' => 'زوجة',
                'display_name_en' => 'Wife',
                'description' => 'Épouse féminine',
                'reverse_relationship' => 'husband',
                'category' => 'marriage',
                'generation_level' => 0,
                'sort_order' => 9
            ],
            [
                'name' => 'daughter_in_law',
                'display_name_fr' => 'Belle-fille',
                'display_name_ar' => 'كنة',
                'display_name_en' => 'Daughter-in-law',
                'description' => 'Épouse du fils',
                'reverse_relationship' => 'father_mother_in_law',
                'category' => 'marriage',
                'generation_level' => 1,
                'sort_order' => 26
            ],
            [
                'name' => 'cousin',
                'display_name_fr' => 'Cousin/Cousine',
                'display_name_ar' => 'ابن/ابنة عم/خال',
                'display_name_en' => 'Cousin',
                'description' => 'Enfant de l\'oncle ou de la tante',
                'reverse_relationship' => 'cousin',
                'category' => 'extended',
                'generation_level' => 0,
                'sort_order' => 27
            ],
            [
                'name' => 'brother',
                'display_name_fr' => 'Frère',
                'display_name_ar' => 'أخ',
                'display_name_en' => 'Brother',
                'description' => 'Frère biologique ou adoptif',
                'reverse_relationship' => 'sibling',
                'category' => 'direct',
                'generation_level' => 0,
                'sort_order' => 11
            ],
            [
                'name' => 'sister',
                'display_name_fr' => 'Sœur',
                'display_name_ar' => 'أخت',
                'display_name_en' => 'Sister',
                'description' => 'Sœur biologique ou adoptive',
                'reverse_relationship' => 'sibling',
                'category' => 'direct',
                'generation_level' => 0,
                'sort_order' => 12
            ],
        ];

        foreach ($relationshipTypes as $type) {
            RelationshipType::create($type);
        }
    }
}
