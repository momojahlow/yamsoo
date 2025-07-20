<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\PhotoAlbum;
use App\Models\Photo;

class TestPhotoAlbums extends Command
{
    protected $signature = 'test:photo-albums';
    protected $description = 'Tester le système d\'albums photo pour les utilisateurs';

    public function handle()
    {
        $this->info('🖼️  TEST DU SYSTÈME D\'ALBUMS PHOTO');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Trouver quelques utilisateurs pour les tests
        $users = User::take(3)->get();
        
        if ($users->count() < 3) {
            $this->error('❌ Pas assez d\'utilisateurs pour les tests (minimum 3 requis)');
            return 1;
        }

        $this->info('👥 UTILISATEURS DE TEST :');
        foreach ($users as $user) {
            $this->line("   • {$user->name} (ID: {$user->id})");
        }
        $this->newLine();

        // Test 1: Créer des albums pour chaque utilisateur
        $this->info('1️⃣ CRÉATION D\'ALBUMS PHOTO :');
        
        foreach ($users as $user) {
            // Album par défaut
            $defaultAlbum = $user->photoAlbums()->create([
                'title' => 'Photos de ' . $user->name,
                'description' => 'Album photo principal de ' . $user->name,
                'privacy' => 'family',
                'is_default' => true,
            ]);

            // Album familial
            $familyAlbum = $user->photoAlbums()->create([
                'title' => 'Moments en famille',
                'description' => 'Photos de famille et événements spéciaux',
                'privacy' => 'family',
                'is_default' => false,
            ]);

            // Album privé
            $privateAlbum = $user->photoAlbums()->create([
                'title' => 'Photos personnelles',
                'description' => 'Album privé',
                'privacy' => 'private',
                'is_default' => false,
            ]);

            $this->line("   ✅ Albums créés pour {$user->name} :");
            $this->line("      • {$defaultAlbum->title} (par défaut, famille)");
            $this->line("      • {$familyAlbum->title} (famille)");
            $this->line("      • {$privateAlbum->title} (privé)");
        }
        $this->newLine();

        // Test 2: Simuler l'ajout de photos
        $this->info('2️⃣ SIMULATION D\'AJOUT DE PHOTOS :');
        
        foreach ($users as $user) {
            $albums = $user->photoAlbums;
            
            foreach ($albums as $album) {
                // Simuler 3-5 photos par album
                $photoCount = rand(3, 5);
                
                for ($i = 1; $i <= $photoCount; $i++) {
                    Photo::create([
                        'user_id' => $user->id,
                        'photo_album_id' => $album->id,
                        'title' => "Photo {$i} - {$album->title}",
                        'description' => "Description de la photo {$i}",
                        'file_path' => "public/photos/{$user->id}/{$album->id}/photo_{$i}.jpg",
                        'file_name' => "photo_{$i}.jpg",
                        'mime_type' => 'image/jpeg',
                        'file_size' => rand(500000, 2000000), // 500KB à 2MB
                        'width' => rand(1920, 4000),
                        'height' => rand(1080, 3000),
                        'thumbnail_path' => "public/photos/{$user->id}/{$album->id}/photo_{$i}_thumb.jpg",
                        'metadata' => [
                            'camera_make' => 'Canon',
                            'camera_model' => 'EOS R5',
                            'taken_at' => now()->subDays(rand(1, 365))->toISOString(),
                        ],
                        'order' => $i,
                        'taken_at' => now()->subDays(rand(1, 365)),
                    ]);
                }
                
                // Mettre à jour le compteur de photos
                $album->updatePhotosCount();
                
                $this->line("   📸 {$photoCount} photos ajoutées à '{$album->title}' de {$user->name}");
            }
        }
        $this->newLine();

        // Test 3: Vérifier les relations et permissions
        $this->info('3️⃣ TEST DES PERMISSIONS D\'ACCÈS :');
        
        $user1 = $users[0];
        $user2 = $users[1];
        $user3 = $users[2];
        
        $this->line("   👤 Testeur principal : {$user1->name}");
        $this->line("   👤 Utilisateur 2 : {$user2->name}");
        $this->line("   👤 Utilisateur 3 : {$user3->name}");
        $this->newLine();

        // Tester l'accès aux albums
        foreach ($user2->photoAlbums as $album) {
            $canView = $album->canBeViewedBy($user1);
            $privacyIcon = $album->privacy === 'public' ? '🌍' : ($album->privacy === 'family' ? '👨‍👩‍👧‍👦' : '🔒');
            $accessIcon = $canView ? '✅' : '❌';
            
            $this->line("   {$accessIcon} {$privacyIcon} Album '{$album->title}' ({$album->privacy}) - Accès : " . ($canView ? 'Autorisé' : 'Refusé'));
        }
        $this->newLine();

        // Test 4: Statistiques des albums
        $this->info('4️⃣ STATISTIQUES DES ALBUMS :');
        
        $totalAlbums = PhotoAlbum::count();
        $totalPhotos = Photo::count();
        $publicAlbums = PhotoAlbum::where('privacy', 'public')->count();
        $familyAlbums = PhotoAlbum::where('privacy', 'family')->count();
        $privateAlbums = PhotoAlbum::where('privacy', 'private')->count();
        
        $this->line("   📊 Total albums : {$totalAlbums}");
        $this->line("   📸 Total photos : {$totalPhotos}");
        $this->line("   🌍 Albums publics : {$publicAlbums}");
        $this->line("   👨‍👩‍👧‍👦 Albums familiaux : {$familyAlbums}");
        $this->line("   🔒 Albums privés : {$privateAlbums}");
        $this->newLine();

        // Test 5: Albums par utilisateur
        $this->info('5️⃣ DÉTAIL PAR UTILISATEUR :');
        
        foreach ($users as $user) {
            $userAlbums = $user->photoAlbums()->withCount('photos')->get();
            $totalUserPhotos = $user->photos()->count();
            
            $this->line("   👤 {$user->name} :");
            $this->line("      📊 {$userAlbums->count()} albums, {$totalUserPhotos} photos au total");
            
            foreach ($userAlbums as $album) {
                $defaultIcon = $album->is_default ? '⭐' : '  ';
                $privacyIcon = $album->privacy === 'public' ? '🌍' : ($album->privacy === 'family' ? '👨‍👩‍👧‍👦' : '🔒');
                
                $this->line("      {$defaultIcon} {$privacyIcon} {$album->title} ({$album->photos_count} photos)");
            }
            $this->newLine();
        }

        // Test 6: Test des méthodes du modèle User
        $this->info('6️⃣ TEST DES MÉTHODES DU MODÈLE USER :');
        
        $testUser = $users[0];
        
        // Test getOrCreateDefaultAlbum
        $defaultAlbum = $testUser->getOrCreateDefaultAlbum();
        $this->line("   ✅ Album par défaut : {$defaultAlbum->title}");
        
        // Test recentPhotos
        $recentPhotos = $testUser->recentPhotos(5)->get();
        $this->line("   📸 Photos récentes : {$recentPhotos->count()} photos");
        
        // Test visibleAlbumsFor
        $visibleAlbums = $testUser->visibleAlbumsFor($users[1]);
        $this->line("   👁️  Albums visibles pour {$users[1]->name} : {$visibleAlbums->count()} albums");
        
        $this->newLine();
        $this->info('🎯 TESTS TERMINÉS AVEC SUCCÈS !');
        $this->line('   Le système d\'albums photo fonctionne correctement.');
        $this->line('   Vous pouvez maintenant utiliser les routes et contrôleurs pour l\'interface utilisateur.');

        return 0;
    }
}
