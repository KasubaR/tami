@props(['title' => 'The Birthday Mystery', 'back' => null])

<header class="flex items-center justify-between gap-3 py-4">
    @if ($back)
        <a href="{{ $back }}" class="btn-secondary !min-h-10 !px-4 !py-2 text-sm" aria-label="Back"><x-icon name="arrow-left" :size="16" /> Back</a>
    @else
        <span class="text-champagne-500"><x-icon name="ribbon" :size="24" /></span>
    @endif
    <h1 class="text-center text-xl font-bold text-champagne-600">{{ $title }}</h1>
    <span class="flex w-16 justify-end text-champagne-500"><x-icon name="wine" :size="24" /></span>
</header>
