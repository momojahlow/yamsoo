@extends('layouts.app')

@section('title', 'Créer un album photo')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center mb-8">
            <a href="{{ route('photo-albums.index') }}" 
               class="text-blue-600 hover:text-blue-800 mr-4">
                ← Retour aux albums
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Créer un nouvel album photo</h1>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form action="{{ route('photo-albums.store') }}" method="POST">
                @csrf

                <!-- Titre de l'album -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Titre de l'album *
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ex: Vacances d'été 2024"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Décrivez votre album photo...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confidentialité -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Confidentialité *
                    </label>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input id="privacy_family" 
                                   name="privacy" 
                                   type="radio" 
                                   value="family" 
                                   {{ old('privacy', 'family') === 'family' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="privacy_family" class="ml-3 block text-sm text-gray-700">
                                <span class="font-medium">👨‍👩‍👧‍👦 Famille</span>
                                <span class="text-gray-500 block">Visible par les membres de votre famille</span>
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input id="privacy_public" 
                                   name="privacy" 
                                   type="radio" 
                                   value="public" 
                                   {{ old('privacy') === 'public' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="privacy_public" class="ml-3 block text-sm text-gray-700">
                                <span class="font-medium">🌍 Public</span>
                                <span class="text-gray-500 block">Visible par tous les utilisateurs</span>
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input id="privacy_private" 
                                   name="privacy" 
                                   type="radio" 
                                   value="private" 
                                   {{ old('privacy') === 'private' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="privacy_private" class="ml-3 block text-sm text-gray-700">
                                <span class="font-medium">🔒 Privé</span>
                                <span class="text-gray-500 block">Visible uniquement par vous</span>
                            </label>
                        </div>
                    </div>
                    @error('privacy')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Album par défaut -->
                <div class="mb-6">
                    <div class="flex items-center">
                        <input id="is_default" 
                               name="is_default" 
                               type="checkbox" 
                               value="1"
                               {{ old('is_default') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_default" class="ml-3 block text-sm text-gray-700">
                            <span class="font-medium">⭐ Définir comme album par défaut</span>
                            <span class="text-gray-500 block">Les nouvelles photos seront ajoutées à cet album par défaut</span>
                        </label>
                    </div>
                    @error('is_default')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('photo-albums.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Annuler
                    </a>
                    
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        📸 Créer l'album
                    </button>
                </div>
            </form>
        </div>

        <!-- Conseils -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-800 mb-2">💡 Conseils pour votre album</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• Choisissez un titre descriptif pour retrouver facilement vos photos</li>
                <li>• Les albums familiaux sont parfaits pour partager des souvenirs avec vos proches</li>
                <li>• Vous pourrez ajouter des photos après avoir créé l'album</li>
                <li>• Un seul album peut être défini comme album par défaut</li>
            </ul>
        </div>
    </div>
</div>
@endsection
