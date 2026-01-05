@extends('layouts.admin')

@section('title', 'Admin - Nouveau Gisement')

@section('admin-title', 'NOUVEAU GISEMENT')

@section('admin-content')

        <div class="max-w-2xl mx-auto bg-gray-800/50 border border-gray-700 rounded-lg p-6">
            <form action="{{ route('admin.gisements.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
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
                        <label class="block text-sm font-medium text-gray-300 mb-1">Ressource</label>
                        <select name="ressource_id" required class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            <option value="">-- Selectionner --</option>
                            @foreach($ressources as $ressource)
                                <option value="{{ $ressource->id }}">{{ $ressource->nom }} ({{ $ressource->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Quantite totale</label>
                            <input type="number" name="quantite_totale" value="{{ old('quantite_totale', 1000000) }}" required min="1"
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Richesse (1-100)</label>
                            <input type="number" name="richesse" value="{{ old('richesse', 50) }}" required min="1" max="100"
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            <p class="text-xs text-gray-500 mt-1">Affecte le rendement d'extraction</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Latitude (optionnel)</label>
                            <input type="number" step="0.01" name="latitude" value="{{ old('latitude') }}"
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white"
                                placeholder="-90 a 90">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Longitude (optionnel)</label>
                            <input type="number" step="0.01" name="longitude" value="{{ old('longitude') }}"
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white"
                                placeholder="-180 a 180">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-4">
                    <a href="{{ route('admin.gisements.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Annuler</a>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Creer</button>
                </div>
            </form>
        </div>\n@endsection
