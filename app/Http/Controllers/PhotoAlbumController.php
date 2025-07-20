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
    public function index(Request $request, ?User $user = null)
    {
        $user = $user ?? Auth::user();

        // Vérifier les permissions
        if ($user->id !== Auth::id() && !$user->hasRelationWith(Auth::user())) {
            abort(403, 'Vous n\'avez pas accès aux albums de cet utilisateur.');
        }

        // Pour l'instant, récupérer tous les albums de l'utilisateur
        $albums = $user->photoAlbums()->withCount('photos')->get();

        return Inertia::render('PhotoAlbums/Index', [
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
        return view('photo-albums.create');
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
        // Vérifier les permissions
        if (!$photoAlbum->canBeViewedBy(Auth::user())) {
            abort(403, 'Vous n\'avez pas accès à cet album.');
        }

        $photos = $photoAlbum->photos()->paginate(24);

        return view('photo-albums.show', compact('photoAlbum', 'photos'));
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
