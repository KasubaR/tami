@extends('layouts.app')
@section('title', 'Your Gift')

@section('content')
    <section class="flex flex-col items-center gap-5 text-center">
        <div class="animate-pop text-7xl" aria-hidden="true">🎁</div>
        <h1 class="text-3xl font-bold text-champagne-600">A gift for you!</h1>
        {{-- Phase 6: gift title, message and image --}}
        <a href="{{ url('/play') }}" class="btn-primary">Back to puzzles</a>
    </section>
@endsection
