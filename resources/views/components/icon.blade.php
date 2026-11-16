@props(['name', 'size' => 24])
{{-- Inline SVG from resources/svg (Lucide icons, ISC licence). Colour follows the surrounding text colour. --}}
@php
    $file = resource_path('svg/'.preg_replace('/[^a-z0-9-]/', '', (string) $name).'.svg');
    $svg = is_file($file) ? file_get_contents($file) : '';
    $svg = preg_replace('/<!--.*?-->/s', '', $svg);
    $svg = preg_replace('/\s(class|width|height)="[^"]*"/', '', $svg);
    $svg = preg_replace(
        '/<svg/',
        '<svg width="'.(int) $size.'" height="'.(int) $size.'" class="'.e($attributes->get('class')).'" aria-hidden="true" focusable="false"',
        trim($svg),
        1
    );
@endphp
{!! $svg !!}
