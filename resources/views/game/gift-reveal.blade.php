@extends('layouts.app')
@section('title', $gift->reveal_heading)

@section('content')
    <section class="flex flex-col items-center gap-5 text-center">
        <div class="animate-pop text-7xl" aria-hidden="true">🎁</div>
        <p class="font-semibold text-ink-soft">Puzzle {{ $level->level_number }} solved!</p>
        <h1 class="text-3xl font-bold text-champagne-600">{{ $gift->reveal_heading }}</h1>
        <div class="card w-full max-w-md">
            <p class="font-display text-2xl font-bold">{{ $gift->name }}</p>
            <p class="mt-2 text-ink-soft">{{ $gift->reveal_message }}</p>
        </div>
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ url('/play') }}" class="btn-secondary">All puzzles</a>
            <a href="{{ $nextUrl }}" class="btn-primary">{{ $isLast ? 'The final surprise 🥂' : 'Next puzzle →' }}</a>
        </div>
    </section>
@endsection
