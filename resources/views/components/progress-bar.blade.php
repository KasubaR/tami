@props(['completed' => 0, 'total' => 11])

@php $percent = $total > 0 ? round($completed / $total * 100) : 0; @endphp

<div {{ $attributes }}>
    <div class="mb-1 flex justify-between text-sm font-semibold text-ink-soft">
        <span>Gifts unlocked</span>
        <span>{{ $completed }} / {{ $total }}</span>
    </div>
    <div class="h-3 overflow-hidden rounded-full bg-champagne-100" role="progressbar"
         aria-valuenow="{{ $completed }}" aria-valuemin="0" aria-valuemax="{{ $total }}">
        <div class="h-full rounded-full bg-linear-to-r from-blush-300 to-champagne-400 transition-all duration-700"
             style="width: {{ $percent }}%"></div>
    </div>
</div>
