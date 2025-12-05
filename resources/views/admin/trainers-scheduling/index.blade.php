@extends('layouts.master')

@section('styles')
    <style>
        .scheduling-status {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
        }
        .status-complete {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-partial {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-incomplete {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .availability-badge {
            font-size: 0.75rem;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            margin: 0.125rem;
            display: inline-block;
        }
        .day-available {
            background-color: #dcfce7;
            color: #166534;
        }
        .day-unavailable {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
    </style>
@endsection

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">Trainers Scheduling Management</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#!">Scheduling</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Trainers Overview</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="{{ route('admin.bookings.scheduling-menu') }}" class="btn btn-primary btn-wave waves-effect waves-light me-2">
            <i class="ri-settings-3-line me-1"></i> Scheduling Settings
        </a>
    </div>
</div>
<!-- Page Header Close -->

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Total Trainers"
                value="{{ $stats['total_trainers'] }}"
                icon="ti ti-users"
                color="warning"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Complete Setup"
                value="{{ $stats['complete_setup'] }}"
                icon="ti ti-check"
                color="success"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Partial Setup"
                value="{{ $stats['partial_setup'] }}"
                icon="ti ti-clock"
                color="info"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="No Setup"
                value="{{ $stats['no_setup'] }}"
                icon="ti ti-x-circle"
                color="danger"
            />
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-xl-12">
            <x-tables.card title="Trainers Scheduling Management">
                {{-- <x-slot:tools>
                    <div class="d-flex gap-2">
                         <select class="form-select form-select-sm" id="setupStatusFilter" style="width:auto;">
                            <option value="">All Setup Status</option>
                            <option value="complete">Complete Setup</option>
                            <option value="partial">Partial Setup</option>
                            <option value="none">No Setup</option>
                        </select>
                        <select class="form-select form-select-sm" id="availabilityFilter" style="width:auto;">
                            <option value="">All Availability</option>
                            <option value="available">Available</option>
                            <option value="busy">Busy</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                </x-slot:tools> --}}

                <!-- Success/Error Messages -->
                <div id="alert-container"></div>

                <x-tables.table 
                    id="trainers-scheduling-table"
                    :headers="['Sr.#', 'Trainer', 'Email', 'Weekly Availability', 'Blocked Times', 'Session Capacity', 'Booking Approval', 'Setup Status', 'Last Updated', 'Actions']"
                    :striped="true"
                    :bordered="true"
                >
                    @foreach($trainers as $trainer)
                    <tr>
                        <td>{{ $trainer->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($trainer->profile_image)
                                    <img src="{{ asset('storage/' . $trainer->profile_image) }}" alt="{{ $trainer->name }}" class="rounded-circle me-2" width="32" height="32">
                                @else
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                        <span class="text-white fw-bold">{{ substr($trainer->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <strong>{{ $trainer->name }}</strong>
                                    @if($trainer->phone)
                                        <br><small class="text-muted">{{ $trainer->phone }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $trainer->email }}</td>
                        <td>
                            <div class="availability-container">
                                @if($trainer->availabilities->count() > 0)
                                    @php
                                        $daysOfWeek = [
                                            0 => 'Sunday',
                                            1 => 'Monday', 
                                            2 => 'Tuesday',
                                            3 => 'Wednesday',
                                            4 => 'Thursday',
                                            5 => 'Friday',
                                            6 => 'Saturday'
                                        ];
                                    @endphp
                                    @foreach([1, 2, 3, 4, 5, 6, 0] as $dayIndex)
                                        @php
                                            $dayAvailability = $trainer->availabilities->where('day_of_week', $dayIndex)->first();
                                            $dayName = $daysOfWeek[$dayIndex];
                                        @endphp
                                        <div class="availability-day mb-1">
                                            <strong>{{ $dayName }}</strong>
                                            @if($dayAvailability)
                                                <div class="time-slots">
                                                    @if($dayAvailability->morning_available)
                                                        <small class="text-success">
                                                            Morning: {{ \Carbon\Carbon::parse($dayAvailability->morning_start)->format('g:i A') }} - {{ \Carbon\Carbon::parse($dayAvailability->morning_end)->format('g:i A') }}
                                                        </small>
                                                    @endif
                                                    @if($dayAvailability->evening_available)
                                                        <br><small class="text-success">
                                                            Evening: {{ \Carbon\Carbon::parse($dayAvailability->evening_start)->format('g:i A') }} - {{ \Carbon\Carbon::parse($dayAvailability->evening_end)->format('g:i A') }}
                                                        </small>
                                                    @endif
                                                    @if(!$dayAvailability->morning_available && !$dayAvailability->evening_available)
                                                        <small class="text-muted">Not Available</small>
                                                    @endif
                                                </div>
                                            @else
                                                <small class="text-muted">Not Available</small>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">Not Set</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="block-time-container">
                                @if($trainer->blockedTimes && $trainer->blockedTimes->count() > 0)
                                    @foreach($trainer->blockedTimes->take(3) as $blockedTime)
                                        <div class="blocked-time-item mb-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="time-badge badge bg-warning-transparent">
                                                    {{ \Carbon\Carbon::parse($blockedTime->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($blockedTime->end_time)->format('g:i A') }}
                                                </span>
                                                <small class="text-muted">{{ $blockedTime->date ? \Carbon\Carbon::parse($blockedTime->date)->format('M d') : 'Recurring' }}</small>
                                            </div>
                                            @if($blockedTime->reason)
                                                <small class="text-muted d-block">{{ Str::limit($blockedTime->reason, 30) }}</small>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if($trainer->blockedTimes->count() > 3)
                                        <small class="text-muted">+{{ $trainer->blockedTimes->count() - 3 }} more</small>
                                    @endif
                                @else
                                    <div class="text-center p-2">
                                        <i class="ri-time-line text-muted fs-20"></i>
                                        <br><small class="text-muted">No blocked times</small>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="capacity-container">
                                @if($trainer->sessionCapacity)
                                    <div class="capacity-section">
                                        <div class="capacity-header mb-2">
                                            <strong class="text-primary">Daily Capacity</strong>
                                        </div>
                                        <div class="capacity-item mb-1">
                                            <span class="capacity-label">Maximum Sessions:</span>
                                            <span class="capacity-value">{{ $trainer->sessionCapacity->max_daily_sessions ?? 'Not set' }}</span>
                                        </div>
                                        
                                        <div class="capacity-header mb-2 mt-3">
                                            <strong class="text-info">Weekly Capacity</strong>
                                        </div>
                                        <div class="capacity-item mb-1">
                                            <span class="capacity-label">Maximum Sessions:</span>
                                            <span class="capacity-value">{{ $trainer->sessionCapacity->max_weekly_sessions ?? 'Not set' }}</span>
                                        </div>
                                        
                                        <div class="capacity-header mb-2 mt-3">
                                            <strong class="text-secondary">Session Details</strong>
                                        </div>
                                        <div class="capacity-item mb-1">
                                            <span class="capacity-label">Duration:</span>
                                            <span class="capacity-value">{{ $trainer->sessionCapacity->session_duration_minutes ?? 60 }} min</span>
                                        </div>
                                        <div class="capacity-item mb-1">
                                            <span class="capacity-label">Break Time:</span>
                                            <span class="capacity-value">{{ $trainer->sessionCapacity->break_between_sessions_minutes ?? 15 }} min</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center p-2">
                                        <i class="ri-calendar-line text-muted fs-20"></i>
                                        <br><small class="text-muted">Capacity not set</small>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="booking-approval-container">
                                @if($trainer->bookingSettings)
                                    <div class="approval-section">
                                        <div class="approval-header mb-2">
                                            <strong class="text-success">Booking Approval</strong>
                                        </div>
                                        
                                        <div class="approval-item mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="ri-user-settings-line text-primary me-2"></i>
                                                <span class="approval-label">Client Self-Booking:</span>
                                            </div>
                                            <div class="mt-1">
                                                @if($trainer->bookingSettings->auto_accept_bookings)
                                                    <span class="badge bg-success-transparent">
                                                        <i class="ri-check-line me-1"></i>Enabled
                                                    </span>
                                                    <div class="small text-muted mt-1">
                                                        Allow clients to book directly through the app
                                                    </div>
                                                @else
                                                    <span class="badge bg-warning-transparent">
                                                        <i class="ri-close-line me-1"></i>Disabled
                                                    </span>
                                                    <div class="small text-muted mt-1">
                                                        Require trainer approval for client bookings
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center p-2">
                                        <i class="ri-settings-3-line text-muted fs-20"></i>
                                        <br><small class="text-muted">Settings not configured</small>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="scheduling-status {{ $trainer->setup_status_class }}">
                                {{ $trainer->setup_status_text }}
                            </span>
                        </td>
                        <td>
                            @if($trainer->last_scheduling_update)
                                {{ $trainer->last_scheduling_update->format('M d, Y') }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <div class="btn-group" role="group">
                                    <!-- <button type="button" class="btn btn-sm btn-success" onclick="window.location.href='{{ route('admin.bookings.scheduling-menu') }}?trainer_id={{ $trainer->id }}'" title="View Details">
                                        <i class="ri-eye-line"></i>
                                    </button> -->
                                    <!-- <button type="button" class="btn btn-sm btn-info" onclick="sendReminder('{{ $trainer->id }}')" title="Send Setup Reminder">
                                        <i class="ri-mail-line"></i>
                                    </button> -->
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </x-tables.table>
            </x-tables.card>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#trainers-scheduling-table').DataTable({
                responsive: true,
                order: [[0, 'desc']],
                pageLength: 25,
                dom: 'Bfrtip',
                buttons: [
                    
                ],
                language: {
                    search: "Search trainers:",
                    lengthMenu: "Show _MENU_ trainers per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ trainers",
                    infoEmpty: "No trainers found",
                    infoFiltered: "(filtered from _MAX_ total trainers)"
                }
            });

            // Setup Status Filter
            $('#setup-filter').on('change', function() {
                var value = $(this).val();
                if (value === '') {
                    table.column(7).search('').draw();
                } else {
                    table.column(7).search(value).draw();
                }
            });

            // Approval Filter
            $('#approval-filter').on('change', function() {
                var value = $(this).val();
                if (value === '') {
                    table.column(6).search('').draw();
                } else if (value === '1') {
                    table.column(6).search('Auto Approval').draw();
                } else {
                    table.column(6).search('Manual Approval').draw();
                }
            });

            // Export button handlers
            $('#export-excel').on('click', function() {
                table.button('.buttons-excel').trigger();
            });

            $('#export-pdf').on('click', function() {
                table.button('.buttons-pdf').trigger();
            });
        });

        // Send setup reminder function
        function sendReminder(trainerId) {
            Swal.fire({
                title: 'Send Reminder?',
                text: "Send setup reminder to this trainer?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, send it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/trainers-scheduling/send-reminder',
                        method: 'POST',
                        data: {
                            trainer_id: trainerId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Success', 'Setup reminder sent successfully!', 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Failed to send reminder. Please try again.', 'error');
                        }
                    });
                }
            });
        }

        // Show alert function (Deprecated in favor of SweetAlert, but kept for compatibility if called elsewhere)
        function showAlert(type, message) {
             Swal.fire({
                title: type.charAt(0).toUpperCase() + type.slice(1),
                text: message,
                icon: type === 'danger' ? 'error' : type,
                timer: 5000,
                timerProgressBar: true
             });
        }
    </script>
@endsection