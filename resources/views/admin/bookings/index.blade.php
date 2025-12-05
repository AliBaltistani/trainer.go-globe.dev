@extends('layouts.master')


@section('content')
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Booking Management</h1>
            <div class="">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Bookings</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="ms-auto pageheader-btn">
            <!-- <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary btn-wave waves-effect waves-light me-2">
                <i class="ri-add-line fw-semibold align-middle me-1"></i> Create Booking
            </a> -->
            <a href="{{ route('admin.bookings.google-calendar') }}" class="btn btn-success btn-wave waves-effect waves-light me-2">
                <i class="ri-calendar-line fw-semibold align-middle me-1"></i> Google Calendar Booking
            </a>
            <a href="{{ route('admin.bookings.dashboard') }}" class="btn btn-secondary btn-wave waves-effect waves-light">
                <i class="ri-dashboard-line fw-semibold align-middle me-1"></i> Dashboard
            </a>
        </div>
    </div>
    <!-- Page Header Close -->

    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Total Bookings"
                value="{{ $stats['total_bookings'] }}"
                icon="ri-calendar-line"
                color="primary"
                badgeText="All scheduled sessions"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Pending Approval"
                value="{{ $stats['pending_bookings'] }}"
                icon="ri-time-line"
                color="warning"
                badgeText="Awaiting confirmation"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Confirmed Bookings"
                value="{{ $stats['confirmed_bookings'] }}"
                icon="ri-check-double-line"
                color="success"
                badgeText="Successfully booked"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Cancelled Bookings"
                value="{{ $stats['cancelled_bookings'] }}"
                icon="ri-close-circle-line"
                color="danger"
                badgeText="Cancelled sessions"
            />
        </div>
    </div>

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-12">
            <x-tables.card title="All Bookings">
                <x-slot:tools>
                    <div class="d-flex">
                        <div class="me-3">
                            <a href="javascript:void(0);" onclick="exportBookings()" class="btn btn-success btn-sm">
                                <i class="ri-download-line me-1"></i> Export
                            </a>
                        </div>
                    </div>
                </x-slot:tools>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-xl-12">
                        <form id="filterForm" class="row g-3" onsubmit="event.preventDefault(); $('#bookingsTable').DataTable().ajax.reload();">
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">All Status</option>
                                    @foreach($statuses as $key => $status)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Trainer</label>
                                <select name="trainer_id" id="trainer_id" class="form-select">
                                    <option value="">All Trainers</option>
                                    @foreach($trainers as $trainer)
                                        <option value="{{ $trainer->id }}" {{ request('trainer_id') == $trainer->id ? 'selected' : '' }}>{{ $trainer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Client</label>
                                <select name="client_id" id="client_id" class="form-select">
                                    <option value="">All Clients</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bookings Table -->
                <x-tables.table 
                    id="bookingsTable"
                    :headers="['Sr.#', 'Trainer', 'Client', 'Date', 'Time', 'Status', 'Google Calendar', 'Created', 'Actions']"
                    :bordered="true"
                    width="100%" 
                    cellspacing="0"
                >
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </x-tables.table>
            </x-tables.card>
        </div>
    </div>
    <!-- End::row-1 -->
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jQuery === 'undefined') {
                console.error('jQuery is not loaded!');
                return;
            }
            var $ = jQuery;
            
            $(document).ready(function() {
                // Initialize DataTable
                var table = $('#bookingsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('admin.bookings.index') }}",
                        type: "GET",
                        data: function(d) {
                            d.status = $('#status').val();
                            d.trainer_id = $('#trainer_id').val();
                            d.client_id = $('#client_id').val();
                            d.date_from = $('#date_from').val();
                            d.date_to = $('#date_to').val();
                        }
                    },
                    columns: [
                        { data: 'id', name: 'id', orderable: false },
                        { data: 'trainer', name: 'trainer' },
                        { data: 'client', name: 'client' },
                        { data: 'date', name: 'date' },
                        { data: 'time', name: 'start_time' },
                        { data: 'status', name: 'status' },
                        { data: 'google_calendar', name: 'google_calendar', orderable: false, searchable: false },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    order: [[3, 'desc'], [4, 'desc']], // Order by Date then Time
                    pageLength: 25,
                    language: {
                        processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
                    }
                });
            });

            window.exportBookings = function() {
                var params = {
                    status: $('#status').val(),
                    trainer_id: $('#trainer_id').val(),
                    client_id: $('#client_id').val(),
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val()
                };
                var queryString = $.param(params);
                window.location.href = "{{ route('admin.bookings.export') }}?" + queryString;
            }
        });

        function updateStatus(id, status) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to change the booking status to " + status + "?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: status === 'confirmed' ? '#28a745' : '#ffc107',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, ' + status + ' it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/bookings/' + id, // Using update route
                        type: 'PUT',
                        data: {
                            status: status,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire(
                                'Updated!',
                                'Booking status has been updated.',
                                'success'
                            );
                            $('#bookingsTable').DataTable().ajax.reload();
                            // Ideally reload stats here too
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'Failed to update booking status.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        function deleteBooking(id) {
            Swal.fire({
                title: 'Delete Booking',
                html: `
                    <p>Are you sure you want to delete this booking? This action will:</p>
                    <ul class="text-start">
                        <li>Permanently delete the booking from the system</li>
                        <li>Remove the Google Calendar event (if exists)</li>
                        <li>Cancel any Google Meet links</li>
                        <li>This action cannot be undone</li>
                    </ul>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/bookings/' + id,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire(
                                'Deleted!',
                                'Booking has been deleted.',
                                'success'
                            );
                            $('#bookingsTable').DataTable().ajax.reload();
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'An error occurred while deleting the booking.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        function syncToGoogleCalendar(bookingId) {
            Swal.fire({
                title: 'Sync to Google Calendar',
                text: 'Are you sure you want to sync this booking to Google Calendar?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Yes, sync it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Syncing...',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`/admin/bookings/${bookingId}/sync-google-calendar`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Synced!', 'Booking synced to Google Calendar successfully!', 'success');
                            $('#bookingsTable').DataTable().ajax.reload();
                        } else {
                            Swal.fire('Error!', 'Error syncing to Google Calendar: ' + (data.message || 'Unknown error'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Error syncing to Google Calendar. Please try again.', 'error');
                    });
                }
            });
        }

        function createGoogleCalendarEvent(bookingId) {
            Swal.fire({
                title: 'Create Google Calendar Event',
                text: 'Are you sure you want to create a Google Calendar event for this booking?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Yes, create it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Creating...',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`/admin/bookings/${bookingId}/sync-google-calendar`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Created!', 'Google Calendar event created successfully!', 'success');
                            $('#bookingsTable').DataTable().ajax.reload();
                        } else {
                            Swal.fire('Error!', 'Error creating Google Calendar event: ' + (data.message || 'Unknown error'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Error creating Google Calendar event. Please try again.', 'error');
                    });
                }
            });
        }
    </script>