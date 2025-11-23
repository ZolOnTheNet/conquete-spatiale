@extends('layouts.app')

@section('title', 'Accès restreint')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full">
        <div class="panel p-8 text-center">
            <div class="text-6xl mb-4">🚫</div>
            
            <h1 class="text-2xl font-orbitron text-red-400 mb-4">Accès restreint</h1>
            
            <p class="text-gray-300 mb-6">
                {{ $message ?? 'Vous ne pouvez pas accéder à cette fonctionnalité depuis votre localisation actuelle.' }}
            </p>
            
            <div class="bg-gray-800/50 border border-gray-700 rounded p-4 mb-6">
                <div class="text-sm text-gray-400 mb-2">Localisation actuelle:</div>
                <div class="text-cyan-400 font-bold">
                    @if(isset($currentLocation))
                        {{ ucfirst($currentLocation) }}
                    @else
                        Inconnue
                    @endif
                </div>
                
                @if(isset($requiredLocation))
                <div class="text-sm text-gray-400 mt-3 mb-2">Localisation requise:</div>
                <div class="text-yellow-400 font-bold">
                    {{ ucfirst($requiredLocation) }}
                </div>
                @endif
            </div>
            
            <a href="{{ route('dashboard') }}" class="btn-primary inline-block">
                ← Retour au tableau de bord
            </a>
        </div>
    </div>
</div>
@endsection
