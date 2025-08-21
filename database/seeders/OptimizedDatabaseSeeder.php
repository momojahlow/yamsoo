<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptimizedDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with optimized performance.
     */
    public function run(): void
    {
        $this->command->info('🚀 Démarrage du seeding optimisé...');

        // Désactiver les contraintes de clés étrangères pour de meilleures performances
        Schema::disableForeignKeyConstraints();

        // Désactiver les événements Eloquent pour accélérer les insertions (compatible SQLite/MySQL)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET foreign_key_checks=0');
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');
        }

        try {
            $this->seedCoreData();
            $this->seedTestData();

            // Afficher les statistiques de performance
            $this->showPerformanceStats();

            $this->command->info('✅ Seeding optimisé terminé avec succès !');

        } catch (\Exception $e) {
            $this->command->error('❌ Erreur lors du seeding : ' . $e->getMessage());
            throw $e;
        } finally {
            // Réactiver les contraintes (compatible SQLite/MySQL)
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET foreign_key_checks=1');
            } elseif (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys=ON');
            }
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Seed des données essentielles du système
     */
    private function seedCoreData(): void
    {
        $this->command->info('📊 Seeding des données essentielles...');

        // Nettoyage des profils (obligatoire avant autres seeders)
        $this->call(CleanupProfilesSeeder::class);

        // Types de relations (optimisé sans reverse_relationship)
        $this->call(ComprehensiveRelationshipTypesSeeder::class);

        // Autres seeders essentiels si nécessaire
        // $this->call(CountriesSeeder::class);
        // $this->call(LanguagesSeeder::class);
    }

    /**
     * Seed des données de test (optionnel)
     */
    private function seedTestData(): void
    {
        if (!$this->shouldSeedTestData()) {
            $this->command->info('⏭️  Données de test ignorées (environnement de production)');
            return;
        }

        $this->command->info('🧪 Seeding des données de test...');

        // Albums photo de test (optimisé)
        $this->call(PhotoAlbumTestSeeder::class);

        // Autres données de test
        // $this->call(UserTestSeeder::class);
        // $this->call(FamilyRelationshipTestSeeder::class);
    }

    /**
     * Déterminer si on doit créer des données de test
     */
    private function shouldSeedTestData(): bool
    {
        // Ne pas créer de données de test en production
        if (app()->environment('production')) {
            return false;
        }

        // Demander confirmation en staging
        if (app()->environment('staging')) {
            return $this->command->confirm('Créer des données de test en staging ?', false);
        }

        // Toujours créer en développement et test
        return true;
    }

    /**
     * Statistiques de performance du seeding
     */
    private function showPerformanceStats(): void
    {
        $stats = [
            'relationship_types' => DB::table('relationship_types')->count(),
            'photo_albums' => DB::table('photo_albums')->count(),
            'photos' => DB::table('photos')->count(),
        ];

        $this->command->info('📈 Statistiques du seeding :');
        foreach ($stats as $table => $count) {
            $this->command->info("   • {$table}: {$count} enregistrements");
        }
    }
}

/**
 * Seeder spécialisé pour les utilisateurs de test
 */
class UserTestSeeder extends Seeder
{
    public function run(): void
    {
        // Créer des utilisateurs de test avec des profils complets
        // Optimisé avec des insertions en batch
    }
}

/**
 * Seeder spécialisé pour les relations familiales de test
 */
class FamilyRelationshipTestSeeder extends Seeder
{
    public function run(): void
    {
        // Créer des relations familiales de test
        // Optimisé avec des insertions en batch
    }
}

/**
 * Trait pour optimiser les performances des seeders
 */
trait OptimizedSeeding
{
    /**
     * Insérer des données en batch avec gestion des erreurs
     */
    protected function batchInsert(string $table, array $data, int $chunkSize = 1000): void
    {
        $chunks = array_chunk($data, $chunkSize);

        foreach ($chunks as $chunk) {
            try {
                DB::table($table)->insert($chunk);
            } catch (\Exception $e) {
                $this->command->error("Erreur lors de l'insertion dans {$table}: " . $e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * Vider une table de manière sécurisée
     */
    protected function truncateTable(string $table): void
    {
        try {
            DB::statement('SET foreign_key_checks=0');
            DB::table($table)->truncate();
            DB::statement('SET foreign_key_checks=1');
        } catch (\Exception $e) {
            $this->command->error("Erreur lors du vidage de {$table}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Générer des timestamps pour les insertions en batch
     */
    protected function getTimestamps(): array
    {
        $now = now();
        return [
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Générer des données aléatoires optimisées
     */
    protected function generateRandomData(array $templates, int $count): array
    {
        $data = [];
        $timestamps = $this->getTimestamps();

        for ($i = 0; $i < $count; $i++) {
            $item = [];

            foreach ($templates as $field => $options) {
                if (is_array($options)) {
                    $item[$field] = $options[array_rand($options)];
                } elseif (is_callable($options)) {
                    $item[$field] = $options($i);
                } else {
                    $item[$field] = $options;
                }
            }

            $data[] = array_merge($item, $timestamps);
        }

        return $data;
    }
}

/**
 * Configuration centralisée pour tous les seeders
 */
class SeederConfig
{
    public static function getPhotoTemplates(): array
    {
        return [
            'titles' => [
                'Coucher de soleil', 'Portrait de famille', 'Paysage magnifique', 'Moment de joie',
                'Souvenir précieux', 'Instant magique', 'Belle journée', 'Sourires partagés',
                'Nature sauvage', 'Architecture unique', 'Détail artistique', 'Émotion pure',
            ],
            'descriptions' => [
                'Une photo qui capture l\'essence du moment',
                'Un souvenir inoubliable de cette journée spéciale',
                'L\'émotion figée dans le temps',
                'Un instant de pure beauté',
            ]
        ];
    }

    public static function getDemoImages(): array
    {
        return [
            'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1500964757637-c85e8a162699?w=800&h=600&fit=crop',
        ];
    }

    public static function getAlbumTemplates(): array
    {
        return [
            ['title' => 'Vacances d\'été 2024', 'privacy' => 'family', 'photos_count' => 15],
            ['title' => 'Moments en famille', 'privacy' => 'private', 'photos_count' => 8],
            ['title' => 'Événements publics', 'privacy' => 'public', 'photos_count' => 23],
        ];
    }
}
