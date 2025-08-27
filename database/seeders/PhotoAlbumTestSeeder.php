<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PhotoAlbum;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoAlbumTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            $this->command->error('Aucun utilisateur trouvé. Créez d\'abord un utilisateur.');
            return;
        }

        $this->command->info("Création d'albums de test pour l'utilisateur: {$user->name}");
        $this->ensureStorageDirectories();

        // Configuration optimisée des albums
        $albumsConfig = $this->getAlbumsConfiguration();
        $createdAlbums = [];

        // Création en batch des albums
        foreach ($albumsConfig as $config) {
            $album = PhotoAlbum::create([
                'user_id' => $user->id,
                'title' => $config['title'],
                'description' => $config['description'],
                'privacy' => $config['privacy'],
                'is_default' => $config['is_default'],
                'cover_photo' => null,
            ]);

            $createdAlbums[] = ['album' => $album, 'photos_count' => $config['photos_count']];
            $this->command->info("Album créé: {$album->title}");
        }

        // Création en batch des photos pour tous les albums
        $this->createPhotosForAllAlbums($createdAlbums);

        $totalPhotos = array_sum(array_column($albumsConfig, 'photos_count'));
        $this->command->info("✅ {count($albumsConfig)} albums et {$totalPhotos} photos créés avec succès !");
    }

    /**
     * Configuration optimisée des albums
     */
    private function getAlbumsConfiguration(): array
    {
        return [
            [
                'title' => 'Vacances d\'été 2024',
                'description' => 'Nos meilleures photos de vacances en famille au bord de la mer',
                'privacy' => 'family',
                'is_default' => false,
                'photos_count' => 15,
            ],
            [
                'title' => 'Moments en famille',
                'description' => 'Les petits moments du quotidien qui comptent le plus',
                'privacy' => 'private',
                'is_default' => true,
                'photos_count' => 8,
            ],
            [
                'title' => 'Événements publics',
                'description' => 'Photos des événements familiaux à partager avec tous',
                'privacy' => 'public',
                'is_default' => false,
                'photos_count' => 23,
            ],
            [
                'title' => 'Anniversaires',
                'description' => 'Collection de tous les anniversaires de la famille',
                'privacy' => 'family',
                'is_default' => false,
                'photos_count' => 12,
            ],
            [
                'title' => 'Voyage à Paris',
                'description' => 'Découverte de la capitale française en famille',
                'privacy' => 'public',
                'is_default' => false,
                'photos_count' => 18,
            ],
        ];
    }

    /**
     * Créer les dossiers de stockage nécessaires
     */
    private function ensureStorageDirectories(): void
    {
        $directories = ['photos', 'photos/thumbnails'];

        foreach ($directories as $directory) {
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
        }
    }

    /**
     * Créer les photos pour tous les albums de manière optimisée
     */
    private function createPhotosForAllAlbums(array $albumsData): void
    {
        $photoTemplates = $this->getPhotoTemplates();
        $demoImages = $this->getDemoImages();

        foreach ($albumsData as $albumData) {
            $this->createTestPhotos($albumData['album'], $albumData['photos_count'], $photoTemplates, $demoImages);
        }
    }

    /**
     * Obtenir les templates de photos
     */
    private function getPhotoTemplates(): array
    {
        return [
            'titles' => [
                'Coucher de soleil', 'Portrait de famille', 'Paysage magnifique', 'Moment de joie',
                'Souvenir précieux', 'Instant magique', 'Belle journée', 'Sourires partagés',
                'Nature sauvage', 'Architecture unique', 'Détail artistique', 'Émotion pure',
                'Lumière dorée', 'Complicité', 'Découverte', 'Aventure', 'Sérénité',
                'Célébration', 'Tendresse', 'Émerveillement', 'Bonheur simple', 'Instant présent',
                'Harmonie', 'Élégance', 'Spontanéité'
            ],
            'descriptions' => [
                'Une photo qui capture l\'essence du moment',
                'Un souvenir inoubliable de cette journée spéciale',
                'L\'émotion figée dans le temps',
                'Un instant de pure beauté',
                'Le bonheur à l\'état pur',
                'Une composition parfaite',
                'La magie de l\'instant présent',
                'Un moment de grâce',
                'L\'art de vivre en famille',
                'Une perspective unique sur la vie'
            ]
        ];
    }

    /**
     * Obtenir les URLs d'images de démonstration
     */
    private function getDemoImages(): array
    {
        return [
            'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1500964757637-c85e8a162699?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1518837695005-2083093ee35b?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1502780402662-acc01917949e?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1540979388789-6cee28a1cdc9?w=800&h=600&fit=crop',
        ];
    }

    /**
     * Créer des photos de test pour un album (version optimisée)
     */
    private function createTestPhotos(PhotoAlbum $album, int $count, array $templates, array $demoImages): void
    {
        $photos = [];
        $baseTime = time();

        // Préparer toutes les photos en batch
        for ($i = 0; $i < $count; $i++) {
            $title = $templates['titles'][array_rand($templates['titles'])];
            $description = $templates['descriptions'][array_rand($templates['descriptions'])];
            $imageUrl = $demoImages[array_rand($demoImages)];
            $fileName = Str::slug($title) . '-' . $baseTime . '-' . $i . '.jpg';

            $photos[] = [
                'user_id' => $album->user_id,
                'photo_album_id' => $album->id,
                'title' => $title,
                'description' => $description,
                'file_path' => $imageUrl,
                'file_name' => $fileName,
                'mime_type' => 'image/jpeg',
                'file_size' => rand(500000, 3000000),
                'width' => 800,
                'height' => 600,
                'thumbnail_path' => $imageUrl . '&w=300&h=200',
                'metadata' => json_encode([
                    'camera' => 'iPhone 14 Pro',
                    'iso' => rand(100, 800),
                    'aperture' => 'f/' . (rand(14, 56) / 10),
                    'shutter_speed' => '1/' . rand(60, 500),
                    'focal_length' => rand(24, 85) . 'mm',
                ]),
                'order' => $i + 1,
                'taken_at' => now()->subDays(rand(1, 365)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertion en batch pour de meilleures performances
        Photo::insert($photos);

        // Définir la première photo comme couverture et mettre à jour le compteur
        if (!empty($photos)) {
            $album->update([
                'cover_photo' => $photos[0]['file_path'],
                'photos_count' => $count
            ]);
        }
    }
}
