@extends('layouts.master')

@section('styles')
    <!-- Apex Charts CSS -->
    <link rel="stylesheet" href="{{ asset('assets/libs/apexcharts/apexcharts.css') }}">
@endsection

@section('content')
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">My Booking Dashboard</h1>
            <div class="">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('trainer.bookings.index') }}">Bookings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="ms-auto pageheader-btn">
            <a href="{{ route('trainer.bookings.index') }}" class="btn btn-secondary btn-wave waves-effect waves-light">
                <i class="ri-list-check fw-semibold align-middle me-1"></i> My Bookings
            </a>
        </div>
    </div>
    <!-- Page Header Close -->

    <!-- Start::row-1 -->
    <div class="row">
        <!-- Today's Bookings -->
        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style2
                title="Today's Bookings"
                value="{{ $stats['today_bookings'] }}"
                color="primary"
                chartId="crm-total-customers"
                subtitle=""
            >
                <x-slot:icon>
                    <i class="ti ti-calendar-event fs-16"></i>
                </x-slot:icon>
            </x-widgets.stat-card-style2>
        </div>
        
        <!-- Pending Bookings -->
        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style2
                title="Pending Approval"
                value="{{ $stats['pending_bookings'] }}"
                color="warning"
                chartId="crm-total-revenue"
                subtitle=""
            >
                <x-slot:icon>
                    <i class="ti ti-clock fs-16"></i>
                </x-slot:icon>
            </x-widgets.stat-card-style2>
        </div>
        
        <!-- Confirmed Bookings -->
        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style2
                title="Confirmed Bookings"
                value="{{ $stats['confirmed_bookings'] }}"
                color="success"
                chartId="crm-conversion-ratio"
                subtitle=""
            >
                <x-slot:icon>
                    <i class="ti ti-check fs-16"></i>
                </x-slot:icon>
            </x-widgets.stat-card-style2>
        </div>
        
        <!-- Total Clients -->
        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style2
                title="Total Clients"
                value="{{ $stats['total_clients'] }}"
                color="info"
                chartId="crm-total-deals"
                subtitle=""
            >
                <x-slot:icon>
                    <i class="ti ti-users fs-16"></i>
                </x-slot:icon>
            </x-widgets.stat-card-style2>
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
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('trainer.bookings.index', ['status' => 'pending']) }}" class="btn btn-warning w-100">
                                <i class="ri-time-line me-2"></i>
                                Pending Approvals
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('trainer.bookings.export') }}" class="btn btn-success w-100">
                                <i class="ri-download-line me-2"></i>
                                Export Data
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('trainer.bookings.schedule') }}" class="btn btn-info w-100">
                                <i class="ri-calendar-line me-2"></i>
                                Calendar View
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('trainer.bookings.settings') }}" class="btn btn-secondary w-100">
                                <i class="ri-settings-3-line me-2"></i>
                                Settings
                            </a>
                        </div>
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
                    <a href="{{ route('trainer.bookings.index') }}" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentBookings as $booking)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($booking->client->profile_image)
                                                    <span class="avatar avatar-sm me-2">
                                                        <img src="{{ asset('storage/' . $booking->client->profile_image) }}" alt="{{ $booking->client->name }}">
                                                    </span>
                                                @else
                                                    <span class="avatar avatar-sm me-2 bg-primary-transparent">
                                                        {{ strtoupper(substr($booking->client->name, 0, 1)) }}
                                                    </span>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">{{ $booking->client->name }}</div>
                                                    <div class="text-muted fs-12">{{ $booking->client->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $booking->date->format('M d, Y') }}</div>
                                            <div class="text-muted fs-12">{{ $booking->start_time->format('h:i A') }} - {{ $booking->end_time->format('h:i A') }}</div>
                                        </td>
                                        <td>
                                            @if($booking->status == 'confirmed')
                                                <span class="badge bg-success">Confirmed</span>
                                            @elseif($booking->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('trainer.bookings.show', $booking->id) }}" class="btn btn-sm btn-primary-light">
                                                <i class="ri-eye-line"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent bookings found</td>
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
                    <a href="{{ route('trainer.bookings.schedule') }}" class="btn btn-sm btn-light">View Calendar</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingBookings as $booking)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($booking->client->profile_image)
                                                    <span class="avatar avatar-sm me-2">
                                                        <img src="{{ asset('storage/' . $booking->client->profile_image) }}" alt="{{ $booking->client->name }}">
                                                    </span>
                                                @else
                                                    <span class="avatar avatar-sm me-2 bg-primary-transparent">
                                                        {{ strtoupper(substr($booking->client->name, 0, 1)) }}
                                                    </span>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">{{ $booking->client->name }}</div>
                                                    <div class="text-muted fs-12">{{ $booking->client->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $booking->date->format('M d, Y') }}</div>
                                            <div class="text-muted fs-12">{{ $booking->start_time->format('h:i A') }} - {{ $booking->end_time->format('h:i A') }}</div>
                                        </td>
                                        <td>
                                            @if($booking->status == 'confirmed')
                                                <span class="badge bg-success">Confirmed</span>
                                            @elseif($booking->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('trainer.bookings.show', $booking->id) }}" class="btn btn-sm btn-primary-light">
                                                <i class="ri-eye-line"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No upcoming bookings found</td>
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
@endsection
