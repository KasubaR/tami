@props(['title' => 'The Birthday Mystery', 'back' => null])

<header class="flex items-center justify-between gap-3 py-4">
    @if ($back)
        <a href="{{ $back }}" class="btn-secondary !min-h-10 !px-4 !py-2 text-sm" aria-label="Back">← Back</a>
    @else
        <span aria-hidden="true">🎀</span>
    @endif
    <h1 class="text-center text-xl font-bold text-champagne-600">{{ $title }}</h1>
    <span class="w-16 text-right" aria-hidden="true">🥂</span>
</header>
