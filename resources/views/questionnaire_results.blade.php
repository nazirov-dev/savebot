@php
    $sortedResults = collect($results)->sortByDesc('votes_count');
@endphp

@forelse ($sortedResults as $result)
    {{ $result['variant'] }} -
    {{ number_format($result['percentage'], 2) }}%
    ({{ $result['votes_count'] }} kishi)
    <br>
@empty
    Variantlar mavjud emas!
@endforelse
