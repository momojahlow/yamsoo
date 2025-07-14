<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $relationshipTypes = [
            [
                'code' => 'father',
                'name_fr' => 'Père',
                'name_ar' => 'أب',
                'name_en' => 'Father',
                'gender' => 'male',
                'requires_mother_name' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'mother',
                'name_fr' => 'Mère',
                'name_ar' => 'أم',
                'name_en' => 'Mother',
                'gender' => 'female',
                'requires_mother_name' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'son',
                'name_fr' => 'Fils',
                'name_ar' => 'ابن',
                'name_en' => 'Son',
                'gender' => 'male',
                'requires_mother_name' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'daughter',
                'name_fr' => 'Fille',
                'name_ar' => 'ابنة',
                'name_en' => 'Daughter',
                'gender' => 'female',
                'requires_mother_name' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'brother',
                'name_fr' => 'Frère',
                'name_ar' => 'أخ',
                'name_en' => 'Brother',
                'gender' => 'male',
                'requires_mother_name' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'sister',
                'name_fr' => 'Sœur',
                'name_ar' => 'أخت',
                'name_en' => 'Sister',
                'gender' => 'female',
                'requires_mother_name' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'husband',
                'name_fr' => 'Mari',
                'name_ar' => 'زوج',
                'name_en' => 'Husband',
                'gender' => 'male',
                'requires_mother_name' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'wife',
                'name_fr' => 'Épouse',
                'name_ar' => 'زوجة',
                'name_en' => 'Wife',
                'gender' => 'female',
                'requires_mother_name' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($relationshipTypes as $type) {
            DB::table('relationship_types')->updateOrInsert(
                ['code' => $type['code']],
                $type
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('relationship_types')->whereIn('code', [
            'father', 'mother', 'son', 'daughter', 'brother', 'sister', 'husband', 'wife'
        ])->delete();
    }
};
