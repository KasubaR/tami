@extends('layouts.app')
@section('title', 'A gift for you')

@section('content')
    <section id="gift-stage" data-clip-url="{{ asset('sfx/level-clear.weba') }}" class="flex flex-col items-center gap-5 text-center">

        {{-- Steps 1-4: congratulations and a closed gift box --}}
        <div id="gift-closed" class="flex flex-col items-center gap-5">
            <p class="font-semibold text-ink-soft">Puzzle {{ $level->level_number }} complete</p>
            <h1 class="text-4xl font-bold text-champagne-600">
                You did it, {{ config('game.recipient') }}!
                <x-icon name="party-popper" :size="36" class="inline-block align-[-4px]" />
            </h1>
            <p class="text-ink-soft">A gift is waiting for you. Tap the box to open it.</p>

            <button type="button" id="gift-box" class="gift-box" aria-label="Open your gift">
                <span class="gift-glow" aria-hidden="true"></span>
                <span class="gift-body" aria-hidden="true"></span>
                <span class="gift-lid" aria-hidden="true"></span>
                <span class="gift-bow text-champagne-600" aria-hidden="true"><x-icon name="ribbon" :size="48" /></span>
            </button>
        </div>

        {{-- Steps 5-8: the reveal --}}
        <div id="gift-opened" hidden class="flex w-full flex-col items-center gap-4">
            <p class="font-display text-sm font-bold uppercase tracking-[0.25em] text-blush-500">{{ $gift->reveal_heading }}</p>

            @if ($gift->image)
                <img src="{{ asset($gift->image) }}" alt="{{ $gift->name }}"
                     class="h-44 w-44 rounded-3xl object-cover shadow-soft">
            @else
                <div class="grid h-36 w-36 place-items-center rounded-full bg-white/80 text-champagne-500 shadow-soft">
                    <x-icon :name="$gift->icon ?? 'gift'" :size="72" />
                </div>
            @endif

            <h2 data-focus tabindex="-1" class="text-4xl font-bold uppercase tracking-wide text-champagne-600 outline-none">
                {{ $gift->name }}
            </h2>
            <p class="card w-full max-w-md text-lg">{{ $gift->reveal_message }}</p>

            <a href="{{ $nextUrl }}" id="gift-next" class="btn-primary text-lg">
                @if ($isLast)
                    The final surprise <x-icon name="wine" :size="20" />
                @else
                    Next challenge <x-icon name="arrow-right" :size="20" />
                @endif
            </a>
            <div class="flex gap-3 text-sm">
                <button type="button" id="gift-replay" class="btn-secondary !min-h-10 !px-4 !py-2"><x-icon name="rotate-ccw" :size="16" /> Replay</button>
                <a href="{{ url('/play') }}" class="btn-secondary !min-h-10 !px-4 !py-2">All puzzles</a>
            </div>
        </div>

        <x-sound-toggle class="mt-2" />
    </section>
@endsection

@push('scripts')
    @vite('resources/js/reveal.js')
@endpush
