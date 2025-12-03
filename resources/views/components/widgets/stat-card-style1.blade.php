@props([
    'title',
    'value',
    'icon',
    'color' => 'primary',
    'badgeText' => '',
    'percentage' => '',
    'percentageColor' => 'text-success',
    'valueId' => ''
])

<div class="card custom-card widget-card-style1 {{ $color }}">
    <div class="card-body">
        <div class="d-flex align-items-start gap-3 flex-wrap">
            <div class="lh-1">
                <span class="avatar avatar-md bg-{{ $color }}">
                    <i class="{{ $icon }} fs-5"></i>
                </span>
            </div>
            <div class="flex-fill">
                <span class="d-block">{{ $title }}</span>
                <h5 class="fw-semibold" @if($valueId) id="{{ $valueId }}" @endif>{{ $value }}</h5>
                @if($badgeText)
                    <span class="badge bg-{{ $color }}-transparent">{{ $badgeText }}</span>
                @endif
            </div>
            @if($percentage)
                <div class="fs-15 {{ $percentageColor }}">{{ $percentage }}</div>
            @endif
        </div>
    </div>
</div>
