@extends('layouts.master')

@section('styles')
    <!-- Apex Charts CSS -->
    <link rel="stylesheet" href="{{ asset('assets/libs/apexcharts/apexcharts.css') }}">
@endsection

@section('content')
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Booking Dashboard</h1>
            <div class="">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="ms-auto pageheader-btn">
            <a href="{{ route('admin.bookings.google-calendar') }}" class="btn btn-primary btn-wave waves-effect waves-light me-2">
                <i class="ri-add-line fw-semibold align-middle me-1"></i> Create Booking
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-wave waves-effect waves-light">
                <i class="ri-list-check fw-semibold align-middle me-1"></i> All Bookings
            </a>
        </div>
    </div>
    <!-- Page Header Close -->

    <!-- Start::row-1 -->
    <div class="row">
        <!-- Today's Bookings -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Today's Bookings"
                value="{{ $stats['today_bookings'] }}"
                icon="ri-calendar-check-line"
                color="primary"
                badgeText="Scheduled for today"
            />
        </div>
        
        <!-- Pending Bookings -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Pending Approval"
                value="{{ $stats['pending_bookings'] }}"
                icon="ri-time-line"
                color="warning"
                badgeText="Awaiting confirmation"
            />
        </div>
        
        <!-- Confirmed Bookings -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Confirmed Bookings"
                value="{{ $stats['confirmed_bookings'] }}"
                icon="ri-check-double-line"
                color="success"
                badgeText="Successfully booked"
            />
        </div>
        
        <!-- Total Users -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Total Users"
                value="{{ $stats['total_trainers'] + $stats['total_clients'] }}"
                icon="ri-group-line"
                color="info"
                badgeText="{{ $stats['total_trainers'] }} Trainers, {{ $stats['total_clients'] }} Clients"
            />
        </div>
    </div>
    <!-- End::row-1 -->

    <!-- Start::row-2 -->
    <div class="row">
        <!-- Weekly Stats -->
        <div class="col-xxl-6 col-xl-6">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Weekly Overview
                    </div>
                    {{-- <div class="dropdown">
                        <a href="javascript:void(0);" class="btn btn-light btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-more-2-fill"></i>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a class="dropdown-item" href="javascript:void(0);">This Week</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);">This Month</a></li>
                        </ul>
                    </div> --}}
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h3 class="fw-semibold mb-1">{{ $stats['week_bookings'] }}</h3>
                                <p class="text-muted mb-0">This Week</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h3 class="fw-semibold mb-1">{{ $stats['month_bookings'] }}</h3>
                                <p class="text-muted mb-0">This Month</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-xxl-6 col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Quick Actions
                    </div>
                </div>
                <div class="card-body my-2">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.bookings.google-calendar') }}" class="btn btn-outline-primary btn-wave text-start">
                            <i class="ri-add-line me-2"></i>Create Booking
                        </a>
                        <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="btn btn-outline-warning btn-wave text-start">
                            <i class="ri-time-line me-2"></i>Pending Approvals
                        </a>
                        <!-- <a href="{{ route('admin.bookings.export') }}" class="btn btn-outline-success btn-wave text-start">
                            <i class="ri-download-line me-2"></i>Export Data
                        </a> -->
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-info btn-wave text-start">
                            <i class="ri-list-check me-2"></i>View All
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End::row-2 -->

    <!-- Start::row-3 -->
    <div class="row">
        <!-- Recent Bookings -->
        <div class="col-xxl-6 col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Recent Bookings
                    </div>
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Trainer</th>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentBookings as $booking)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <span class="avatar avatar-md bg-light border border-dashed p-1">
                                                        @if($booking->trainer && $booking->trainer->profile_image)
                                                            <img src="{{ asset('storage/' . $booking->trainer->profile_image) }}" alt="trainer">
                                                        @else
                                                            <div class="header-link-icon avatar bg-info-transparent avatar-rounded w-100 h-100 d-flex align-items-center justify-content-center">
                                                                {{ strtoupper(substr(optional($booking->trainer)->name ?? '?', 0, 1)) }}
                                                            </div>
                                                        @endif
                                                    </span>
                                                </div>
                                                <span class="fw-semibold">{{ optional($booking->trainer)->name ?? 'Unknown Trainer' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <span class="avatar avatar-md bg-light border border-dashed p-1">
                                                        @if($booking->client && $booking->client->profile_image)
                                                            <img src="{{ asset('storage/' . $booking->client->profile_image) }}" alt="client">
                                                        @else
                                                            <div class="header-link-icon avatar bg-warning-transparent avatar-rounded w-100 h-100 d-flex align-items-center justify-content-center">
                                                                {{ strtoupper(substr(optional($booking->client)->name ?? '?', 0, 1)) }}
                                                            </div>
                                                        @endif
                                                    </span>
                                                </div>
                                                <span class="fw-semibold">{{ optional($booking->client)->name ?? 'Unknown Client' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $booking->date->format('M d') }}</span>
                                            <br><small class="text-muted">{{ $booking->start_time->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            @if($booking->status == 'pending')
                                                <span class="badge bg-warning-transparent">Pending</span>
                                            @elseif($booking->status == 'confirmed')
                                                <span class="badge bg-success-transparent">Confirmed</span>
                                            @else
                                                <span class="badge bg-danger-transparent">Cancelled</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3">
                                            <span class="text-muted">No recent bookings</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Bookings -->
        <div class="col-xxl-6 col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Upcoming Bookings
                    </div>
                    <a href="{{ route('admin.bookings.index', ['date_from' => now()->toDateString()]) }}" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Trainer</th>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingBookings as $booking)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <span class="avatar avatar-md bg-light border border-dashed p-1">
                                                        @if($booking->trainer->profile_image)
                                                            <img src="{{ asset('storage/' . $booking->trainer->profile_image) }}" alt="trainer">
                                                        @else
                                                            <div class="header-link-icon avatar bg-info-transparent avatar-rounded w-100 h-100 d-flex align-items-center justify-content-center">
                                                                {{ strtoupper(substr($booking->trainer->name ?? '?', 0, 1)) }}
                                                            </div>
                                                        @endif
                                                    </span>
                                                </div>
                                                <span class="fw-semibold">{{ $booking->trainer->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <span class="avatar avatar-md bg-light border border-dashed p-1">
                                                        @if($booking->client->profile_image)
                                                            <img src="{{ asset('storage/' . $booking->client->profile_image) }}" alt="client">
                                                        @else
                                                            <div class="header-link-icon avatar bg-warning-transparent avatar-rounded w-100 h-100 d-flex align-items-center justify-content-center">
                                                                {{ strtoupper(substr($booking->client->name ?? '?', 0, 1)) }}
                                                            </div>
                                                        @endif
                                                    </span>
                                                </div>
                                                <span class="fw-semibold">{{ $booking->client->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $booking->date->format('M d') }}</span>
                                            <br><small class="text-muted">{{ $booking->start_time->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            @if($booking->status == 'pending')
                                                <span class="badge bg-warning-transparent">Pending</span>
                                            @elseif($booking->status == 'confirmed')
                                                <span class="badge bg-success-transparent">Confirmed</span>
                                            @else
                                                <span class="badge bg-danger-transparent">Cancelled</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3">
                                            <span class="text-muted">No upcoming bookings</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End::row-3 -->
@endsection

@section('scripts')
    <!-- Apex Charts JS -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    
    <script>
        // You can add charts here if needed
        $(document).ready(function() {
            // Initialize any charts or additional functionality
        });
    </script>
@endsection