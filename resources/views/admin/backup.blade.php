@extends('layouts.admin')

@section('title', 'Admin - Backup')

@section('admin-title', 'ADMINISTRATION')

@section('admin-content')
<h2 class="text-xl font-bold text-white mb-6">Gestion des Sauvegardes</h2>

            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-bold text-white mb-4">Actions</h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-900/50 rounded">
                        <div>
                            <div class="text-white font-bold">Exporter la base de donnees</div>
                            <div class="text-sm text-gray-500">Telecharger un backup SQLite</div>
                        </div>
                        <button class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded text-sm">
                            Exporter
                        </button>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-900/50 rounded">
                        <div>
                            <div class="text-white font-bold">Reinitialiser la base</div>
                            <div class="text-sm text-gray-500">Attention: supprime toutes les donnees</div>
                        </div>
                        <button class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded text-sm">
                            Reinitialiser
                        </button>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-900/50 rounded">
                        <div>
                            <div class="text-white font-bold">Regenerer l'univers</div>
                            <div class="text-sm text-gray-500">Executer les seeders</div>
                        </div>
                        <button class="bg-yellow-600 hover:bg-yellow-500 text-white px-4 py-2 rounded text-sm">
                            Regenerer
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-bold text-white mb-4">Informations Base de Donnees</h3>

                <div class="text-sm text-gray-300 space-y-2">
                    <p><span class="text-gray-500">Type:</span> SQLite</p>
                    <p><span class="text-gray-500">Fichier:</span> database/database.sqlite</p>
                    <p><span class="text-gray-500">Mode generation:</span> {{ config('universe.generation_mode', 'hybrid') }}</p>
                </div>
            </div>
@endsection
