<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\RelationshipType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter les types de relations par alliance (belle-famille)
        $inLawRelationships = [
            // Relations par alliance directes
            [
                'code' => 'father_in_law',
                'name_fr' => 'Beau-père',
                'name_ar' => 'حمو',
                'name_en' => 'Father-in-law',
                'gender' => 'male',
                'requires_mother_name' => false,
            ],
            [
                'code' => 'mother_in_law',
                'name_fr' => 'Belle-mère',
                'name_ar' => 'حماة',
                'name_en' => 'Mother-in-law',
                'gender' => 'female',
                'requires_mother_name' => false,
            ],
            [
                'code' => 'brother_in_law',
                'name_fr' => 'Beau-frère',
                'name_ar' => 'صهر',
                'name_en' => 'Brother-in-law',
                'gender' => 'male',
                'requires_mother_name' => false,
            ],
            [
                'code' => 'sister_in_law',
                'name_fr' => 'Belle-sœur',
                'name_ar' => 'سلفة',
                'name_en' => 'Sister-in-law',
                'gender' => 'female',
                'requires_mother_name' => false,
            ],
            [
                'code' => 'stepson',
                'name_fr' => 'Beau-fils',
                'name_ar' => 'ربيب',
                'name_en' => 'Stepson',
                'gender' => 'male',
                'requires_mother_name' => false,
            ],
            [
                'code' => 'stepdaughter',
                'name_fr' => 'Belle-fille',
                'name_ar' => 'ربيبة',
                'name_en' => 'Stepdaughter',
                'gender' => 'female',
                'requires_mother_name' => false,
            ],
        ];

        foreach ($inLawRelationships as $relationship) {
            RelationshipType::updateOrCreate(
                ['code' => $relationship['code']],
                $relationship
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $codes = [
            'father_in_law', 'mother_in_law', 'brother_in_law', 'sister_in_law',
            'stepson', 'stepdaughter'
        ];

        RelationshipType::whereIn('code', $codes)->delete();
    }
};
