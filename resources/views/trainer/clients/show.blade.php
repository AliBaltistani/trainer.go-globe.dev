@extends('layouts.master')

@section('title', $client->name . ' - Profile')

@section('content')

<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-center justify-content-between flex-wrap">
        <h1 class="page-title fw-medium fs-18 mb-0">Client Profile</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trainer.clients.index') }}">Clients</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $client->name }}</li>
        </ol>
    </div>
</div>

<div class="row">
    <!-- Left Sidebar: Profile Info -->
    <div class="col-xl-3">
        <div class="card custom-card">
            <div class="card-body text-center">
                <span class="avatar avatar-xxl avatar-rounded mb-3">
                    <img src="{{ $client->profile_image ? asset('storage/'.$client->profile_image) : asset('assets/images/faces/9.jpg') }}" alt="img">
                </span>
                <h5 class="fw-semibold mb-1">{{ $client->name }}</h5>
                <p class="text-muted mb-2">Client</p>
                
                <div class="d-flex justify-content-center gap-2 mb-3">
                    @if($client->phone)
                    <a href="tel:{{ $client->phone }}" class="btn btn-sm btn-icon btn-outline-primary rounded-circle">
                        <i class="ri-phone-line"></i>
                    </a>
                    @endif
                    <a href="mailto:{{ $client->email }}" class="btn btn-sm btn-icon btn-outline-primary rounded-circle">
                        <i class="ri-mail-line"></i>
                    </a>
                </div>
                
                <div class="text-start mt-4">
                    <h6 class="fw-semibold mb-3">Contact Details</h6>
                    <div class="mb-2">
                        <span class="fw-medium text-muted me-2">Email:</span>
                        <span>{{ $client->email }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="fw-medium text-muted me-2">Phone:</span>
                        <span>{{ $client->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="fw-medium text-muted me-2">Joined:</span>
                        <span>{{ $client->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Subscription Status -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Subscription</div>
            </div>
            <div class="card-body">
                @php
                    $subscription = $client->subscriptionsAsClient->where('trainer_id', Auth::id())->first();
                @endphp
                
                @if($subscription)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Status:</span>
                        @if($subscription->status == 'active')
                            <span class="badge bg-success-transparent">Active</span>
                        @else
                            <span class="badge bg-danger-transparent">{{ ucfirst($subscription->status) }}</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Since:</span>
                        <span>{{ \Carbon\Carbon::parse($subscription->start_date)->format('M d, Y') }}</span>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">No active subscription found.</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Content: Stats, Progress, etc. -->
    <div class="col-xl-9">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    Overview
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-light">Last 30 Days</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                        <div class="p-3 border rounded bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted">Workouts Completed</span>
                                <i class="ri-fitness-line text-primary fs-18"></i>
                            </div>
                            <h4 class="fw-bold mb-0">{{ $client->videoProgress->where('completed', true)->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                        <div class="p-3 border rounded bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted">Goals Active</span>
                                <i class="ri-flag-line text-success fs-18"></i>
                            </div>
                            <h4 class="fw-bold mb-0">{{ $client->goals->where('status', 'active')->count() }}</h4>
                        </div>
                    </div>
                    <!-- Add more stats as needed -->
                </div>
            </div>
        </div>

        <!-- Tabs for detailed info -->
        <div class="card custom-card">
            <div class="card-body">
                <ul class="nav nav-tabs tab-style-1 d-flex justify-content-start mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#goals" aria-selected="true" role="tab">Goals</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#progress" aria-selected="false" role="tab">Progress</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="goals" role="tabpanel">
                        @if($client->goals->count() > 0)
                            <div class="table-responsive">
                                <table class="table text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Goal</th>
                                            <th>Target Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($client->goals as $goal)
                                        <tr>
                                            <td>{{ $goal->description }}</td>
                                            <td>{{ $goal->target_date ? \Carbon\Carbon::parse($goal->target_date)->format('M d, Y') : 'N/A' }}</td>
                                            <td><span class="badge bg-primary-transparent">{{ ucfirst($goal->status) }}</span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center py-3">No goals set yet.</p>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="progress" role="tabpanel">
                        <p class="text-muted text-center py-3">Progress charts and data will appear here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
