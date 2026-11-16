@extends('layouts.app')
@section('title', 'Crossword')

@section('header')
    <x-game-header title="Crossword" :back="url('/play')" />
@endsection

@section('content')
    <div class="card" id="crossword">
        {{-- Phase 5: grid and clues rendered here --}}
        <p class="text-center text-ink-soft">The crossword goes here.</p>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/crossword.js')
@endpush
