@props([
    'title',
    'value',
    'color' => 'primary',
    'percentage' => '',
    'percentageColor' => 'text-success',
    'subtitle' => 'This Week'
])

<div class="text-center">
    <div class="lh-1 mb-3">
        <span class="avatar avatar-lg bg-{{ $color }}-transparent avatar-rounded">
            <span class="avatar avatar-sm bg-{{ $color }} avatar-rounded svg-white">
                 {{ $icon ?? '' }}
            </span>
        </span>
    </div>
    <span class="d-block mb-1">{{ $title }}</span>
    <h5 class="fw-semibold">{{ $value }}</h5>
    <div class="fs-13"><span class="{{ $percentageColor }} me-1">{{ $percentage }}</span> {{ $subtitle }}</div>
</div>
