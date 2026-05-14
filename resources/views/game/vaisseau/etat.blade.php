@extends('layouts.game-hud')

@section('title', 'Ingénierie')

@section('hud-content')
<div class="hud-page-title">État du Vaisseau</div>

@if(isset($partial))
    @include('game.vaisseau.partials.etat')
@else
<div class="hud-panel">
    <div class="hud-panel-title">Diagnostic</div>
    <p style="color:var(--text-secondary);">Informations sur l'état du vaisseau</p>
    <p style="margin-top:8px;font-family:var(--mono);font-size:11px;color:var(--text-muted);">À implémenter selon GDD</p>
</div>
@endif
@endsection
