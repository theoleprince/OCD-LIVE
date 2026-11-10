{{-- Logo de moyen de paiement à taille bornée, même sans CSS chargé (les SVG ont une taille native très grande). --}}
@props(['src', 'alt' => ''])
@php
    // [largeur, hauteur] d'affichage maximales en px selon le logo
    $size = match (true) {
        str_contains($src, 'moov-money') => [40, 40],
        str_contains($src, 'mixx') => [70, 28],
        str_contains($src, 'mastercard') => [34, 21],
        str_contains($src, 'visa') => [44, 14],
        default => [56, 28],
    };
@endphp
<img src="{{ asset($src) }}" alt="{{ $alt }}" width="{{ $size[0] }}" height="{{ $size[1] }}" loading="lazy" decoding="async"
    style="width: {{ $size[0] }}px; height: {{ $size[1] }}px; object-fit: contain;"
    @class(['shrink-0', 'rounded-lg' => str_ends_with($src, '.jpg')])>
