@extends('layouts.game-hud')

@section('title', 'Ingénierie')

@section('hud-content')
@include('game.vaisseau.partials.etat')
@endsection
