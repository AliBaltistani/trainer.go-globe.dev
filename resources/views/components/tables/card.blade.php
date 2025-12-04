@props(['title', 'tools' => null])

<div class="card custom-card">
    <div class="card-header justify-content-between">
        <div class="card-title">
            {{ $title }}
        </div>
        @if($tools)
            <div class="d-flex flex-wrap gap-2">
                {{ $tools }}
            </div>
        @endif
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
