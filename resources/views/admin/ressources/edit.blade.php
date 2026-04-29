@extends('layouts.admin')

@section('title', 'Admin - Modifier Ressource')

@section('admin-title', 'MODIFIER RESSOURCE')

@section('admin-content')

        <div class="max-w-2xl mx-auto bg-gray-800/50 border border-gray-700 rounded-lg p-6">
            <form action="{{ route('admin.ressources.update', $ressource) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Code</label>
                            <input type="text" name="code" value="{{ old('code', $ressource->code) }}" required maxlength="10"
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white font-mono uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Nom</label>
                            <input type="text" name="nom" value="{{ old('nom', $ressource->nom) }}" required
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Categorie</label>
                        <select name="categorie" required class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            <option value="metaux" {{ $ressource->categorie == 'metaux' ? 'selected' : '' }}>Metaux</option>
                            <option value="gaz" {{ $ressource->categorie == 'gaz' ? 'selected' : '' }}>Gaz</option>
                            <option value="elementaire" {{ $ressource->categorie == 'elementaire' ? 'selected' : '' }}>Elementaire</option>
                            <option value="chimie" {{ $ressource->categorie == 'chimie' ? 'selected' : '' }}>Chimie</option>
                            <option value="exotique" {{ $ressource->categorie == 'exotique' ? 'selected' : '' }}>Exotique</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Rarete (1-100)</label>
                            <input type="number" name="rarete" value="{{ old('rarete', $ressource->rarete) }}" required min="1" max="100"
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Prix de base (Cr)</label>
                            <input type="number" step="0.01" name="prix_base" value="{{ old('prix_base', $ressource->prix_base) }}" required min="0"
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">{{ old('description', $ressource->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-4">
                    <a href="{{ route('admin.ressources.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Annuler</a>
                    <button type="submit" class="px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700">Sauvegarder</button>
                </div>
            </form>
        </div>
    @endsection
