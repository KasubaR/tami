@extends('layouts.app')
@section('title', 'Your Puzzles')

@section('header')
    <x-game-header title="Your Puzzles" />
@endsection

@section('content')
    <x-progress-bar :completed="0" :total="11" class="mb-6" />
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        {{-- Phase 4: loop over puzzles --}}
        @foreach (range(1, 11) as $n)
            <x-gift-card :number="$n" :state="$n === 1 ? 'available' : 'locked'" />
        @endforeach
    </div>
@endsection
