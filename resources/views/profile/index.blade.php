@extends('layouts.master')

@section('styles')
<style>
.modal-backdrop {
    z-index: 1040;
}
.modal {
    z-index: 1050;
}
.profile-image-preview {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #e9ecef;
}
.image-upload-container {
    position: relative;
    display: inline-block;
}
.image-upload-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
    cursor: pointer;
}
.image-upload-container:hover .image-upload-overlay {
    opacity: 1;
}
</style>
@endsection

@section('content')

<!-- Start::page-header -->
<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-center justify-content-between flex-wrap">
        <h1 class="page-title fw-medium fs-18 mb-0">My Profile</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol>
    </div>
</div>
<!-- End::page-header -->

<!-- Alert Messages -->
<div id="alert-container"></div>

<!-- Profile Overview -->
<div class="row">
    <div class="col-xl-4">
        <div class="card custom-card">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($user->profile_image)
                        <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="avatar avatar-xxl avatar-rounded">
                    @else
                        <span class="avatar avatar-xxl avatar-rounded bg-primary-transparent">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                    @endif
                </div>
                <h5 class="fw-semibold mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-2">{{ ucfirst($user->role) }}</p>
                <p class="text-muted fs-12 mb-3">
                    <i class="ri-mail-line me-1"></i>{{ $user->email }}
                </p>
                <p class="text-muted fs-12 mb-3">
                    <i class="ri-phone-line me-1"></i>{{ $user->phone ?: 'Not provided' }}
                </p>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h6 class="fw-semibold mb-0">{{ ceil(\Carbon\Carbon::parse($user->created_at)->diffInDays()) }}</h6>
                            <span class="text-muted fs-12">Days Active</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6 class="fw-semibold mb-0">{{ $user->email_verified_at ? 'Verified' : 'Pending' }}</h6>
                        <span class="text-muted fs-12">Email Status</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- About Section -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    About 
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted">User profile information and account details.</p>
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
                            <i class="ri-calendar-line align-middle fs-15"></i>
                        </span>
                        <span class="fw-medium text-default">Member Since : </span> {{ \Carbon\Carbon::parse($user->created_at)->format('M Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-8">
        <!-- Profile Information -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">
                    Profile Information
                </div>
                <div class="ms-auto">
                    <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary">
                        <i class="ri-edit-line me-1"></i>Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
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
                            <p class="text-muted mb-0">{{ $user->email_verified_at ? 'Verified' : 'Not Verified' }}</p>
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
                        
                        @if($user->about)
                        <div class="col-xl-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">About Me</label>
                                <p class="text-muted mb-0">{{ $user->about }}</p>
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
        
        <!-- Account Actions -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    Account Settings
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">
                        <i class="ri-edit-line me-1"></i>Edit Profile
                    </a>
                    <a href="{{ route('profile.change-password') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ri-lock-password-line me-1"></i>Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

@endsection