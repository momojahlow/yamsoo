<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Profile;
use App\Models\RelationshipType;

class CleanDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nettoyer toutes les relations et suggestions existantes
        // Utiliser delete au lieu de truncate pour éviter les problèmes de clés étrangères
        DB::table('suggestions')->delete();
        DB::table('family_relationships')->delete();
        DB::table('relationship_requests')->delete();
        DB::table('notifications')->delete();

        $this->command->info('🧹 Base de données nettoyée (relations et suggestions supprimées)');

        // Créer les utilisateurs de base sans aucune relation
        $this->createUsers();

        // Créer les types de relations
        $this->createRelationshipTypes();

        $this->command->info('✅ Base de données réinitialisée avec succès');
        $this->command->info('👥 Utilisateurs créés sans aucune relation');
        $this->command->info('🔗 Types de relations configurés');
    }

    private function createUsers(): void
    {
        $users = [
            [
                'name' => 'Ahmed Benali',
                'email' => 'ahmed.benali@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'first_name' => 'Ahmed',
                    'last_name' => 'Benali',
                    'gender' => 'male',
                    'birth_date' => '1985-03-15',
                    'phone' => '+212 6 12 34 56 78'
                ]
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima.zahra@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'first_name' => 'Fatima',
                    'last_name' => 'Zahra',
                    'gender' => 'female',
                    'birth_date' => '1990-07-22',
                    'phone' => '+212 6 23 45 67 89'
                ]
            ],
            [
                'name' => 'Mohammed Alami',
                'email' => 'mohammed.alami@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'first_name' => 'Mohammed',
                    'last_name' => 'Alami',
                    'gender' => 'male',
                    'birth_date' => '1988-11-10',
                    'phone' => '+212 6 34 56 78 90'
                ]
            ],
            [
                'name' => 'Youssef Bennani',
                'email' => 'youssef.bennani@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'first_name' => 'Youssef',
                    'last_name' => 'Bennani',
                    'gender' => 'male',
                    'birth_date' => '1992-05-18',
                    'phone' => '+212 6 45 67 89 01'
                ]
            ],
            [
                'name' => 'Aicha Idrissi',
                'email' => 'aicha.idrissi@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'first_name' => 'Aicha',
                    'last_name' => 'Idrissi',
                    'gender' => 'female',
                    'birth_date' => '1995-09-03',
                    'phone' => '+212 6 56 78 90 12'
                ]
            ]
        ];

        foreach ($users as $userData) {
            $profileData = $userData['profile'];
            unset($userData['profile']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            Profile::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($profileData, ['user_id' => $user->id])
            );

            $this->command->info("👤 Utilisateur mis à jour : {$user->name}");
        }
    }

    private function createRelationshipTypes(): void
    {
        $relationshipTypes = [
            // Relations directes
            ['code' => 'father', 'name_fr' => 'Père', 'name_en' => 'Father', 'name_ar' => 'أب', 'gender' => 'male'],
            ['code' => 'mother', 'name_fr' => 'Mère', 'name_en' => 'Mother', 'name_ar' => 'أم', 'gender' => 'female'],
            ['code' => 'son', 'name_fr' => 'Fils', 'name_en' => 'Son', 'name_ar' => 'ابن', 'gender' => 'male'],
            ['code' => 'daughter', 'name_fr' => 'Fille', 'name_en' => 'Daughter', 'name_ar' => 'ابنة', 'gender' => 'female'],
            ['code' => 'brother', 'name_fr' => 'Frère', 'name_en' => 'Brother', 'name_ar' => 'أخ', 'gender' => 'male'],
            ['code' => 'sister', 'name_fr' => 'Sœur', 'name_en' => 'Sister', 'name_ar' => 'أخت', 'gender' => 'female'],
            ['code' => 'husband', 'name_fr' => 'Mari', 'name_en' => 'Husband', 'name_ar' => 'زوج', 'gender' => 'male'],
            ['code' => 'wife', 'name_fr' => 'Épouse', 'name_en' => 'Wife', 'name_ar' => 'زوجة', 'gender' => 'female'],

            // Grands-parents
            ['code' => 'grandfather_paternal', 'name_fr' => 'Grand-père paternel', 'name_en' => 'Paternal Grandfather', 'name_ar' => 'جد من جهة الأب', 'gender' => 'male'],
            ['code' => 'grandmother_paternal', 'name_fr' => 'Grand-mère paternelle', 'name_en' => 'Paternal Grandmother', 'name_ar' => 'جدة من جهة الأب', 'gender' => 'female'],
            ['code' => 'grandfather_maternal', 'name_fr' => 'Grand-père maternel', 'name_en' => 'Maternal Grandfather', 'name_ar' => 'جد من جهة الأم', 'gender' => 'male'],
            ['code' => 'grandmother_maternal', 'name_fr' => 'Grand-mère maternelle', 'name_en' => 'Maternal Grandmother', 'name_ar' => 'جدة من جهة الأم', 'gender' => 'female'],

            // Oncles et tantes
            ['code' => 'uncle_paternal', 'name_fr' => 'Oncle paternel', 'name_en' => 'Paternal Uncle', 'name_ar' => 'عم', 'gender' => 'male'],
            ['code' => 'aunt_paternal', 'name_fr' => 'Tante paternelle', 'name_en' => 'Paternal Aunt', 'name_ar' => 'عمة', 'gender' => 'female'],
            ['code' => 'uncle_maternal', 'name_fr' => 'Oncle maternel', 'name_en' => 'Maternal Uncle', 'name_ar' => 'خال', 'gender' => 'male'],
            ['code' => 'aunt_maternal', 'name_fr' => 'Tante maternelle', 'name_en' => 'Maternal Aunt', 'name_ar' => 'خالة', 'gender' => 'female'],

            // Petits-enfants
            ['code' => 'grandson', 'name_fr' => 'Petit-fils', 'name_en' => 'Grandson', 'name_ar' => 'حفيد', 'gender' => 'male'],
            ['code' => 'granddaughter', 'name_fr' => 'Petite-fille', 'name_en' => 'Granddaughter', 'name_ar' => 'حفيدة', 'gender' => 'female'],

            // Neveux et nièces
            ['code' => 'nephew', 'name_fr' => 'Neveu', 'name_en' => 'Nephew', 'name_ar' => 'ابن أخ', 'gender' => 'male'],
            ['code' => 'niece', 'name_fr' => 'Nièce', 'name_en' => 'Niece', 'name_ar' => 'ابنة أخ', 'gender' => 'female'],

            // Cousins
            ['code' => 'cousin_paternal_m', 'name_fr' => 'Cousin paternel', 'name_en' => 'Paternal Male Cousin', 'name_ar' => 'ابن عم', 'gender' => 'male'],
            ['code' => 'cousin_paternal_f', 'name_fr' => 'Cousine paternelle', 'name_en' => 'Paternal Female Cousin', 'name_ar' => 'بنت عم', 'gender' => 'female'],
            ['code' => 'cousin_maternal_m', 'name_fr' => 'Cousin maternel', 'name_en' => 'Maternal Male Cousin', 'name_ar' => 'ابن خال', 'gender' => 'male'],
            ['code' => 'cousin_maternal_f', 'name_fr' => 'Cousine maternelle', 'name_en' => 'Maternal Female Cousin', 'name_ar' => 'بنت خال', 'gender' => 'female'],

            // Beaux-parents et belle-famille
            ['code' => 'father_in_law', 'name_fr' => 'Beau-père', 'name_en' => 'Father-in-law', 'name_ar' => 'حمو', 'gender' => 'male'],
            ['code' => 'mother_in_law', 'name_fr' => 'Belle-mère', 'name_en' => 'Mother-in-law', 'name_ar' => 'حماة', 'gender' => 'female'],
            ['code' => 'son_in_law', 'name_fr' => 'Gendre', 'name_en' => 'Son-in-law', 'name_ar' => 'صهر', 'gender' => 'male'],
            ['code' => 'daughter_in_law', 'name_fr' => 'Belle-fille', 'name_en' => 'Daughter-in-law', 'name_ar' => 'كنة', 'gender' => 'female'],
            ['code' => 'brother_in_law', 'name_fr' => 'Beau-frère', 'name_en' => 'Brother-in-law', 'name_ar' => 'صهر', 'gender' => 'male'],
            ['code' => 'sister_in_law', 'name_fr' => 'Belle-sœur', 'name_en' => 'Sister-in-law', 'name_ar' => 'سلفة', 'gender' => 'female'],

            // Arrière-grands-parents
            ['code' => 'great_grandfather_paternal', 'name_fr' => 'Arrière-grand-père paternel', 'name_en' => 'Paternal Great-grandfather', 'name_ar' => 'جد الجد من جهة الأب', 'gender' => 'male'],
            ['code' => 'great_grandmother_paternal', 'name_fr' => 'Arrière-grand-mère paternelle', 'name_en' => 'Paternal Great-grandmother', 'name_ar' => 'جدة الجد من جهة الأب', 'gender' => 'female'],
            ['code' => 'great_grandfather_maternal', 'name_fr' => 'Arrière-grand-père maternel', 'name_en' => 'Maternal Great-grandfather', 'name_ar' => 'جد الجد من جهة الأم', 'gender' => 'male'],
            ['code' => 'great_grandmother_maternal', 'name_fr' => 'Arrière-grand-mère maternelle', 'name_en' => 'Maternal Great-grandmother', 'name_ar' => 'جدة الجد من جهة الأم', 'gender' => 'female'],

            // Arrière-petits-enfants
            ['code' => 'great_grandson', 'name_fr' => 'Arrière-petit-fils', 'name_en' => 'Great-grandson', 'name_ar' => 'حفيد الحفيد', 'gender' => 'male'],
            ['code' => 'great_granddaughter', 'name_fr' => 'Arrière-petite-fille', 'name_en' => 'Great-granddaughter', 'name_ar' => 'حفيدة الحفيد', 'gender' => 'female'],
        ];

        foreach ($relationshipTypes as $type) {
            RelationshipType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }

        $this->command->info("🔗 Types de relations créés : " . count($relationshipTypes));
    }
}
