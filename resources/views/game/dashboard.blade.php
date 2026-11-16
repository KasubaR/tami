@extends('layouts.app')
@section('title', 'Your Puzzles')

@section('header')
    <x-game-header title="Your Puzzles" />
@endsection

@section('content')
    <x-progress-bar :completed="$completed" :total="$total" class="mb-6" />

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        @foreach ($levels as $level)
            @if ($level['state'] === 'locked')
                <x-gift-card :number="$level['number']" state="locked" />
            @else
                <a href="{{ url('/play/'.$level['number'].($level['state'] === 'completed' ? '/gift' : '')) }}"
                   class="block rounded-3xl focus-visible:outline-2 focus-visible:outline-champagne-500">
                    <x-gift-card :number="$level['number']" :state="$level['state']" :title="$level['gift']" class="h-full" />
                </a>
            @endif
        @endforeach
    </div>

    @if ($allDone)
        <div class="mt-8 text-center">
            <a href="{{ url('/finale') }}" class="btn-primary text-lg">Open the final surprise 🥂</a>
        </div>
    @endif
@endsection
