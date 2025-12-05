<div class="btn-list d-md-flex d-block">
    {{-- Google Calendar Actions --}}
    @if($booking->google_event_id)
        <button class="btn btn-sm btn-icon btn-success-light rounded-circle"
                onclick="syncToGoogleCalendar('{{ $booking->id }}')"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Sync to Google Calendar">
            <i class="ri-calendar-check-line"></i>
        </button>
        @if($booking->meet_link)
            <a href="{{ $booking->meet_link }}" target="_blank"
               class="btn btn-sm btn-icon btn-warning-light rounded-circle"
               data-bs-toggle="tooltip" data-bs-placement="top" title="Join Google Meet">
                <i class="ri-video-chat-line"></i>
            </a>
        @endif
    @else
        <button class="btn btn-sm btn-icon btn-info-light rounded-circle"
                onclick="createGoogleCalendarEvent('{{ $booking->id }}')"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Create Google Calendar Event">
            <i class="ri-calendar-2-line"></i>
        </button>
    @endif

    {{-- Status Actions --}}
    @if($booking->status === 'pending')
        <button type="button" onclick="updateStatus('{{ $booking->id }}', 'confirmed')"
                class="btn btn-sm btn-icon btn-success-light rounded-circle"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Confirm">
            <i class="ri-check-line"></i>
        </button>
    @endif

    @if($booking->status === 'pending' || $booking->status === 'confirmed')
        <button type="button" onclick="updateStatus('{{ $booking->id }}', 'cancelled')"
                class="btn btn-sm btn-icon btn-warning-light rounded-circle"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel">
            <i class="ri-close-line"></i>
        </button>
    @endif

    {{-- View Action --}}
    <a href="{{ route('admin.bookings.show', $booking->id) }}"
       class="btn btn-sm btn-icon btn-primary-light rounded-circle"
       data-bs-toggle="tooltip" data-bs-placement="top" title="View">
        <i class="ri-eye-line"></i>
    </a>

    {{-- Edit Action --}}
    <a href="{{ route('admin.bookings.edit', $booking->id) }}"
       class="btn btn-sm btn-icon btn-secondary-light rounded-circle"
       data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
        <i class="ri-edit-line"></i>
    </a>

    {{-- Delete Action --}}
    <button type="button" onclick="deleteBooking('{{ $booking->id }}')"
            class="btn btn-sm btn-icon btn-danger-light rounded-circle"
            data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
        <i class="ri-delete-bin-line"></i>
    </button>
</div>
