<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\RelationshipType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter les relations step-family (belle-famille par remariage)
        $stepRelations = [
            [
                'name' => 'stepfather',
                'display_name_fr' => 'Beau-père',
                'display_name_ar' => 'زوج الأم',
                'display_name_en' => 'Stepfather',
                'category' => 'step_family'
            ],
            [
                'name' => 'stepmother',
                'display_name_fr' => 'Belle-mère',
                'display_name_ar' => 'زوجة الأب',
                'display_name_en' => 'Stepmother',
                'category' => 'step_family'
            ],
            [
                'name' => 'stepson',
                'display_name_fr' => 'Beau-fils',
                'display_name_ar' => 'ابن الزوج/الزوجة',
                'display_name_en' => 'Stepson',
                'category' => 'step_family'
            ],
            [
                'name' => 'stepdaughter',
                'display_name_fr' => 'Belle-fille',
                'display_name_ar' => 'ابنة الزوج/الزوجة',
                'display_name_en' => 'Stepdaughter',
                'category' => 'step_family'
            ]
        ];

        foreach ($stepRelations as $relation) {
            RelationshipType::updateOrCreate(
                ['name' => $relation['name']],
                $relation
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        RelationshipType::whereIn('name', ['stepfather', 'stepmother', 'stepson', 'stepdaughter'])->delete();
    }
};
