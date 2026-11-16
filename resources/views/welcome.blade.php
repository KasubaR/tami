@extends('layouts.app')

@section('content')
    <section class="flex min-h-[70dvh] flex-col items-center justify-center gap-6 text-center">
        <div class="animate-float text-8xl" aria-hidden="true">🍾</div>
        <h1 class="text-4xl font-bold text-champagne-600 sm:text-5xl">The Birthday Mystery</h1>
        <p class="max-w-md text-lg text-ink-soft">
            Eleven puzzles. Eleven surprises. One unforgettable birthday.
        </p>
        <a href="{{ url('/play') }}" class="btn-primary text-lg">Begin 🎀</a>
    </section>
@endsection
