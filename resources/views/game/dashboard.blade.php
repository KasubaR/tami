@extends('layouts.app')
@section('title', 'The Birthday Mystery')

@section('content')
    <section class="mb-6 text-center">
        <div class="flex justify-center text-champagne-500"><x-icon name="wine" :size="40" /></div>
        <h1 class="mt-2 text-3xl font-bold text-champagne-600">The Birthday Mystery</h1>
        <p class="mt-1 text-lg text-ink-soft">Happy Birthday, <span class="font-semibold text-blush-500">{{ $recipient }}</span></p>
    </section>

    <div class="card mb-6">
        <x-progress-bar :completed="$completed" :total="$total" />
        <p class="mt-3 flex items-center justify-center gap-1.5 text-center text-sm text-ink-soft">
            <x-icon name="gift" :size="16" class="text-champagne-500" />
            <span><span class="font-bold text-champagne-600">{{ $completed }}</span> of {{ $total }} gifts unlocked</span>
        </p>
        <div class="mt-4 text-center">
            <a href="{{ $continueUrl }}" class="btn-primary w-full sm:w-auto">
                @if ($allDone)
                    Open the final surprise <x-icon name="arrow-right" :size="18" />
                @elseif ($completed === 0)
                    Start playing
                @else
                    Continue playing · Puzzle {{ $current }}
                @endif
            </a>
        </div>
    </div>

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
@endsection
