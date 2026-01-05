@extends('layouts.admin')

@section('title', 'Admin - Nouvelle Station')

@section('admin-title', 'NOUVELLE STATION')

@section('admin-content')

        <div class="max-w-2xl mx-auto bg-gray-800/50 border border-gray-700 rounded-lg p-6">
            <form action="{{ route('admin.stations.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nom</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required
                            class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Type</label>
                            <select name="type" required class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                                <option value="orbitale">Orbitale</option>
                                <option value="spatiale">Spatiale</option>
                                <option value="miniere">Miniere</option>
                                <option value="militaire">Militaire</option>
                                <option value="commerciale">Commerciale</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Capacite amarrage</label>
                            <input type="number" name="capacite_amarrage" value="{{ old('capacite_amarrage', 10) }}" required
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Population</label>
                            <input type="number" name="population" value="{{ old('population', 0) }}" min="0"
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="gere_admin" value="1" {{ old('gere_admin') ? 'checked' : '' }} class="rounded bg-gray-900 border-gray-600">
                                <span class="text-gray-300 text-sm">Gérée par admin (pas de consommation énergie/personnel)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Systeme stellaire</label>
                        <select name="systeme_stellaire_id" required class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            @foreach($systemes as $systeme)
                                <option value="{{ $systeme->id }}">{{ $systeme->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Planete (optionnel)</label>
                        <select name="planete_id" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            <option value="">-- Aucune (station libre) --</option>
                            @foreach($planetes as $planete)
                                <option value="{{ $planete->id }}">{{ $planete->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Faction (optionnel)</label>
                        <select name="faction_id" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            <option value="">-- Neutre --</option>
                            @foreach($factions as $faction)
                                <option value="{{ $faction->id }}">{{ $faction->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="border-t border-gray-700 pt-4 mt-4">
                        <h3 class="text-sm font-bold text-gray-300 mb-3">Services disponibles</h3>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="accessible" value="1" checked class="rounded bg-gray-900 border-gray-600">
                                <span class="text-gray-300 text-sm">Accessible</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="commerciale" value="1" class="rounded bg-gray-900 border-gray-600">
                                <span class="text-gray-300 text-sm">Commerciale</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="ravitaillement" value="1" class="rounded bg-gray-900 border-gray-600">
                                <span class="text-gray-300 text-sm">Ravitaillement</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="reparations" value="1" class="rounded bg-gray-900 border-gray-600">
                                <span class="text-gray-300 text-sm">Reparations</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="medical" value="1" class="rounded bg-gray-900 border-gray-600">
                                <span class="text-gray-300 text-sm">Medical</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="industrielle" value="1" class="rounded bg-gray-900 border-gray-600">
                                <span class="text-gray-300 text-sm">Industrielle</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="militaire" value="1" class="rounded bg-gray-900 border-gray-600">
                                <span class="text-gray-300 text-sm">Militaire</span>
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-gray-700 pt-4 mt-4">
                        <h3 class="text-sm font-bold text-gray-300 mb-3">Mines associées ({{ $mines->count() }} disponible(s))</h3>
                        @if($mines->count() > 0)
                            <div class="bg-gray-900/50 border border-gray-700 rounded p-3 max-h-60 overflow-y-auto">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($mines as $mine)
                                        <label class="flex items-start gap-2 p-2 hover:bg-gray-700/30 rounded">
                                            <input type="checkbox" name="mines[]" value="{{ $mine->id }}"
                                                {{ in_array($mine->id, old('mines', [])) ? 'checked' : '' }}
                                                class="mt-1 rounded bg-gray-900 border-gray-600">
                                            <div class="flex-1 text-sm">
                                                <div class="text-white font-medium">{{ $mine->nom }}</div>
                                                <div class="text-xs text-gray-400">
                                                    {{ $mine->planete->nom ?? 'N/A' }} -
                                                    {{ $mine->gisement->ressource->nom ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Aucune mine disponible</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-4">
                    <a href="{{ route('admin.stations.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Annuler</a>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Creer</button>
                </div>
            </form>
        </div>
@endsection
