@extends('layouts.app')

@section('title', $photoAlbum->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête de l'album -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <a href="{{ route('photo-albums.index') }}" 
                   class="text-blue-600 hover:text-blue-800 mr-4">
                    ← Retour aux albums
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ $photoAlbum->title }}</h1>
            </div>

            @if($photoAlbum->user_id === auth()->id())
                <div class="flex space-x-2">
                    <a href="{{ route('albums.photos.create', $photoAlbum) }}" 
                       class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        📸 Ajouter des photos
                    </a>
                    <a href="{{ route('photo-albums.edit', $photoAlbum) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        ✏️ Modifier
                    </a>
                </div>
            @endif
        </div>

        <!-- Informations de l'album -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    @if($photoAlbum->description)
                        <p class="text-gray-600 mb-4">{{ $photoAlbum->description }}</p>
                    @endif

                    <div class="flex items-center space-x-6 text-sm text-gray-500">
                        <span>👤 {{ $photoAlbum->user->name }}</span>
                        <span>📸 {{ $photoAlbum->photos_count }} photo{{ $photoAlbum->photos_count > 1 ? 's' : '' }}</span>
                        <span>📅 {{ $photoAlbum->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    @if($photoAlbum->is_default)
                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">⭐ Défaut</span>
                    @endif

                    @if($photoAlbum->privacy === 'public')
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">🌍 Public</span>
                    @elseif($photoAlbum->privacy === 'family')
                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">👨‍👩‍👧‍👦 Famille</span>
                    @else
                        <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">🔒 Privé</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Galerie de photos -->
    @if($photos->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-8">
            @foreach($photos as $photo)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-200 group">
                    <div class="aspect-square relative">
                        @if($photo->thumbnail_path && Storage::exists($photo->thumbnail_path))
                            <img src="{{ Storage::url($photo->thumbnail_path) }}" 
                                 alt="{{ $photo->title ?? 'Photo' }}"
                                 class="w-full h-full object-cover">
                        @elseif($photo->file_path && Storage::exists($photo->file_path))
                            <img src="{{ Storage::url($photo->file_path) }}" 
                                 alt="{{ $photo->title ?? 'Photo' }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <div class="text-gray-400 text-center">
                                    <div class="text-2xl mb-1">📷</div>
                                    <div class="text-xs">Image non disponible</div>
                                </div>
                            </div>
                        @endif

                        <!-- Overlay avec actions -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
                            <div class="flex space-x-2">
                                <a href="{{ route('photos.show', $photo) }}" 
                                   class="bg-white text-gray-800 p-2 rounded-full hover:bg-gray-100 transition duration-200"
                                   title="Voir la photo">
                                    👁️
                                </a>
                                @if($photoAlbum->user_id === auth()->id())
                                    <a href="{{ route('photos.edit', $photo) }}" 
                                       class="bg-white text-gray-800 p-2 rounded-full hover:bg-gray-100 transition duration-200"
                                       title="Modifier">
                                        ✏️
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($photo->title)
                        <div class="p-2">
                            <p class="text-xs text-gray-600 truncate">{{ $photo->title }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($photos->hasPages())
            <div class="flex justify-center">
                {{ $photos->links() }}
            </div>
        @endif
    @else
        <!-- Album vide -->
        <div class="text-center py-12">
            <div class="text-6xl mb-4">📷</div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucune photo dans cet album</h3>
            <p class="text-gray-500 mb-6">
                @if($photoAlbum->user_id === auth()->id())
                    Commencez à ajouter des photos pour créer de beaux souvenirs !
                @else
                    {{ $photoAlbum->user->name }} n'a pas encore ajouté de photos à cet album.
                @endif
            </p>
            @if($photoAlbum->user_id === auth()->id())
                <a href="{{ route('albums.photos.create', $photoAlbum) }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    📸 Ajouter mes premières photos
                </a>
            @endif
        </div>
    @endif

    <!-- Actions de l'album (pour le propriétaire) -->
    @if($photoAlbum->user_id === auth()->id())
        <div class="mt-12 pt-8 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions de l'album</h3>
            <div class="flex space-x-4">
                <a href="{{ route('photo-albums.edit', $photoAlbum) }}" 
                   class="text-blue-600 hover:text-blue-800 text-sm">
                    ✏️ Modifier l'album
                </a>
                
                @if(!$photoAlbum->is_default || $photoAlbum->photos_count === 0)
                    <form action="{{ route('photo-albums.destroy', $photoAlbum) }}" 
                          method="POST" 
                          class="inline"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet album ? Cette action est irréversible.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                            🗑️ Supprimer l'album
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
