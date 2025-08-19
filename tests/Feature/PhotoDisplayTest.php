<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PhotoAlbum;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test que les URLs des photos sont correctement générées
     */
    public function test_photo_urls_are_generated_correctly(): void
    {
        echo "\n=== TEST URLS DES PHOTOS ===\n";

        // Créer un utilisateur et un album
        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        $album = PhotoAlbum::create([
            'user_id' => $user->id,
            'title' => 'Album Test',
            'description' => 'Album pour tester les photos',
            'privacy' => 'public',
        ]);

        // Créer une photo factice
        $photo = Photo::create([
            'user_id' => $user->id,
            'photo_album_id' => $album->id,
            'title' => 'Photo Test',
            'description' => 'Une photo de test',
            'file_path' => 'photos/1/1/test_photo.jpg',
            'file_name' => 'test_photo.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'width' => 800,
            'height' => 600,
            'order' => 0,
        ]);

        echo "✅ Photo créée avec file_path: {$photo->file_path}\n";

        // Tester l'URL générée
        $expectedUrl = asset('storage/photos/1/1/test_photo.jpg');
        $actualUrl = $photo->url;

        echo "URL attendue: {$expectedUrl}\n";
        echo "URL générée: {$actualUrl}\n";

        $this->assertEquals($expectedUrl, $actualUrl);
        echo "✅ URL de la photo correcte\n";

        // Tester l'affichage dans la page album
        $response = $this->actingAs($user)->get("/photo-albums/{$album->id}");
        
        $response->assertStatus(200);
        
        // Vérifier que les données passées à React contiennent les bonnes URLs
        $response->assertInertia(fn ($page) => 
            $page->component('PhotoAlbums/Show')
                ->has('photos', 1)
                ->where('photos.0.file_path', $expectedUrl)
        );

        echo "✅ Photo affichée correctement dans l'album\n";

        echo "\n🎉 TEST URLS DES PHOTOS TERMINÉ\n";
    }

    /**
     * Test l'upload et l'affichage d'une vraie photo
     */
    public function test_photo_upload_and_display(): void
    {
        echo "\n=== TEST UPLOAD ET AFFICHAGE PHOTO ===\n";

        // Créer un utilisateur et un album
        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        $album = PhotoAlbum::create([
            'user_id' => $user->id,
            'title' => 'Album Upload Test',
            'description' => 'Album pour tester l\'upload',
            'privacy' => 'public',
        ]);

        // Créer un fichier image factice
        $file = UploadedFile::fake()->image('test_photo.jpg', 800, 600);

        echo "✅ Fichier image factice créé: {$file->getClientOriginalName()}\n";

        // Tester l'upload
        $response = $this->actingAs($user)->post("/photo-albums/{$album->id}/photos", [
            'photos' => [$file],
            'title' => 'Photo uploadée',
            'description' => 'Une photo uploadée pour test',
        ]);

        $response->assertRedirect();
        echo "✅ Upload réussi\n";

        // Vérifier que la photo a été créée
        $photo = Photo::where('photo_album_id', $album->id)->first();
        $this->assertNotNull($photo);
        
        echo "Photo créée avec file_path: {$photo->file_path}\n";
        echo "URL générée: {$photo->url}\n";

        // Vérifier que l'URL ne contient pas de double 'public'
        $this->assertStringNotContainsString('public/public', $photo->url);
        $this->assertStringContainsString('storage/photos', $photo->url);
        
        echo "✅ URL de la photo correcte (pas de double 'public')\n";

        echo "\n🎉 TEST UPLOAD ET AFFICHAGE TERMINÉ\n";
    }
}
