@extends('layouts.admin')

@section('title', 'Admin - Nouvelle Ressource')

@section('admin-title', 'NOUVELLE RESSOURCE')

@section('admin-content')

        <div class="max-w-2xl mx-auto bg-gray-800/50 border border-gray-700 rounded-lg p-6">
            <form action="{{ route('admin.ressources.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Code (unique)</label>
                            <input type="text" name="code" value="{{ old('code') }}" required maxlength="10"
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white font-mono uppercase">
                            @error('code')<span class="text-red-400 text-sm">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Nom</label>
                            <input type="text" name="nom" value="{{ old('nom') }}" required
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Categorie</label>
                        <select name="categorie" required class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            <option value="metaux">Metaux</option>
                            <option value="gaz">Gaz</option>
                            <option value="elementaire">Elementaire</option>
                            <option value="chimie">Chimie</option>
                            <option value="exotique">Exotique</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Rarete (1-100)</label>
                            <input type="number" name="rarete" value="{{ old('rarete', 50) }}" required min="1" max="100"
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            <p class="text-xs text-gray-500 mt-1">Plus eleve = plus commun</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Prix de base (Cr)</label>
                            <input type="number" step="0.01" name="prix_base" value="{{ old('prix_base', 10) }}" required min="0"
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-4">
                    <a href="{{ route('admin.ressources.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Annuler</a>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Creer</button>
                </div>
            </form>
        </div>\n@endsection
