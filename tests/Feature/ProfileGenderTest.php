<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileGenderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que les profils sont créés avec les bons genres
     */
    public function test_profile_gender_creation(): void
    {
        echo "\n=== TEST CRÉATION PROFILS AVEC GENRES ===\n";

        // Test 1: Création manuelle d'un profil masculin
        $maleUser = User::factory()->create(['name' => 'Ahmed Test']);
        $maleProfile = $maleUser->profile()->create([
            'gender' => 'male',
            'bio' => 'Test bio',
            'birth_date' => now()->subYears(30),
            'language' => 'fr',
        ]);

        $this->assertEquals('male', $maleProfile->gender, "Genre masculin incorrect");
        echo "✅ Profil masculin créé manuellement: {$maleProfile->gender}\n";

        // Test 2: Création manuelle d'un profil féminin
        $femaleUser = User::factory()->create(['name' => 'Fatima Test']);
        $femaleProfile = $femaleUser->profile()->create([
            'gender' => 'female',
            'bio' => 'Test bio',
            'birth_date' => now()->subYears(25),
            'language' => 'fr',
        ]);

        $this->assertEquals('female', $femaleProfile->gender, "Genre féminin incorrect");
        echo "✅ Profil féminin créé manuellement: {$femaleProfile->gender}\n";

        // Test 3: Utilisation du factory avec genre spécifique
        $leila = User::factory()->withProfile('female')->create(['name' => 'Leila Mansouri']);
        $leila->refresh();
        $this->assertNotNull($leila->profile, "Profil de Leila non créé");
        echo "✅ Leila créée avec factory\n";

        echo "\n🎉 TOUS LES PROFILS CRÉÉS CORRECTEMENT\n";
    }
}
