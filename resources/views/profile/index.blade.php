@extends('layouts.master')

@section('styles')

        <link rel="stylesheet" href="{{asset('build/assets/libs/glightbox/css/glightbox.min.css')}}">

@endsection

@section('content')
	
                    <!-- Start::page-header -->
                    <div class="page-header-breadcrumb mb-3">
                        <div class="d-flex align-center justify-content-between flex-wrap">
                            <h1 class="page-title fw-medium fs-18 mb-0">
                                @if($isOwnProfile)
                                    My Profile
                                @else
                                    {{ $user->name }}'s Profile
                                @endif
                            </h1>
                            <ol class="breadcrumb mb-0">
                                @if(Auth::user()->role === 'admin' && !$isOwnProfile)
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                                @else
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                @endif
                                <li class="breadcrumb-item active" aria-current="page">Profile</li>
                            </ol>
                        </div>
                    </div>
                    <!-- End::page-header -->

                    <!-- Start:: row-1 -->
                    <div class="row justify-content-center">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card custom-card profile-card">
                                        <div class="profile-banner-image" style="height: 220px; overflow: hidden;">
                                            @if(!empty($user->business_logo))
                                                <img src="{{ asset('storage/' . $user->business_logo) }}" class="card-img-top" alt="Business Logo" style="height: 100%; width: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='{{ asset('build/assets/images/media/media-3.jpg') }}';">
                                            @else
                                                <img src="{{ asset('build/assets/images/media/media-3.jpg') }}" class="card-img-top" alt="..." style="height: 100%; width: 100%; object-fit: cover;">
                                            @endif
                                        </div>
                                        <div class="card-body p-4 pb-0 position-relative">
                                            <div class="d-flex align-items-end justify-content-between flex-wrap">
                                                <div>
                                                    <span class="avatar avatar-xxl avatar-rounded bg-primary online">
                                                        @if($user->profile_image)
                                                            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" onerror="this.onerror=null; this.src='{{ asset('build/assets/images/faces/12.jpg') }}';">
                                                        @else
                                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                                        @endif
                                                    </span>
                                                    <div class="mt-4 mb-3 d-flex align-items-center flex-wrap gap-3 justify-content-between">
                                                        <div>
                                                            <h5 class="fw-semibold mb-1">{{ $user->name }}</h5>
                                                            <span class="d-block fw-medium text-muted mb-1">
                                                                @if($user->role === 'trainer' && $user->designation)
                                                                    {{ $user->designation }}
                                                                @else
                                                                    {{ ucfirst($user->role) }}
                                                                @endif
                                                            </span>
                                                            <p class="fs-12 mb-0 fw-medium text-muted"> 
                                                                <span class="me-3"><i class="ri-mail-line me-1 align-middle"></i>{{ $user->email }}</span> 
                                                                <span><i class="ri-phone-line me-1 align-middle"></i>{{ $user->phone ?: 'Not provided' }}</span> 
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    @if($user->role === 'trainer')
                                                        <ul class="nav nav-tabs mb-0 tab-style-8 scaleX" id="myTab" role="tablist">
                                                            <li class="nav-item" role="presentation">
                                                                <button class="nav-link active" id="profile-about-tab" data-bs-toggle="tab"
                                                                    data-bs-target="#profile-about-tab-pane" type="button" role="tab"
                                                                    aria-controls="profile-about-tab-pane" aria-selected="true">About</button>
                                                            </li>
                                                            <li class="nav-item" role="presentation">
                                                                <button class="nav-link" id="certifications-tab" data-bs-toggle="tab"
                                                                    data-bs-target="#certifications-tab-pane" type="button" role="tab"
                                                                    aria-controls="certifications-tab-pane" aria-selected="false">Certifications</button>
                                                            </li>
                                                            <li class="nav-item" role="presentation">
                                                                <button class="nav-link" id="testimonials-tab" data-bs-toggle="tab"
                                                                    data-bs-target="#testimonials-tab-pane" type="button" role="tab"
                                                                    aria-controls="testimonials-tab-pane" aria-selected="false">Reviews</button>
                                                            </li>
                                                            <li class="nav-item" role="presentation">
                                                                <button class="nav-link" id="specializations-tab" data-bs-toggle="tab"
                                                                    data-bs-target="#specializations-tab-pane" type="button" role="tab"
                                                                    aria-controls="specializations-tab-pane" aria-selected="false">Specializations</button>
                                                            </li>
                                                            <li class="nav-item" role="presentation">
                                                                <button class="nav-link" id="clients-tab" data-bs-toggle="tab"
                                                                    data-bs-target="#clients-tab-pane" type="button" role="tab"
                                                                    aria-controls="clients-tab-pane" aria-selected="false">Clients</button>
                                                            </li>
                                                        </ul>
                                                    @else
                                                        <ul class="nav nav-tabs mb-0 tab-style-8 scaleX" id="myTab" role="tablist">
                                                            <li class="nav-item" role="presentation">
                                                                <button class="nav-link active" id="profile-about-tab" data-bs-toggle="tab"
                                                                    data-bs-target="#profile-about-tab-pane" type="button" role="tab"
                                                                    aria-controls="profile-about-tab-pane" aria-selected="true">About</button>
                                                            </li>
                                                        </ul>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="tab-content" id="profile-tabs">
                                        <div class="tab-pane show active p-0 border-0" id="profile-about-tab-pane" role="tabpanel" aria-labelledby="profile-about-tab" tabindex="0">
                                            <div class="row">
                                                <div class="col-xxl-4">
                                                    <div class="row">
                                                        <div class="col-xl-12">
                                                            <div class="card custom-card">
                                                                <div class="card-body">
                                                                    @if($user->role === 'trainer')
                                                                        @php
                                                                            $subscribedClientsCount = $user->subscriptionsAsTrainer()->where('status', 'active')->count();
                                                                            $certificationsCount = $user->certifications ? $user->certifications->count() : 0;
                                                                            $specializationsCount = $user->specializations ? $user->specializations->count() : 0;
                                                                            $reviewsCount = $user->receivedTestimonials ? $user->receivedTestimonials->count() : 0;
                                                                        @endphp
                                                                        <div class="d-flex align-items-center justify-content-center gap-4">
                                                                            <div class="text-center">
                                                                                <h3 class="fw-semibold mb-1">{{ $subscribedClientsCount }}</h3>
                                                                                <span class="d-block text-muted">Clients</span>
                                                                            </div>
                                                                            <div class="vr"></div>
                                                                            <div class="text-center">
                                                                                <h3 class="fw-semibold mb-1">{{ $certificationsCount }}</h3>
                                                                                <span class="d-block text-muted">Certifications</span>
                                                                            </div>
                                                                            <div class="vr"></div>
                                                                            <div class="text-center">
                                                                                <h3 class="fw-semibold mb-1">{{ $reviewsCount }}</h3>
                                                                                <span class="d-block text-muted">Reviews</span>
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <div class="d-flex align-items-center justify-content-center gap-4">
                                                                            <div class="text-center">
                                                                                <h3 class="fw-semibold mb-1">
                                                                                    {{ ceil(\Carbon\Carbon::parse($user->created_at)->diffInDays()) }}
                                                                                </h3>
                                                                                <span class="d-block text-muted">Days Active</span>
                                                                            </div>
                                                                            <div class="vr"></div>
                                                                            <div class="text-center">
                                                                                <h3 class="fw-semibold mb-1">
                                                                                    {{ $user->email_verified_at ? 'Verified' : 'Pending' }}
                                                                                </h3>
                                                                                <span class="d-block text-muted">Email Status</span>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12">
                                                            <div class="card custom-card">
                                                                <div class="card-header">
                                                                    <div class="card-title">
                                                                        About 
                                                                    </div>
                                                                </div>
                                                                <div class="card-body">
                                                                    @if($user->role === 'trainer' && $user->about)
                                                                        <p class="text-muted">{{ $user->about }}</p>
                                                                    @else
                                                                        <p class="text-muted">User profile information and account details.</p>
                                                                    @endif
                                                                    <div class="text-muted">
                                                                        <div class="mb-2 d-flex align-items-center gap-1 flex-wrap">
                                                                            <span class="avatar avatar-sm avatar-rounded text-default">
                                                                                <i class="ri-mail-line align-middle fs-15"></i>
                                                                            </span>
                                                                            <span class="fw-medium text-default">Email : </span> {{ $user->email }}
                                                                        </div>
                                                                        <div class="mb-2 d-flex align-items-center gap-1 flex-wrap">
                                                                            <span class="avatar avatar-sm avatar-rounded text-default">
                                                                                <i class="ri-phone-line align-middle fs-15"></i>
                                                                            </span>
                                                                            <span class="fw-medium text-default">Phone : </span> {{ $user->phone ?: 'Not provided' }}
                                                                        </div>
                                                                        <div class="mb-2 d-flex align-items-center gap-1 flex-wrap">
                                                                            <span class="avatar avatar-sm avatar-rounded text-default">
                                                                                <i class="ri-shield-user-line align-middle fs-15"></i>
                                                                            </span>
                                                                            <span class="fw-medium text-default">Role : </span> {{ ucfirst($user->role) }}
                                                                        </div>
                                                                        @if($user->role === 'trainer' && $user->designation)
                                                                        <div class="mb-2 d-flex align-items-center gap-1 flex-wrap">
                                                                            <span class="avatar avatar-sm avatar-rounded text-default">
                                                                                <i class="ri-user-star-line align-middle fs-15"></i>
                                                                            </span>
                                                                            <span class="fw-medium text-default">Designation : </span> {{ $user->designation }}
                                                                        </div>
                                                                        @endif
                                                                        @if($user->role === 'trainer' && $user->experience)
                                                                        <div class="mb-2 d-flex align-items-center gap-1 flex-wrap">
                                                                            <span class="avatar avatar-sm avatar-rounded text-default">
                                                                                <i class="ri-time-line align-middle fs-15"></i>
                                                                            </span>
                                                                            <span class="fw-medium text-default">Experience : </span> {{ str_replace('_', ' ', ucwords($user->experience, '_')) }}
                                                                        </div>
                                                                        @endif
                                                                        <div class="mb-0 d-flex align-items-center gap-1">
                                                                            <span class="avatar avatar-sm avatar-rounded text-default">
                                                                                <i class="ri-building-line align-middle fs-15"></i>
                                                                            </span>
                                                                            <span class="fw-medium text-default">Member Since : </span> {{ \Carbon\Carbon::parse($user->created_at)->format('M Y') }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if($user->role === 'client' && $user->goals && $user->goals->count() > 0)
                                                        <div class="col-xl-12">
                                                            <div class="card custom-card overflow-hidden">
                                                                <div class="card-header">
                                                                    <div class="card-title">
                                                                        Fitness Goals
                                                                    </div>
                                                                    <div class="ms-auto">
                                                                        <span class="badge bg-primary-transparent">{{ $user->goals->count() }} Goals</span>
                                                                    </div>
                                                                </div>
                                                                <div class="card-body p-0">
                                                                    <ul class="list-group list-group-flush">
                                                                        @foreach($user->goals as $goal)
                                                                        <li class="list-group-item">
                                                                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                                                                <div>
                                                                                    <span class="avatar avatar-md bg-primary-transparent">
                                                                                        <i class="ri-target-line fs-4"></i>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="flex-fill">
                                                                                    <span class="d-block fw-medium">{{ $goal->name }}</span>
                                                                                    <span class="text-muted fs-12">
                                                                                        @if($goal->status)
                                                                                            <span class="badge bg-success-transparent">Active</span>
                                                                                        @else
                                                                                            <span class="badge bg-secondary-transparent">Inactive</span>
                                                                                        @endif
                                                                                        @if($goal->target_value)
                                                                                            - Target: {{ $goal->target_value }}{{ $goal->metric_unit ? ' ' . $goal->metric_unit : '' }}
                                                                                        @endif
                                                                                        @if($goal->deadline)
                                                                                            - Deadline: {{ \Carbon\Carbon::parse($goal->deadline)->format('M d, Y') }}
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                                @if($goal->current_value && $goal->target_value)
                                                                                <div class="text-end">
                                                                                    <div class="progress" style="width: 100px; height: 8px;">
                                                                                        @php
                                                                                            $progress = min(100, ($goal->current_value / $goal->target_value) * 100);
                                                                                        @endphp
                                                                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"></div>
                                                                                    </div>
                                                                                    <small class="text-muted">{{ number_format($progress, 0) }}%</small>
                                                                                </div>
                                                                                @endif
                                                                            </div>
                                                                        </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @if($user->role === 'trainer' && $user->specializations && $user->specializations->count() > 0)
                                                        <div class="col-xl-12">
                                                            <div class="card custom-card overflow-hidden">
                                                                <div class="card-header">
                                                                    <div class="card-title">
                                                                        Specializations
                                                                    </div>
                                                                </div>
                                                                <div class="card-body p-0">
                                                                    <ul class="list-group list-group-flush">
                                                                        @foreach($user->specializations as $specialization)
                                                                        <li class="list-group-item">
                                                                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                                                                <div>
                                                                                    <span class="avatar avatar-md bg-primary-transparent">
                                                                                        <i class="ri-star-line fs-4"></i>
                                                                                    </span>
                                                                                </div>
                                                                                <div>
                                                                                    <span class="d-block fw-medium">{{ $specialization->name }}</span>
                                                                                    @if($specialization->description)
                                                                                    <span class="text-muted fs-12">{{ Str::limit($specialization->description, 60) }}</span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                        <div class="col-xl-12">
                                                            <div class="card custom-card overflow-hidden">
                                                                <div class="card-header">
                                                                    <div class="card-title">
                                                                        Location
                                                                    </div>
                                                                </div>
                                                                <div class="card-body">
                                                                    @if($user->location)
                                                                        <div class="d-flex align-items-center mb-2">
                                                                            <span class="badge bg-success-transparent me-2">
                                                                                <i class="ri-map-pin-fill me-1"></i>Location Set
                                                                            </span>
                                                                            @if($user->location->country && $user->location->state && $user->location->city)
                                                                                <span class="badge bg-info-transparent">Complete</span>
                                                                            @else
                                                                                <span class="badge bg-warning-transparent">Incomplete</span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="text-muted">
                                                                            <div class="mb-2 d-flex align-items-center gap-1 flex-wrap">
                                                                                <span class="avatar avatar-sm avatar-rounded text-default">
                                                                                    <i class="ri-map-pin-line align-middle fs-15"></i>
                                                                                </span>
                                                                                <span class="fw-medium text-default">Address : </span>
                                                                                @if($user->location->address || $user->location->city || $user->location->state || $user->location->country)
                                                                                    <span>
                                                                                        @if($user->location->address)
                                                                                            {{ $user->location->address }},
                                                                                        @endif
                                                                                        @if($user->location->city)
                                                                                            {{ $user->location->city }},
                                                                                        @endif
                                                                                        @if($user->location->state)
                                                                                            {{ $user->location->state }},
                                                                                        @endif
                                                                                        @if($user->location->country)
                                                                                            {{ $user->location->country }}
                                                                                        @endif
                                                                                        @if($user->location->zipcode)
                                                                                            - {{ $user->location->zipcode }}
                                                                                        @endif
                                                                                    </span>
                                                                                @else
                                                                                    <span class="text-muted">Not specified</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <div class="text-center py-2">
                                                                            <div class="mb-2">
                                                                                <i class="ri-map-pin-line fs-24 text-muted"></i>
                                                                            </div>
                                                                            <p class="text-muted mb-0 fs-12">No location information available</p>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-8">
                                                    <div class="card custom-card">
                                                        <div class="card-header p-0">
                                                            <ul class="nav nav-tabs tab-style-8 scaleX justify-content-end" id="myTab4" role="tablist">
                                                                <li class="nav-item" role="presentation">
                                                                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-tab-pane" type="button" role="tab" aria-controls="info-tab-pane" aria-selected="true"><i class="ri-information-line lh-1 me-1"></i>Information</button>
                                                                </li>
                                                                @if($canEdit)
                                                                <li class="nav-item" role="presentation">
                                                                    <a href="@if($isOwnProfile){{ route('profile.edit') }}@else{{ route('admin.users.edit', $user->id) }}@endif" class="nav-link" type="button"><i class="ri-edit-line lh-1 me-1"></i>Edit</a>
                                                                </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="tab-content" id="myTabContent3">
                                                                <div class="tab-pane show active overflow-hidden p-0 border-0" id="info-tab-pane" role="tabpanel" aria-labelledby="info-tab" tabindex="0">
                                                                    <div class="row">
                                                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-semibold">Full Name</label>
                                                                                <p class="text-muted mb-0">{{ $user->name ?: 'Not provided' }}</p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-semibold">Email Address</label>
                                                                                <p class="text-muted mb-0">{{ $user->email }}</p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-semibold">Phone Number</label>
                                                                                <p class="text-muted mb-0">{{ $user->phone ?: 'Not provided' }}</p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-semibold">Role</label>
                                                                                <p class="text-muted mb-0">{{ ucfirst($user->role) }}</p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-semibold">Member Since</label>
                                                                                <p class="text-muted mb-0">{{ $user->created_at->format('M d, Y') }}</p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-semibold">Email Status</label>
                                                                                <p class="text-muted mb-0">
                                                                                    @if($user->email_verified_at)
                                                                                        <span class="badge bg-success-transparent">Verified</span>
                                                                                    @else
                                                                                        <span class="badge bg-warning-transparent">Not Verified</span>
                                                                                    @endif
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        {{-- Trainer-specific fields display --}}
                                                                        @if($user->role === 'trainer')
                                                                            @if($user->designation)
                                                                            <div class="col-xl-6 col-lg-6 col-md-6">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label fw-semibold">Designation</label>
                                                                                    <p class="text-muted mb-0">{{ $user->designation }}</p>
                                                                                </div>
                                                                            </div>
                                                                            @endif
                                                                            
                                                                            @if($user->experience)
                                                                            <div class="col-xl-6 col-lg-6 col-md-6">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label fw-semibold">Experience</label>
                                                                                    <p class="text-muted mb-0">{{ str_replace('_', ' ', ucwords($user->experience, '_')) }}</p>
                                                                                </div>
                                                                            </div>
                                                                            @endif
                                                                            
                                                                            @if($user->location)
                                                                            <div class="col-xl-12">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label fw-semibold">Location</label>
                                                                                    <p class="text-muted mb-0">
                                                                                        @if($user->location->address)
                                                                                            {{ $user->location->address }},
                                                                                        @endif
                                                                                        @if($user->location->city)
                                                                                            {{ $user->location->city }},
                                                                                        @endif
                                                                                        @if($user->location->state)
                                                                                            {{ $user->location->state }},
                                                                                        @endif
                                                                                        @if($user->location->country)
                                                                                            {{ $user->location->country }}
                                                                                        @endif
                                                                                        @if($user->location->zipcode)
                                                                                            - {{ $user->location->zipcode }}
                                                                                        @endif
                                                                                        @if(!$user->location->address && !$user->location->city && !$user->location->state && !$user->location->country)
                                                                                            <span class="text-muted">Location not specified</span>
                                                                                        @endif
                                                                                    </p>
                                                                                </div>
                                                                            </div>
                                                                            @endif
                                                                            
                                                                            @if($user->training_philosophy)
                                                                            <div class="col-xl-12">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label fw-semibold">Training Philosophy</label>
                                                                                    <p class="text-muted mb-0">{{ $user->training_philosophy }}</p>
                                                                                </div>
                                                                            </div>
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    @if($canEdit)
                                                    <!-- Account Actions -->
                                                    <div class="card custom-card">
                                                        <div class="card-header">
                                                            <div class="card-title">
                                                                Account Settings
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="d-flex flex-wrap gap-2">
                                                                @if($isOwnProfile)
                                                                    <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">
                                                                        <i class="ri-edit-line me-1"></i>Edit Profile
                                                                    </a>
                                                                    <a href="{{ route('profile.change-password') }}" class="btn btn-outline-secondary btn-sm">
                                                                        <i class="ri-lock-password-line me-1"></i>Change Password
                                                                    </a>
                                                                @elseif(Auth::user()->role === 'admin')
                                                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                                                                        <i class="ri-edit-line me-1"></i>Edit Profile
                                                                    </a>
                                                                @endif
                                                                <!-- <a href="{{ route('profile.settings') }}" class="btn btn-outline-info btn-sm">
                                                                    <i class="ri-settings-3-line me-1"></i>Settings
                                                                </a> -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($user->role === 'trainer')
                                        <!-- Certifications Tab -->
                                        <div class="tab-pane p-0 border-0" id="certifications-tab-pane" role="tabpanel" aria-labelledby="certifications-tab" tabindex="0">
                                            <div class="row">
                                                <div class="col-xl-12">
                                                    <div class="card custom-card">
                                                        <div class="card-header">
                                                            <div class="card-title">Professional Certifications</div>
                                                            <div class="ms-auto">
                                                                <span class="badge bg-primary-transparent">{{ $user->certifications->count() }} Certifications</span>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            @if($user->certifications->count() > 0)
                                                                <div class="row">
                                                                    @foreach($user->certifications as $certification)
                                                                    <div class="col-md-4 col-sm-6 mb-3">
                                                                        <div class="card border h-100">
                                                                            <div class="card-body text-center p-4">
                                                                                <div class="mb-3">
                                                                                    <span class="avatar avatar-lg avatar-rounded bg-primary-transparent">
                                                                                        <i class="ri-award-line fs-24"></i>
                                                                                    </span>
                                                                                </div>
                                                                                <h6 class="fw-semibold mb-2">{{ $certification->certificate_name }}</h6>
                                                                                <p class="text-muted fs-12 mb-3">Certified</p>
                                                                                <div class="d-flex flex-column gap-2">
                                                                                    @if($certification->doc)
                                                                                        <a href="{{ asset('storage/' . $certification->doc) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                                            <i class="ri-download-line me-1"></i>View Document
                                                                                        </a>
                                                                                    @else
                                                                                        <span class="badge bg-secondary-transparent">No Document</span>
                                                                                    @endif
                                                                                    <small class="text-muted">Added: {{ $certification->created_at->format('d M, Y') }}</small>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <div class="text-center py-4">
                                                                    <i class="ri-award-line fs-48 text-muted mb-3"></i>
                                                                    <h5 class="fw-semibold mb-2">No Certifications</h5>
                                                                    <p class="text-muted mb-0">This trainer hasn't added any certifications yet.</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Testimonials Tab -->
                                        <div class="tab-pane p-0 border-0" id="testimonials-tab-pane" role="tabpanel" aria-labelledby="testimonials-tab" tabindex="0">
                                            <div class="row">
                                                <div class="col-xl-12">
                                                    <div class="card custom-card">
                                                        <div class="card-header">
                                                            <div class="card-title">Client Reviews & Testimonials</div>
                                                            <div class="ms-auto">
                                                                <div class="d-flex gap-2">
                                                                    <span class="badge bg-warning-transparent">
                                                                        <i class="ri-star-line me-1"></i>{{ number_format($user->receivedTestimonials->avg('rate') ?: 0, 1) }} Rating
                                                                    </span>
                                                                    <span class="badge bg-primary-transparent">
                                                                        {{ $user->receivedTestimonials->count() }} Reviews
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            @if($user->receivedTestimonials->count() > 0)
                                                                <!-- Statistics Row -->
                                                                <div class="row mb-4">
                                                                    <div class="col-md-3">
                                                                        <div class="text-center p-3 border rounded">
                                                                            <h4 class="fw-semibold mb-1 text-warning">{{ number_format($user->receivedTestimonials->avg('rate') ?: 0, 1) }}</h4>
                                                                            <small class="text-muted">Average Rating</small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="text-center p-3 border rounded">
                                                                            <h4 class="fw-semibold mb-1 text-primary">{{ $user->receivedTestimonials->count() }}</h4>
                                                                            <small class="text-muted">Total Reviews</small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="text-center p-3 border rounded">
                                                                            <h4 class="fw-semibold mb-1 text-success">{{ $user->receivedTestimonials->sum('likes') }}</h4>
                                                                            <small class="text-muted">Total Likes</small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="text-center p-3 border rounded">
                                                                            <h4 class="fw-semibold mb-1 text-danger">{{ $user->receivedTestimonials->sum('dislikes') }}</h4>
                                                                            <small class="text-muted">Total Dislikes</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Reviews List -->
                                                                <div class="row">
                                                                    @foreach($user->receivedTestimonials->take(6) as $testimonial)
                                                                    <div class="col-md-6 mb-3">
                                                                        <div class="card border">
                                                                            <div class="card-body">
                                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                                    <div class="d-flex align-items-center gap-2">
                                                                                        <span class="avatar avatar-sm avatar-rounded bg-info-transparent">
                                                                                            <i class="ri-user-line fs-14"></i>
                                                                                        </span>
                                                                                        <div>
                                                                                            <h6 class="mb-1">{{ $testimonial->name }}</h6>
                                                                                            <div class="d-flex align-items-center gap-1 mb-1">
                                                                                                @for($i = 1; $i <= 5; $i++)
                                                                                                    @if($i <= $testimonial->rate)
                                                                                                        <i class="ri-star-fill text-warning fs-12"></i>
                                                                                                    @else
                                                                                                        <i class="ri-star-line text-muted fs-12"></i>
                                                                                                    @endif
                                                                                                @endfor
                                                                                                <span class="ms-1 fw-semibold fs-12">{{ $testimonial->rate }}/5</span>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <small class="text-muted">{{ $testimonial->created_at->format('d M, Y') }}</small>
                                                                                </div>
                                                                                <p class="text-muted mb-2 fs-13">{{ Str::limit($testimonial->comments, 120) }}</p>
                                                                                <div class="d-flex gap-2">
                                                                                    <span class="badge bg-success-transparent fs-11">
                                                                                        <i class="ri-thumb-up-line me-1"></i>{{ $testimonial->likes }}
                                                                                    </span>
                                                                                    <span class="badge bg-danger-transparent fs-11">
                                                                                        <i class="ri-thumb-down-line me-1"></i>{{ $testimonial->dislikes }}
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                                
                                                                @if($user->receivedTestimonials->count() > 6)
                                                                <div class="text-center mt-3">
                                                                    <p class="text-muted">Showing 6 of {{ $user->receivedTestimonials->count() }} reviews</p>
                                                                </div>
                                                                @endif
                                                            @else
                                                                <div class="text-center py-4">
                                                                    <i class="ri-chat-3-line fs-48 text-muted mb-3"></i>
                                                                    <h5 class="fw-semibold mb-2">No Reviews Yet</h5>
                                                                    <p class="text-muted mb-0">This trainer hasn't received any client reviews yet.</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Specializations Tab -->
                                        <div class="tab-pane p-0 border-0" id="specializations-tab-pane" role="tabpanel" aria-labelledby="specializations-tab" tabindex="0">
                                            <div class="row">
                                                <div class="col-xl-12">
                                                    <div class="card custom-card">
                                                        <div class="card-header">
                                                            <div class="card-title">Professional Specializations</div>
                                                            <div class="ms-auto">
                                                                <span class="badge bg-primary-transparent">{{ $user->specializations ? $user->specializations->count() : 0 }} Specializations</span>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            @if($user->specializations && $user->specializations->count() > 0)
                                                                <div class="row">
                                                                    @foreach($user->specializations as $specialization)
                                                                    <div class="col-md-4 col-sm-6 mb-3">
                                                                        <div class="card border h-100">
                                                                            <div class="card-body text-center p-4">
                                                                                <div class="mb-3">
                                                                                    <span class="avatar avatar-lg avatar-rounded bg-primary-transparent">
                                                                                        <i class="ri-star-line fs-24"></i>
                                                                                    </span>
                                                                                </div>
                                                                                <h6 class="fw-semibold mb-2">{{ $specialization->name }}</h6>
                                                                                @if($specialization->description)
                                                                                <p class="text-muted fs-12 mb-3">{{ Str::limit($specialization->description, 100) }}</p>
                                                                                @else
                                                                                <p class="text-muted fs-12 mb-3">Specialized Area</p>
                                                                                @endif
                                                                                <div class="d-flex flex-column gap-2">
                                                                                    <span class="badge bg-success-transparent">Active</span>
                                                                                    <small class="text-muted">Added: {{ $specialization->pivot->created_at ? \Carbon\Carbon::parse($specialization->pivot->created_at)->format('d M, Y') : 'N/A' }}</small>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <div class="text-center py-4">
                                                                    <i class="ri-star-line fs-48 text-muted mb-3"></i>
                                                                    <h5 class="fw-semibold mb-2">No Specializations</h5>
                                                                    <p class="text-muted mb-0">This trainer hasn't added any specializations yet.</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Subscribed Clients Tab -->
                                        <div class="tab-pane p-0 border-0" id="clients-tab-pane" role="tabpanel" aria-labelledby="clients-tab" tabindex="0">
                                            <div class="row">
                                                @php
                                                    $subscribedClients = $user->subscriptionsAsTrainer()->where('status', 'active')->with('client')->get();
                                                @endphp
                                                @if($subscribedClients->count() > 0)
                                                    @foreach($subscribedClients as $subscription)
                                                        @if($subscription->client)
                                                        <div class="col-xl-4">
                                                            <div class="card custom-card">
                                                                <div class="card-body">
                                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                        <div class="lh-1">
                                                                            <span class="avatar avatar-lg bg-light border border-dashed p-1">
                                                                                @if($subscription->client->profile_image)
                                                                                    <img src="{{ asset('storage/' . $subscription->client->profile_image) }}" alt="{{ $subscription->client->name }}">
                                                                                @else
                                                                                    <div class="header-link-icon avatar bg-primary-transparent avatar-rounded w-100 h-100 d-flex align-items-center justify-content-center">
                                                                                        {{ strtoupper(substr($subscription->client->name, 0, 1)) }}
                                                                                    </div>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                        <div class="flex-fill">
                                                                            <span class="fw-semibold d-block">{{ $subscription->client->name }}</span>
                                                                            <span class="text-muted fs-13">{{ $subscription->client->email }}</span>
                                                                            @if($subscription->subscribed_at)
                                                                            <span class="d-block text-muted fs-11">Subscribed: {{ \Carbon\Carbon::parse($subscription->subscribed_at)->format('M d, Y') }}</span>
                                                                            @endif
                                                                        </div>
                                                                        <div>
                                                                            <span class="badge bg-success-transparent">Active</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <div class="col-xl-12">
                                                        <div class="card custom-card">
                                                            <div class="card-body">
                                                                <div class="text-center py-4">
                                                                    <i class="ri-user-line fs-48 text-muted mb-3"></i>
                                                                    <h5 class="fw-semibold mb-2">No Subscribed Clients</h5>
                                                                    <p class="text-muted mb-0">This trainer doesn't have any active client subscriptions yet.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->

@endsection

@section('scripts')

        <!-- GLightbox JS -->
        <script src="{{asset('build/assets/libs/glightbox/js/glightbox.min.js')}}"></script>
        <script>
            const lightbox = GLightbox({
                selector: '.glightbox'
            });
        </script>

@endsection