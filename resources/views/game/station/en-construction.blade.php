@extends('layouts.game-hud')

@section('title', $titre . ' — En construction')

@section('hud-content')
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;text-align:center;padding:40px 20px;">
    <div style="font-size:64px;margin-bottom:24px;">🚧</div>
    <div style="font-family:var(--sans);font-size:26px;font-weight:700;color:var(--warning);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:8px;">{{ $titre }}</div>
    <div style="font-size:16px;color:var(--text-secondary);margin-bottom:6px;">En cours de construction</div>
    <div style="font-family:var(--mono);font-size:11px;color:var(--text-muted);margin-bottom:24px;">Cette fonctionnalité n'est pas encore implémentée.</div>

    <div style="background:var(--bg-panel);border:1px solid var(--border-subtle);padding:16px 24px;max-width:480px;width:100%;text-align:left;margin-bottom:24px;">
        <div style="font-family:var(--mono);font-size:9px;letter-spacing:0.2em;color:var(--data);text-transform:uppercase;margin-bottom:8px;">Prévu :</div>
        <p style="color:var(--text-secondary);font-size:13px;">{{ $description }}</p>
    </div>

    <a href="{{ route('station.menu') }}" style="font-family:var(--mono);font-size:10px;letter-spacing:0.15em;text-transform:uppercase;padding:8px 20px;background:transparent;color:var(--data);border:1px solid var(--data);text-decoration:none;transition:all 0.15s;" onmouseover="this.style.background='rgba(127,212,255,0.1)'" onmouseout="this.style.background='transparent'">
        ← Retour au menu de la station
    </a>
</div>
@endsection
