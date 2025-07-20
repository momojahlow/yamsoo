<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\PhotoAlbum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class PhotoController extends Controller
{
    /**
     * Display a listing of photos for an album.
     */
    public function index(PhotoAlbum $album)
    {
        // Vérifier les permissions
        if (!$album->canBeViewedBy(Auth::user())) {
            abort(403, 'Vous n\'avez pas accès à cet album.');
        }

        $photos = $album->photos()->paginate(24);

        return view('photos.index', compact('album', 'photos'));
    }

    /**
     * Show the form for uploading new photos.
     */
    public function create(PhotoAlbum $album)
    {
        // Seul le propriétaire peut ajouter des photos
        if ($album->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas ajouter de photos à cet album.');
        }

        return view('photos.create', compact('album'));
    }

    /**
     * Store newly uploaded photos.
     */
    public function store(Request $request, PhotoAlbum $album)
    {
        // Seul le propriétaire peut ajouter des photos
        if ($album->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas ajouter de photos à cet album.');
        }

        $request->validate([
            'photos' => 'required|array|max:10',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $uploadedPhotos = [];

        foreach ($request->file('photos') as $index => $file) {
            $uploadedPhotos[] = $this->processAndStorePhoto($file, $album, $request, $index);
        }

        // Mettre à jour le compteur de photos de l'album
        $album->updatePhotosCount();

        $message = count($uploadedPhotos) === 1
            ? 'Photo ajoutée avec succès !'
            : count($uploadedPhotos) . ' photos ajoutées avec succès !';

        return redirect()->route('photo-albums.show', $album)
                        ->with('success', $message);
    }

    /**
     * Display the specified photo.
     */
    public function show(Photo $photo)
    {
        // Vérifier les permissions
        if (!$photo->canBeViewedBy(Auth::user())) {
            abort(403, 'Vous n\'avez pas accès à cette photo.');
        }

        return view('photos.show', compact('photo'));
    }

    /**
     * Show the form for editing the photo.
     */
    public function edit(Photo $photo)
    {
        // Seul le propriétaire peut modifier
        if ($photo->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas modifier cette photo.');
        }

        return view('photos.edit', compact('photo'));
    }

    /**
     * Update the specified photo.
     */
    public function update(Request $request, Photo $photo)
    {
        // Seul le propriétaire peut modifier
        if ($photo->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas modifier cette photo.');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer|min:0',
        ]);

        $photo->update($validated);

        return redirect()->route('photos.show', $photo)
                        ->with('success', 'Photo mise à jour avec succès !');
    }

    /**
     * Remove the specified photo.
     */
    public function destroy(Photo $photo)
    {
        // Seul le propriétaire peut supprimer
        if ($photo->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas supprimer cette photo.');
        }

        $album = $photo->album;
        $photo->deleteWithFiles();

        return redirect()->route('photo-albums.show', $album)
                        ->with('success', 'Photo supprimée avec succès !');
    }

    /**
     * Process and store a photo with thumbnail generation.
     */
    private function processAndStorePhoto($file, PhotoAlbum $album, Request $request, int $index): Photo
    {
        $fileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
        $filePath = 'photos/' . Auth::id() . '/' . $album->id . '/' . $fileName;

        // Stocker le fichier original
        $file->storeAs('photos/' . Auth::id() . '/' . $album->id, $fileName, 'public');

        // Générer une miniature
        $thumbnailPath = $this->generateThumbnail($file, $filePath);

        // Obtenir les dimensions de l'image
        $imageSize = getimagesize($file->getRealPath());
        $width = $imageSize[0] ?? null;
        $height = $imageSize[1] ?? null;

        // Extraire les métadonnées EXIF si disponibles
        $metadata = $this->extractMetadata($file);

        return Photo::create([
            'user_id' => Auth::id(),
            'photo_album_id' => $album->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'file_path' => 'public/' . $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'thumbnail_path' => $thumbnailPath,
            'metadata' => $metadata,
            'order' => $album->photos()->count(),
            'taken_at' => $metadata['taken_at'] ?? now(),
        ]);
    }

    /**
     * Generate thumbnail for the photo.
     */
    private function generateThumbnail($file, string $originalPath): ?string
    {
        try {
            $thumbnailPath = str_replace('.', '_thumb.', $originalPath);

            // Créer une miniature de 300x300 pixels
            $image = Image::make($file->getRealPath())
                         ->fit(300, 300, function ($constraint) {
                             $constraint->upsize();
                         });

            Storage::disk('public')->put($thumbnailPath, $image->encode());

            return 'public/' . $thumbnailPath;
        } catch (\Exception $e) {
            // En cas d'erreur, retourner null (pas de miniature)
            return null;
        }
    }

    /**
     * Extract metadata from the photo.
     */
    private function extractMetadata($file): array
    {
        $metadata = [];

        try {
            $exif = exif_read_data($file->getRealPath());

            if ($exif) {
                // Date de prise de vue
                if (isset($exif['DateTime'])) {
                    $metadata['taken_at'] = \Carbon\Carbon::createFromFormat('Y:m:d H:i:s', $exif['DateTime']);
                }

                // Informations de l'appareil photo
                if (isset($exif['Make'])) {
                    $metadata['camera_make'] = $exif['Make'];
                }

                if (isset($exif['Model'])) {
                    $metadata['camera_model'] = $exif['Model'];
                }

                // Paramètres de prise de vue
                if (isset($exif['ExposureTime'])) {
                    $metadata['exposure_time'] = $exif['ExposureTime'];
                }

                if (isset($exif['FNumber'])) {
                    $metadata['f_number'] = $exif['FNumber'];
                }

                if (isset($exif['ISOSpeedRatings'])) {
                    $metadata['iso'] = $exif['ISOSpeedRatings'];
                }
            }
        } catch (\Exception $e) {
            // En cas d'erreur, continuer sans métadonnées
        }

        return $metadata;
    }
}
