@extends('layouts.admin')

@section('title', 'Admin - Modifier Mine')

@section('admin-title', 'MODIFIER MINE')

@section('admin-content')

        <div class="max-w-2xl mx-auto bg-gray-800/50 border border-gray-700 rounded-lg p-6">
            <form action="{{ route('admin.mines-admin.update', $mine) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nom</label>
                        <input type="text" name="nom" value="{{ old('nom', $mine->nom) }}" required
                            class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Planete</label>
                        <select name="planete_id" required class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            @foreach($planetes as $planete)
                                <option value="{{ $planete->id }}" {{ $mine->planete_id == $planete->id ? 'selected' : '' }}>
                                    {{ $planete->nom }} ({{ $planete->systemeStellaire->nom ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Gisement</label>
                        <select name="gisement_id" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            <option value="">-- Aucun --</option>
                            @foreach($gisements as $gisement)
                                <option value="{{ $gisement->id }}" {{ $mine->gisement_id == $gisement->id ? 'selected' : '' }}>
                                    {{ $gisement->ressource->nom ?? 'Ressource' }} - {{ number_format($gisement->quantite_restante) }} restants
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Proprietaire</label>
                        <select name="proprietaire_id" class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                            <option value="">-- Aucun --</option>
                            @foreach($personnages as $personnage)
                                <option value="{{ $personnage->id }}" {{ $mine->proprietaire_id == $personnage->id ? 'selected' : '' }}>
                                    {{ $personnage->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Capacite stockage</label>
                            <input type="number" name="capacite_stockage" value="{{ old('capacite_stockage', $mine->capacite_stockage) }}" required
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Taux extraction</label>
                            <input type="number" step="0.01" name="taux_extraction" value="{{ old('taux_extraction', $mine->taux_extraction) }}" required
                                class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Statut</label>
                            <select name="statut" required class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                                <option value="inactive" {{ $mine->statut == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="active" {{ $mine->statut == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="maintenance" {{ $mine->statut == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="endommagee" {{ $mine->statut == 'endommagee' ? 'selected' : '' }}>Endommagee</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Emplacement</label>
                            <select name="emplacement" required class="w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-white">
                                <option value="surface" {{ $mine->emplacement == 'surface' ? 'selected' : '' }}>Surface</option>
                                <option value="orbite" {{ $mine->emplacement == 'orbite' ? 'selected' : '' }}>Orbite</option>
                            </select>
                        </div>
                    </div>

                    <!-- Info usure -->
                    <div class="bg-gray-900/50 border border-gray-700 rounded p-4">
                        <h3 class="text-sm font-bold text-gray-300 mb-2">Etat de la mine</h3>
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Usure :</span>
                                <span class="text-white">{{ $mine->niveau_usure ?? 0 }}%</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Stock actuel :</span>
                                <span class="text-white">{{ number_format($mine->stock_actuel ?? 0) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Energie :</span>
                                <span class="text-white">{{ number_format($mine->stock_energie ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-4">
                    <a href="{{ route('admin.mines-admin.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Annuler</a>
                    <button type="submit" class="px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700">Sauvegarder</button>
                </div>
            </form>
        </div>\n@endsection
