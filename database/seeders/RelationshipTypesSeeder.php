<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RelationshipTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $relationshipTypes = [
            // Relations directes
            [
                'code' => 'father',
                'name_fr' => 'Père',
                'name_ar' => 'أب',
                'name_en' => 'Father',
                'gender' => 'male',
                'requires_mother_name' => true,
            ],
            [
                'code' => 'mother',
                'name_fr' => 'Mère',
                'name_ar' => 'أم',
                'name_en' => 'Mother',
                'gender' => 'female',
                'requires_mother_name' => false,
            ],
            [
                'code' => 'son',
                'name_fr' => 'Fils',
                'name_ar' => 'ابن',
                'name_en' => 'Son',
                'gender' => 'male',
                'requires_mother_name' => true,
            ],
            [
                'code' => 'daughter',
                'name_fr' => 'Fille',
                'name_ar' => 'ابنة',
                'name_en' => 'Daughter',
                'gender' => 'female',
                'requires_mother_name' => true,
            ],
            [
                'code' => 'brother',
                'name_fr' => 'Frère',
                'name_ar' => 'أخ',
                'name_en' => 'Brother',
                'gender' => 'male',
                'requires_mother_name' => true,
            ],
            [
                'code' => 'sister',
                'name_fr' => 'Sœur',
                'name_ar' => 'أخت',
                'name_en' => 'Sister',
                'gender' => 'female',
                'requires_mother_name' => true,
            ],
            [
                'code' => 'husband',
                'name_fr' => 'Mari',
                'name_ar' => 'زوج',
                'name_en' => 'Husband',
                'gender' => 'male',
                'requires_mother_name' => false,
            ],
            [
                'code' => 'wife',
                'name_fr' => 'Épouse',
                'name_ar' => 'زوجة',
                'name_en' => 'Wife',
                'gender' => 'female',
                'requires_mother_name' => false,
            ],

            // Grands-parents
            [
                'code' => 'grandfather_paternal',
                'name_fr' => 'Grand-père paternel',
                'name_ar' => 'جد من جهة الأب',
                'name_en' => 'Paternal Grandfather',
                'gender' => 'male',
                'requires_mother_name' => false,
            ],
            [
                'code' => 'grandmother_paternal',
                'name_fr' => 'Grand-mère paternelle',
                'name_ar' => 'جدة من جهة الأب',
                'name_en' => 'Paternal Grandmother',
                'gender' => 'female',
                'requires_mother_name' => false,
            ],
            [
                'code' => 'grandfather_maternal',
                'name_fr' => 'Grand-père maternel',
                'name_ar' => 'جد من جهة الأم',
                'name_en' => 'Maternal Grandfather',
                'gender' => 'male',
                'requires_mother_name' => false,
            ],
            [
                'code' => 'grandmother_maternal',
                'name_fr' => 'Grand-mère maternelle',
                'name_ar' => 'جدة من جهة الأم',
                'name_en' => 'Maternal Grandmother',
                'gender' => 'female',
                'requires_mother_name' => false,
            ],
        ];

        foreach ($relationshipTypes as $type) {
            DB::table('relationship_types')->updateOrInsert(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
