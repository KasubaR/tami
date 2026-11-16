@props(['number', 'title' => null, 'state' => 'locked']) {{-- locked | available | completed --}}

<div {{ $attributes->class([
    'card flex flex-col items-center gap-2 text-center',
    'opacity-60' => $state === 'locked',
    'ring-2 ring-champagne-400' => $state === 'available',
]) }}>
    <span class="text-3xl" aria-hidden="true">
        {{ match ($state) { 'locked' => '🔒', 'available' => '🎁', default => '✨' } }}
    </span>
    <span class="text-sm font-semibold text-ink-soft">Puzzle {{ $number }}</span>
    @if ($state === 'completed' && $title)
        <span class="font-display font-bold text-champagne-600">{{ $title }}</span>
    @endif
    {{ $slot }}
</div>
