@extends('layouts.app')
@section('title', 'Happy Birthday, '.config('game.recipient').'!')

@section('content')
    {{-- Decorative floating balloons and sparkles --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <span class="absolute left-[6%] top-[3%] animate-float text-5xl">🎈</span>
        <span class="absolute right-[6%] top-[3%] animate-float text-4xl [animation-delay:1s]">🎀</span>
        <span class="absolute left-[14%] top-[60%] animate-float text-3xl [animation-delay:2s]">✨</span>
        <span class="absolute right-[8%] top-[68%] animate-float text-5xl [animation-delay:.5s]">🎈</span>
        <span class="absolute left-[45%] top-[6%] animate-float text-3xl [animation-delay:1.5s]">✨</span>
    </div>

    <section class="flex min-h-[78dvh] flex-col items-center justify-center gap-5 text-center">
        <div class="animate-float text-7xl" aria-hidden="true">🍾</div>
        <h1 class="text-4xl font-bold leading-tight text-champagne-600 sm:text-5xl">
            Happy Birthday,<br><span class="text-blush-500">{{ config('game.recipient') }}!</span>
        </h1>

        <div class="card max-w-md space-y-3 text-left text-lg leading-relaxed">
            <p>{{ config('game.intro') }}</p>
            <p class="text-ink-soft">{{ config('game.how_it_works') }}</p>
            <p class="text-right font-display italic text-champagne-600">{{ config('game.sign_off') }} 💕</p>
        </div>

        <p class="font-display text-lg italic text-ink-soft">Eleven puzzles. Eleven surprises. One unforgettable birthday.</p>

        <a href="{{ url('/play') }}" class="btn-primary text-lg">
            {{ $started ? 'Continue My Adventure' : 'Start My Adventure' }} 🎀
        </a>
    </section>
@endsection
