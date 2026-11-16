@props(['number', 'title' => null, 'state' => 'locked']) {{-- locked | current | completed --}}

<div {{ $attributes->class([
    'card flex flex-col items-center gap-2 text-center',
    'opacity-60' => $state === 'locked',
    'ring-2 ring-champagne-400 -translate-y-0.5' => $state === 'current',
]) }}>
    <span class="text-champagne-500">
        <x-icon :name="match ($state) { 'locked' => 'lock', 'current' => 'gift', default => 'sparkles' }" :size="32" />
    </span>
    <span class="text-sm font-semibold text-ink-soft">Puzzle {{ $number }}</span>
    @if ($state === 'completed' && $title)
        <span class="font-display font-bold text-champagne-600">{{ $title }}</span>
    @elseif ($state === 'current')
        <span class="rounded-full bg-champagne-100 px-3 py-0.5 text-xs font-bold text-champagne-600">Play now</span>
    @elseif ($state === 'locked')
        <span class="text-xs text-ink-soft">Locked</span>
    @endif
    {{ $slot }}
</div>
