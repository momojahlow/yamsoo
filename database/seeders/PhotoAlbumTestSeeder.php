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
        // Récupérer l'utilisateur connecté ou le premier utilisateur
        $user = User::first();
        
        if (!$user) {
            $this->command->error('Aucun utilisateur trouvé. Créez d\'abord un utilisateur.');
            return;
        }

        $this->command->info("Création d'albums de test pour l'utilisateur: {$user->name}");

        // Créer le dossier de stockage s'il n'existe pas
        if (!Storage::disk('public')->exists('photos')) {
            Storage::disk('public')->makeDirectory('photos');
        }
        if (!Storage::disk('public')->exists('photos/thumbnails')) {
            Storage::disk('public')->makeDirectory('photos/thumbnails');
        }

        // Albums de test avec différents niveaux de confidentialité
        $albums = [
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

        foreach ($albums as $albumData) {
            // Créer l'album
            $album = PhotoAlbum::create([
                'user_id' => $user->id,
                'title' => $albumData['title'],
                'description' => $albumData['description'],
                'privacy' => $albumData['privacy'],
                'is_default' => $albumData['is_default'],
                'cover_photo' => null, // Sera défini après création des photos
            ]);

            $this->command->info("Album créé: {$album->title}");

            // Créer des photos de test pour cet album
            $this->createTestPhotos($album, $albumData['photos_count']);
        }

        $this->command->info('Albums et photos de test créés avec succès !');
    }

    /**
     * Créer des photos de test pour un album
     */
    private function createTestPhotos(PhotoAlbum $album, int $count): void
    {
        $photoTitles = [
            'Coucher de soleil', 'Portrait de famille', 'Paysage magnifique', 'Moment de joie',
            'Souvenir précieux', 'Instant magique', 'Belle journée', 'Sourires partagés',
            'Nature sauvage', 'Architecture unique', 'Détail artistique', 'Émotion pure',
            'Lumière dorée', 'Complicité', 'Découverte', 'Aventure', 'Sérénité',
            'Célébration', 'Tendresse', 'Émerveillement', 'Bonheur simple', 'Instant présent',
            'Harmonie', 'Élégance', 'Spontanéité'
        ];

        $descriptions = [
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
        ];

        // URLs d'images de démonstration (Unsplash)
        $demoImages = [
            'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1500964757637-c85e8a162699?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1518837695005-2083093ee35b?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1502780402662-acc01917949e?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1540979388789-6cee28a1cdc9?w=800&h=600&fit=crop',
        ];

        for ($i = 0; $i < $count; $i++) {
            $title = $photoTitles[array_rand($photoTitles)];
            $description = $descriptions[array_rand($descriptions)];
            $imageUrl = $demoImages[array_rand($demoImages)];
            
            // Créer un nom de fichier unique
            $fileName = Str::slug($title) . '-' . time() . '-' . $i . '.jpg';
            $filePath = 'photos/' . $fileName;
            $thumbnailPath = 'photos/thumbnails/thumb_' . $fileName;

            // Créer la photo en base
            $photo = Photo::create([
                'user_id' => $album->user_id,
                'photo_album_id' => $album->id,
                'title' => $title,
                'description' => $description,
                'file_path' => $imageUrl, // Utiliser l'URL directement pour la démo
                'file_name' => $fileName,
                'mime_type' => 'image/jpeg',
                'file_size' => rand(500000, 3000000), // Taille aléatoire entre 500KB et 3MB
                'width' => 800,
                'height' => 600,
                'thumbnail_path' => $imageUrl . '&w=300&h=200', // Version miniature
                'metadata' => [
                    'camera' => 'iPhone 14 Pro',
                    'iso' => rand(100, 800),
                    'aperture' => 'f/' . (rand(14, 56) / 10),
                    'shutter_speed' => '1/' . rand(60, 500),
                    'focal_length' => rand(24, 85) . 'mm',
                ],
                'order' => $i + 1,
                'taken_at' => now()->subDays(rand(1, 365)),
            ]);

            // Définir la première photo comme couverture de l'album
            if ($i === 0) {
                $album->update(['cover_photo' => $photo->file_path]);
            }
        }

        // Mettre à jour le compteur de photos
        $album->update(['photos_count' => $count]);
    }
}
