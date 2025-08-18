<?php

namespace App\Http\Controllers;

use App\Models\PhotoAlbum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PhotoAlbumController extends Controller
{
    /**
     * Display a listing of the user's photo albums.
     */
    public function index(?User $user = null)
    {
        $user = $user ?? Auth::user();

        // Vérifier les permissions (pour l'instant, seul le propriétaire peut voir ses albums)
        if ($user->id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas accès aux albums de cet utilisateur.');
        }

        // Récupérer tous les albums de l'utilisateur avec le nombre de photos
        $albums = PhotoAlbum::where('user_id', $user->id)
            ->withCount('photos')
            ->with(['photos' => function($query) {
                $query->latest()->limit(1); // Récupérer la dernière photo pour la couverture
            }])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Transformer les albums pour l'affichage
        $albums = $albums->map(function ($album) {
            return [
                'id' => $album->id,
                'title' => $album->title,
                'description' => $album->description,
                'cover_photo' => $album->cover_photo ?: ($album->photos->first()?->file_path ?? null),
                'privacy' => $album->privacy,
                'is_default' => $album->is_default,
                'photos_count' => $album->photos_count,
                'created_at' => $album->created_at->toISOString(),
                'updated_at' => $album->updated_at->toISOString(),
            ];
        });

        return Inertia::render('PhotoAlbums/ModernIndex', [
            'albums' => $albums,
            'user' => $user,
            'canCreateAlbum' => $user->id === Auth::id(),
        ]);
    }

    /**
     * Show the form for creating a new album.
     */
    public function create()
    {
        return Inertia::render('PhotoAlbums/Create');
    }

    /**
     * Store a newly created album.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'privacy' => 'required|in:public,family,private',
            'is_default' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        $album = PhotoAlbum::create($validated);

        // Si c'est défini comme album par défaut
        if ($request->boolean('is_default')) {
            $album->setAsDefault();
        }

        return redirect()->route('photo-albums.show', $album)
                        ->with('success', 'Album créé avec succès !');
    }

    /**
     * Display the specified album.
     */
    public function show(PhotoAlbum $photoAlbum)
    {
        // Vérifier les permissions (pour l'instant, seul le propriétaire peut voir)
        if ($photoAlbum->user_id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas accès à cet album.');
        }

        // Charger les photos avec leurs informations
        $photos = $photoAlbum->photos()
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'title' => $photo->title,
                    'description' => $photo->description,
                    'file_path' => $photo->file_path,
                    'thumbnail_path' => $photo->thumbnail_path ?: $photo->file_path,
                    'width' => $photo->width,
                    'height' => $photo->height,
                    'file_size' => $photo->file_size,
                    'taken_at' => $photo->taken_at?->toISOString() ?? $photo->created_at->toISOString(),
                    'created_at' => $photo->created_at->toISOString(),
                ];
            });

        // Charger l'utilisateur propriétaire
        $photoAlbum->load('user');

        // Transformer l'album pour l'affichage
        $album = [
            'id' => $photoAlbum->id,
            'title' => $photoAlbum->title,
            'description' => $photoAlbum->description,
            'cover_photo' => $photoAlbum->cover_photo,
            'privacy' => $photoAlbum->privacy,
            'is_default' => $photoAlbum->is_default,
            'photos_count' => $photoAlbum->photos_count,
            'created_at' => $photoAlbum->created_at->toISOString(),
            'updated_at' => $photoAlbum->updated_at->toISOString(),
            'user' => [
                'id' => $photoAlbum->user->id,
                'name' => $photoAlbum->user->name,
            ],
        ];

        return Inertia::render('PhotoAlbums/Show', [
            'album' => $album,
            'photos' => $photos,
            'canEdit' => $photoAlbum->user_id === Auth::id(),
        ]);
    }

    /**
     * Show the form for editing the album.
     */
    public function edit(PhotoAlbum $photoAlbum)
    {
        // Seul le propriétaire peut modifier
        if ($photoAlbum->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas modifier cet album.');
        }

        return view('photo-albums.edit', compact('photoAlbum'));
    }

    /**
     * Update the specified album.
     */
    public function update(Request $request, PhotoAlbum $photoAlbum)
    {
        // Seul le propriétaire peut modifier
        if ($photoAlbum->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas modifier cet album.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'privacy' => 'required|in:public,family,private',
            'is_default' => 'boolean',
        ]);

        $photoAlbum->update($validated);

        // Si c'est défini comme album par défaut
        if ($request->boolean('is_default')) {
            $photoAlbum->setAsDefault();
        }

        return redirect()->route('photo-albums.show', $photoAlbum)
                        ->with('success', 'Album mis à jour avec succès !');
    }

    /**
     * Remove the specified album.
     */
    public function destroy(PhotoAlbum $photoAlbum)
    {
        // Seul le propriétaire peut supprimer
        if ($photoAlbum->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas supprimer cet album.');
        }

        // Ne pas permettre la suppression de l'album par défaut s'il contient des photos
        if ($photoAlbum->is_default && $photoAlbum->photos_count > 0) {
            return back()->with('error', 'Impossible de supprimer l\'album par défaut contenant des photos.');
        }

        // Supprimer toutes les photos de l'album
        foreach ($photoAlbum->photos as $photo) {
            $photo->deleteWithFiles();
        }

        $photoAlbum->delete();

        return redirect()->route('photo-albums.index')
                        ->with('success', 'Album supprimé avec succès !');
    }
}
