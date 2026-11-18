@extends('layouts.app')
@section('title', 'Puzzle '.$level->level_number)

@section('header')
    <x-game-header :title="'Puzzle '.$level->level_number" :back="url('/play')" />
@endsection

@section('content')
    @if (empty($puzzle['words']))
        <div class="card text-center text-ink-soft">
            <p class="text-lg">This puzzle isn't ready yet. Check back soon!</p>
        </div>
    @else
        <div id="crossword"
             data-check-url="{{ url('/play/'.$level->level_number.'/check') }}"
             data-storage-key="birthday-mystery-puzzle-{{ $level->level_number }}"
             class="flex flex-col gap-4">
            <script type="application/json" id="puzzle-data">@json($puzzle)</script>

            {{-- Current clue --}}
            <div class="card flex items-center gap-3 !py-3" aria-live="polite">
                <span id="cw-clue-label" class="shrink-0 rounded-full bg-champagne-100 px-3 py-1 text-sm font-bold text-champagne-600"></span>
                <span id="cw-clue-text" class="font-semibold">Tap a square to begin</span>
            </div>

            {{-- Grid (built by crossword.js) --}}
            <div class="card !p-3">
                <div id="cw-grid" class="mx-auto grid w-full gap-0.5" role="application" aria-label="Crossword grid"></div>
            </div>

            <p id="cw-message" class="min-h-6 text-center font-semibold text-ink-soft" role="status"></p>

            {{-- On-screen keyboard --}}
            <div id="cw-keyboard" class="select-none touch-none space-y-1.5" aria-label="Keyboard">
                @php
                    // Keep the familiar QWERTY order, but only for letters this puzzle uses.
                    $rows = collect(['QWERTYUIOP', 'ASDFGHJKL', 'ZXCVBNM'])
                        ->map(fn ($row) => array_values(array_filter(str_split($row), fn ($k) => in_array($k, $puzzle['letters'], true))))
                        ->filter()
                        ->values();
                    $lastRow = $rows->count() - 1;
                @endphp
                @foreach ($rows as $i => $keys)
                    <div class="flex justify-center gap-1.5">
                        @foreach ($keys as $key)
                            <button type="button" data-key="{{ $key }}"
                                    class="h-12 min-w-0 max-w-14 flex-1 rounded-lg border border-champagne-200 bg-white text-lg font-bold shadow-sm active:bg-champagne-200 sm:h-14">{{ $key }}</button>
                        @endforeach
                        @if ($i === $lastRow)
                            <button type="button" data-key="Backspace" aria-label="Backspace"
                                    class="grid h-12 max-w-20 flex-[1.5] place-items-center rounded-lg border border-champagne-300 bg-champagne-100 text-ink active:bg-champagne-200 sm:h-14"><x-icon name="delete" :size="22" /></button>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex gap-2.5">
                <button type="button" id="cw-clear"
                        class="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-2xl border border-champagne-300 bg-white/80 px-4 text-[15px] font-bold text-champagne-600 shadow-sm transition hover:bg-champagne-50 active:scale-[0.97] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-champagne-500">
                    <x-icon name="rotate-ccw" :size="18" /> Clear word
                </button>
                <button type="button" id="cw-check"
                        class="inline-flex h-12 flex-[1.4] items-center justify-center gap-2 rounded-2xl bg-[#8A4FA8] px-4 text-[15px] font-bold text-white shadow-[0_10px_24px_-10px_rgb(138_79_168/0.6)] transition hover:bg-[#7E4A9E] active:scale-[0.97] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C3A3DA] disabled:opacity-60">
                    <x-icon name="check" :size="18" /> Check answers
                </button>
            </div>

            <div class="text-center"><x-sound-toggle /></div>

            {{-- Clue lists --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="card">
                    <h2 class="mb-2 text-lg font-bold text-champagne-600">Across</h2>
                    <ul id="cw-clues-across" class="space-y-1"></ul>
                </div>
                <div class="card">
                    <h2 class="mb-2 text-lg font-bold text-champagne-600">Down</h2>
                    <ul id="cw-clues-down" class="space-y-1"></ul>
                </div>
            </div>
        </div>

        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endif
@endsection

@push('scripts')
    @vite('resources/js/crossword.js')
@endpush
