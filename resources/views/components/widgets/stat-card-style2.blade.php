@props([
    'title',
    'value',
    'color' => 'primary',
    'subtitle' => 'Increases Today',
    'chartId' => ''
])

<div class="card custom-card ">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div class="mb-2">
                <span class="avatar avatar-md bg-{{ $color }}-transparent svg-{{ $color }}">
                    {{ $icon ?? '' }}
                </span>
            </div>
            <span class="fs-16">{{ $title }}</span>
        </div>
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="fs-20 fw-medium mb-0 d-flex align-items-center">{{ $value }}</span>
                <span class="fs-13 text-muted">{{ $subtitle }}</span>
            </div>
            @if($chartId)
                <div id="{{ $chartId }}"></div>
            @endif
        </div>
    </div>
</div>
