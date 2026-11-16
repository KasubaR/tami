@extends('layouts.app')
@section('title', 'Happy Birthday!')

@section('content')
    <section class="flex flex-col items-center gap-6 text-center">
        <div class="animate-float text-champagne-500" aria-hidden="true"><x-icon name="wine" :size="96" /></div>

        <div class="reveal-in">
            <p class="font-display text-sm font-bold uppercase tracking-[0.25em] text-blush-500">You solved all eleven!</p>
            <h1 class="mt-2 text-4xl font-bold text-champagne-600 sm:text-5xl">
                Happy Birthday,<br>{{ config('game.recipient') }}!
            </h1>
        </div>

        {{-- All 11 gifts --}}
        <div class="w-full">
            <h2 class="mb-3 text-xl font-bold text-champagne-600">Your eleven gifts</h2>
            <ol class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($gifts as $i => $gift)
                    <li class="card reveal-in flex flex-col items-center gap-1 !p-3" style="animation-delay: {{ 0.15 * $i }}s">
                        <span class="text-champagne-500"><x-icon :name="$gift->icon ?? 'gift'" :size="32" /></span>
                        <span class="font-display font-bold">{{ $gift->name }}</span>
                        <span class="text-xs text-ink-soft">{{ $gift->reveal_heading }}</span>
                    </li>
                @endforeach
            </ol>
        </div>

        {{-- Final message and personal closing --}}
        <div class="card w-full max-w-lg space-y-4 text-lg leading-relaxed">
            <p class="whitespace-pre-line">{{ $message }}</p>
            <hr class="border-champagne-200">
            <p class="text-ink-soft">{{ config('game.closing') }}</p>
            <p class="text-right font-display italic text-champagne-600">
                {{ config('game.sign_off') }} <x-icon name="heart" :size="18" class="inline-block align-[-3px] text-blush-500" />
            </p>
        </div>

        <div class="flex flex-wrap justify-center gap-3">
            <button type="button" id="celebrate-again" class="btn-primary"><x-icon name="party-popper" :size="20" /> Celebrate again</button>
            <button type="button" id="play-music" class="btn-secondary"><x-icon name="music" :size="20" /> Play birthday tune</button>
        </div>
        <div class="flex gap-4 text-sm">
            <x-sound-toggle />
            <a href="{{ url('/play') }}" class="text-ink-soft underline-offset-4 hover:underline">Back to my gifts</a>
        </div>
    </section>
@endsection

@push('scripts')
    @vite('resources/js/finale.js')
@endpush
