@extends('layouts.app')

@section('title', 'Albums Photo')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            Albums Photo
            @if(isset($user) && $user->id !== auth()->id())
                de {{ $user->name }}
            @endif
        </h1>
        
        @if(!isset($user) || $user->id === auth()->id())
            <a href="{{ route('photo-albums.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                📸 Créer un album
            </a>
        @endif
    </div>

    @if($albums->isEmpty())
        <div class="text-center py-12">
            <div class="text-6xl mb-4">📷</div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucun album photo</h3>
            <p class="text-gray-500 mb-6">
                @if(!isset($user) || $user->id === auth()->id())
                    Créez votre premier album pour commencer à partager vos souvenirs !
                @else
                    {{ $user->name }} n'a pas encore d'albums photo visibles.
                @endif
            </p>
            @if(!isset($user) || $user->id === auth()->id())
                <a href="{{ route('photo-albums.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Créer mon premier album
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($albums as $album)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-200">
                    <!-- Photo de couverture -->
                    <div class="h-48 bg-gray-200 relative">
                        @if($album->cover_photo)
                            <img src="{{ Storage::url($album->cover_photo) }}" 
                                 alt="Couverture de {{ $album->title }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <div class="text-center">
                                    <div class="text-4xl mb-2">📷</div>
                                    <div class="text-sm">Aucune photo</div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Badge de confidentialité -->
                        <div class="absolute top-2 right-2">
                            @if($album->privacy === 'public')
                                <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">🌍 Public</span>
                            @elseif($album->privacy === 'family')
                                <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">👨‍👩‍👧‍👦 Famille</span>
                            @else
                                <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">🔒 Privé</span>
                            @endif
                        </div>

                        <!-- Badge album par défaut -->
                        @if($album->is_default)
                            <div class="absolute top-2 left-2">
                                <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">⭐ Défaut</span>
                            </div>
                        @endif
                    </div>

                    <!-- Informations de l'album -->
                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ $album->title }}</h3>
                        
                        @if($album->description)
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $album->description }}</p>
                        @endif

                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <span>📸 {{ $album->photos_count }} photo{{ $album->photos_count > 1 ? 's' : '' }}</span>
                            <span>{{ $album->created_at->format('d/m/Y') }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <a href="{{ route('photo-albums.show', $album) }}" 
                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded text-sm transition duration-200">
                                Voir l'album
                            </a>
                            
                            @if(!isset($user) || $user->id === auth()->id())
                                <a href="{{ route('albums.photos.create', $album) }}" 
                                   class="bg-green-600 hover:bg-green-700 text-white py-2 px-3 rounded text-sm transition duration-200">
                                    + Photos
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Statistiques -->
    @if($albums->isNotEmpty())
        <div class="mt-12 bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Statistiques</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-blue-600">{{ $albums->count() }}</div>
                    <div class="text-sm text-gray-600">Albums</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600">{{ $albums->sum('photos_count') }}</div>
                    <div class="text-sm text-gray-600">Photos</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600">{{ $albums->where('privacy', 'family')->count() }}</div>
                    <div class="text-sm text-gray-600">Albums familiaux</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-red-600">{{ $albums->where('privacy', 'private')->count() }}</div>
                    <div class="text-sm text-gray-600">Albums privés</div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
