@extends('layouts.admin')

@section('title', 'Admin - Nouvelle Mine')

@section('admin-title', 'NOUVELLE MINE')

@section('admin-content')

        <div class="max-w-2xl mx-auto bg-gray-800/50 border border-gray-700 rounded-lg p-6">
            <form action="{{ route('admin.mines-admin.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nom</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required
                            class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white focus:border-cyan-500 focus:outline-none">
                        @error('nom')<span class="text-red-400 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Planete</label>
                        <select name="planete_id" required class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            <option value="">-- Selectionner --</option>
                            @foreach($planetes as $planete)
                                <option value="{{ $planete->id }}">{{ $planete->nom }} ({{ $planete->systemeStellaire->nom ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Proprietaire (optionnel)</label>
                        <select name="proprietaire_id" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            <option value="">-- Aucun --</option>
                            @foreach($personnages as $personnage)
                                <option value="{{ $personnage->id }}">{{ $personnage->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Capacite stockage</label>
                            <input type="number" name="capacite_stockage" value="{{ old('capacite_stockage', 10000) }}" required
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Taux extraction (u/jour)</label>
                            <input type="number" step="0.01" name="taux_extraction" value="{{ old('taux_extraction', 100) }}" required
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Statut</label>
                            <select name="statut" required class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                                <option value="inactive">Inactive</option>
                                <option value="active">Active</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Emplacement</label>
                            <select name="emplacement" required class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                                <option value="surface">Surface</option>
                                <option value="orbite">Orbite</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Modele (optionnel)</label>
                        <input type="text" name="modele" value="{{ old('modele') }}"
                            class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-4">
                    <a href="{{ route('admin.mines-admin.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Annuler</a>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Creer</button>
                </div>
            </form>
        </div>\n@endsection
