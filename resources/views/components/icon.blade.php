@props(['name'])
@php
    $paths = [
        'add' => '<path d="M12 5v14M5 12h14"/>',
        'upload' => '<path d="M12 16V4m0 0-4 4m4-4 4 4M5 14v5h14v-5"/>',
        'download' => '<path d="M12 4v12m0 0-4-4m4 4 4-4M5 19h14"/>',
        'search' => '<circle cx="11" cy="11" r="6"/><path d="m16 16 4 4"/>',
        'edit' => '<path d="m4 16.5-.5 4 4-.5L19 8.5 15.5 5 4 16.5ZM14.5 6l3.5 3.5"/>',
        'delete' => '<path d="M4 7h16M10 11v6m4-6v6M9 7l1-2h4l1 2m-8 0 1 13h8l1-13"/>',
        'print' => '<path d="M7 9V4h10v5M7 17H5V10h14v7h-2M7 14h10v6H7z"/>',
        'save' => '<path d="M5 4h12l2 2v14H5V4Zm3 0v6h7V4m-7 16v-6h8v6"/>',
        'cancel' => '<path d="m6 6 12 12M18 6 6 18"/>',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'icon', 'viewBox' => '0 0 24 24', 'aria-hidden' => 'true']) }} fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $paths[$name] ?? '' !!}</svg>
