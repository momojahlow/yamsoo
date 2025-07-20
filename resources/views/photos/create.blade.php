@extends('layouts.app')

@section('title', 'Ajouter des photos')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center mb-8">
            <a href="{{ route('photo-albums.show', $album) }}" 
               class="text-blue-600 hover:text-blue-800 mr-4">
                ← Retour à l'album
            </a>
            <h1 class="text-3xl font-bold text-gray-900">
                Ajouter des photos à "{{ $album->title }}"
            </h1>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form action="{{ route('albums.photos.store', $album) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Upload de fichiers -->
                <div class="mb-6">
                    <label for="photos" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionner les photos *
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="photos" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Télécharger des photos</span>
                                    <input id="photos" 
                                           name="photos[]" 
                                           type="file" 
                                           class="sr-only" 
                                           multiple 
                                           accept="image/*"
                                           required>
                                </label>
                                <p class="pl-1">ou glisser-déposer</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PNG, JPG, JPEG jusqu'à 10MB chacune
                            </p>
                        </div>
                    </div>
                    @error('photos')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('photos.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Titre global (optionnel) -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Titre pour toutes les photos (optionnel)
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ex: Vacances à la plage">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description globale (optionnelle) -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description pour toutes les photos (optionnelle)
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Décrivez ces photos...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('photo-albums.show', $album) }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Annuler
                    </a>
                    
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        📸 Ajouter les photos
                    </button>
                </div>
            </form>
        </div>

        <!-- Informations sur l'album -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-800 mb-2">📋 Informations sur l'album</h3>
            <div class="text-sm text-blue-700 space-y-1">
                <p><strong>Album :</strong> {{ $album->title }}</p>
                @if($album->description)
                    <p><strong>Description :</strong> {{ $album->description }}</p>
                @endif
                <p><strong>Confidentialité :</strong> 
                    @if($album->privacy === 'public')
                        🌍 Public
                    @elseif($album->privacy === 'family')
                        👨‍👩‍👧‍👦 Famille
                    @else
                        🔒 Privé
                    @endif
                </p>
                <p><strong>Photos actuelles :</strong> {{ $album->photos_count ?? 0 }}</p>
            </div>
        </div>

        <!-- Conseils -->
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-800 mb-2">💡 Conseils pour vos photos</h3>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>• Vous pouvez sélectionner plusieurs photos à la fois</li>
                <li>• Les formats supportés : JPG, JPEG, PNG</li>
                <li>• Taille maximale : 10MB par photo</li>
                <li>• Les photos seront automatiquement redimensionnées si nécessaire</li>
                <li>• Vous pourrez modifier le titre et la description de chaque photo individuellement après l'upload</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Améliorer l'expérience utilisateur avec le drag & drop
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.querySelector('.border-dashed');
    const fileInput = document.getElementById('photos');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    dropZone.addEventListener('drop', handleDrop, false);

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight(e) {
        dropZone.classList.add('border-blue-400', 'bg-blue-50');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        
        // Afficher le nombre de fichiers sélectionnés
        if (files.length > 0) {
            const fileCount = files.length;
            const fileText = fileCount === 1 ? 'fichier sélectionné' : 'fichiers sélectionnés';
            dropZone.querySelector('p.pl-1').textContent = `${fileCount} ${fileText}`;
        }
    }

    // Afficher le nombre de fichiers sélectionnés via le bouton
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const fileCount = this.files.length;
            const fileText = fileCount === 1 ? 'fichier sélectionné' : 'fichiers sélectionnés';
            dropZone.querySelector('p.pl-1').textContent = `${fileCount} ${fileText}`;
        }
    });
});
</script>
@endsection
