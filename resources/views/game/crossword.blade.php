@extends('layouts.app')
@section('title', 'Puzzle '.$level->level_number)

@section('header')
    <x-game-header :title="'Puzzle '.$level->level_number" :back="url('/play')" />
@endsection

@section('content')
    @if (empty($puzzle['words']))
        <div class="card text-center text-ink-soft">
            <p class="text-lg">This puzzle isn't ready yet. Check back soon! 🎀</p>
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
            <div id="cw-keyboard" class="select-none space-y-1.5" aria-label="Keyboard">
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
                                    class="h-12 max-w-20 flex-[1.5] rounded-lg border border-champagne-300 bg-champagne-100 font-bold active:bg-champagne-200 sm:h-14">⌫</button>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex gap-3">
                <button type="button" id="cw-clear" class="btn-secondary flex-1">Clear word</button>
                <button type="button" id="cw-check" class="btn-primary flex-1">Check answers</button>
            </div>

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
