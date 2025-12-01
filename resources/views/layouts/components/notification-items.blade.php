@forelse($notifications as $notification)
<li class="dropdown-item position-relative">
    <a href="{{ route('notifications.show', $notification->id) }}" class="stretched-link"></a>
    <div class="d-flex align-items-start gap-3">
        <div class="lh-1">
            <span class="avatar avatar-sm avatar-rounded bg-primary-transparent">
                <i class="ri-notification-line fs-14"></i>
            </span>
        </div>
        <div class="flex-fill">
            <span class="d-block fw-semibold">{{ $notification->title }}</span>
            <span class="d-block text-muted fs-12">{{ Str::limit($notification->message, 50) }}</span>
        </div>
        <div class="text-end">
            <span class="d-block mb-1 fs-12 text-muted">{{ $notification->created_at->diffForHumans() }}</span>
            @if($notification->status != 'read')
            <span class="d-block text-primary"><i class="ri-circle-fill fs-9"></i></span>
            @endif
        </div>
    </div>
</li>
@empty
@endforelse
