{{-- Wired up by sound.js (bindSoundToggle): shows the "on" or "off" half. --}}
<button type="button" data-sound-toggle aria-pressed="false" {{ $attributes->class('inline-flex items-center gap-1.5 text-sm text-ink-soft underline-offset-4 hover:underline') }}>
    <span data-sound-on hidden class="inline-flex items-center gap-1.5"><x-icon name="volume-2" :size="16" /> Sound on</span>
    <span data-sound-off class="inline-flex items-center gap-1.5"><x-icon name="volume-x" :size="16" /> Sound off</span>
</button>
