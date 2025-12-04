@props([
    'id' => null,
    'headers' => [],
    'bordered' => true,
    'striped' => false,
    'hover' => false,
    'responsive' => true
])

@php
    $classes = 'table text-nowrap w-100';
    if($bordered) $classes .= ' table-bordered';
    if($striped) $classes .= ' table-striped';
    if($hover) $classes .= ' table-hover';
@endphp

@if($responsive) <div class="table-responsive"> @endif
    <table {{ $attributes->merge(['class' => $classes, 'id' => $id]) }}>
        <thead>
            @if(isset($thead))
                {{ $thead }}
            @else
                <tr>
                    @foreach($headers as $header)
                        <th scope="col">{{ $header }}</th>
                    @endforeach
                </tr>
            @endif
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
@if($responsive) </div> @endif
