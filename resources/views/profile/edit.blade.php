@extends('layouts.master')

@section('styles')

@endsection

@section('content')

<!-- Start::page-header -->
<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-center justify-content-between flex-wrap">
        <h1 class="page-title fw-medium fs-18 mb-0">
            @if($isOwnProfile)
            Profile Settings
            @else
            Edit {{ $user->name }}'s Profile
            @endif
        </h1>
        <ol class="breadcrumb mb-0">
            @if(Auth::user()->role === 'admin' && !$isOwnProfile)
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user->id) }}">{{ $user->name }}</a></li>
            @else
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('profile.index') }}">Profile</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
        </ol>
    </div>
</div>
<!-- End::page-header -->

<!-- Display Success Messages -->
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="ri-check-circle-line me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Display Error Messages -->
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Start::row-1 -->
<div class="row">
    <form method="POST" action="@if($isOwnProfile){{ route('profile.update') }}@else{{ route('admin.users.update', $user->id) }}@endif" enctype="multipart/form-data" id="profileForm">
        @csrf
        @if(!$isOwnProfile)
        @method('PUT')
        @endif
        <input type="hidden" name="form_section" id="formSection" value="account">

        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Account
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row gy-3">
                        <div class="col-xl-6">
                            <div class="d-flex align-items-start flex-wrap gap-3">
                                <div>
                                    <span class="avatar avatar-xxl" id="profileAvatarContainer">
                                        @if($user->profile_image)
                                        <img id="profileAvatarImage" src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" onerror="this.onerror=null; this.src='{{ asset('build/assets/images/faces/9.jpg') }}';">
                                        @else
                                        <span class="avatar avatar-xxl bg-primary text-white d-flex align-items-center justify-content-center" id="profileAvatarImage" style="font-size:2rem;">
                                            <i class="ri-user-3-line"></i>
                                        </span>
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="fw-medium d-block mb-2">Profile Picture</span>
                                    <div class="btn-list mb-1">
                                        <input type="file" id="profileImage" name="profile_image" accept="image/*" style="display: none;">
                                        <button type="button" class="btn btn-sm btn-primary btn-wave" onclick="document.getElementById('profileImage').click()"><i class="ri-upload-2-line me-1"></i>Change Image</button>
                                        @if($user->profile_image)
                                        <button type="button" class="btn btn-sm btn-light btn-wave" onclick="deleteProfileImage()" id="deleteImageBtn"><i class="ri-delete-bin-line me-1"></i>Remove</button>
                                        @endif
                                    </div>
                                    <span class="d-block fs-12 text-muted">Use JPEG, PNG, or GIF. Best size: 200x200 pixels. Keep it under 5MB</span>
                                </div>
                            </div>
                        </div>

                        @if($user->role == 'trainer')
                        <div class="col-xl-6">
                            <div class="d-flex align-items-start flex-wrap gap-3">
                                <div>
                                    <span class="avatar avatar-xl">
                                        @if($user->business_logo)
                                        <img id="businessLogoPreview" src="{{ asset('storage/' . $user->business_logo) }}" alt="" onerror="this.onerror=null; this.src='{{ asset('build/assets/images/logo.png') }}';">
                                        @else
                                        <img id="businessLogoPreview" src="{{ asset('build/assets/images/logo.png') }}" alt="">
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="fw-medium d-block mb-2">Business Logo</span>
                                    <div class="btn-list mb-1">
                                        <input type="file" id="businessLogo" name="business_logo" accept="image/*" style="display: none;">
                                        <button type="button" class="btn btn-sm btn-primary btn-wave" onclick="document.getElementById('businessLogo').click()"><i class="ri-upload-2-line me-1"></i>Change Logo</button>
                                        @if($user->business_logo)
                                        <button type="button" class="btn btn-sm btn-light btn-wave" onclick="deleteBusinessLogo()" id="deleteBusinessLogoBtn"><i class="ri-delete-bin-line me-1"></i>Remove</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-xl-6">
                            <label for="profile-user-name" class="form-label">User Name :</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="profile-user-name" name="name" value="{{ old('name', $user->name) }}" placeholder="Enter Name">
                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-email" class="form-label">Email :</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="profile-email" name="email" value="{{ old('email', $user->email) }}" placeholder="Enter Email">
                            @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-phn-no" class="form-label">Phone No :</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="profile-phn-no" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Enter Number">
                            @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-timezone" class="form-label">Timezone :</label>
                            <select class="form-control @error('timezone') is-invalid @enderror" id="profile-timezone" name="timezone" data-trigger>
                                <option value="UTC" {{ old('timezone', $user->timezone ?? 'UTC') === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                                <optgroup label="UTC Positive Offsets">
                                    <option value="UTC+1" {{ old('timezone', $user->timezone) === 'UTC+1' ? 'selected' : '' }}>UTC+1 (Central European Time)</option>
                                    <option value="UTC+2" {{ old('timezone', $user->timezone) === 'UTC+2' ? 'selected' : '' }}>UTC+2 (Eastern European Time)</option>
                                    <option value="UTC+3" {{ old('timezone', $user->timezone) === 'UTC+3' ? 'selected' : '' }}>UTC+3 (Moscow Time)</option>
                                    <option value="UTC+3:30" {{ old('timezone', $user->timezone) === 'UTC+3:30' ? 'selected' : '' }}>UTC+3:30 (Iran Time)</option>
                                    <option value="UTC+4" {{ old('timezone', $user->timezone) === 'UTC+4' ? 'selected' : '' }}>UTC+4 (Gulf Standard Time)</option>
                                    <option value="UTC+4:30" {{ old('timezone', $user->timezone) === 'UTC+4:30' ? 'selected' : '' }}>UTC+4:30 (Afghanistan Time)</option>
                                    <option value="UTC+5" {{ old('timezone', $user->timezone) === 'UTC+5' ? 'selected' : '' }}>UTC+5 (Pakistan Standard Time)</option>
                                    <option value="UTC+5:30" {{ old('timezone', $user->timezone) === 'UTC+5:30' ? 'selected' : '' }}>UTC+5:30 (India Standard Time)</option>
                                    <option value="UTC+5:45" {{ old('timezone', $user->timezone) === 'UTC+5:45' ? 'selected' : '' }}>UTC+5:45 (Nepal Time)</option>
                                    <option value="UTC+6" {{ old('timezone', $user->timezone) === 'UTC+6' ? 'selected' : '' }}>UTC+6 (Bangladesh Time)</option>
                                    <option value="UTC+6:30" {{ old('timezone', $user->timezone) === 'UTC+6:30' ? 'selected' : '' }}>UTC+6:30 (Myanmar Time)</option>
                                    <option value="UTC+7" {{ old('timezone', $user->timezone) === 'UTC+7' ? 'selected' : '' }}>UTC+7 (Indochina Time)</option>
                                    <option value="UTC+8" {{ old('timezone', $user->timezone) === 'UTC+8' ? 'selected' : '' }}>UTC+8 (China/Singapore Time)</option>
                                    <option value="UTC+9" {{ old('timezone', $user->timezone) === 'UTC+9' ? 'selected' : '' }}>UTC+9 (Japan/Korea Time)</option>
                                    <option value="UTC+9:30" {{ old('timezone', $user->timezone) === 'UTC+9:30' ? 'selected' : '' }}>UTC+9:30 (Australian Central Time)</option>
                                    <option value="UTC+10" {{ old('timezone', $user->timezone) === 'UTC+10' ? 'selected' : '' }}>UTC+10 (Australian Eastern Time)</option>
                                    <option value="UTC+11" {{ old('timezone', $user->timezone) === 'UTC+11' ? 'selected' : '' }}>UTC+11 (Solomon Islands Time)</option>
                                    <option value="UTC+12" {{ old('timezone', $user->timezone) === 'UTC+12' ? 'selected' : '' }}>UTC+12 (New Zealand Time)</option>
                                    <option value="UTC+13" {{ old('timezone', $user->timezone) === 'UTC+13' ? 'selected' : '' }}>UTC+13 (Tonga Time)</option>
                                    <option value="UTC+14" {{ old('timezone', $user->timezone) === 'UTC+14' ? 'selected' : '' }}>UTC+14 (Line Islands Time)</option>
                                </optgroup>
                                <optgroup label="UTC Negative Offsets">
                                    <option value="UTC-1" {{ old('timezone', $user->timezone) === 'UTC-1' ? 'selected' : '' }}>UTC-1 (Azores Time)</option>
                                    <option value="UTC-2" {{ old('timezone', $user->timezone) === 'UTC-2' ? 'selected' : '' }}>UTC-2 (South Georgia Time)</option>
                                    <option value="UTC-3" {{ old('timezone', $user->timezone) === 'UTC-3' ? 'selected' : '' }}>UTC-3 (Argentina Time)</option>
                                    <option value="UTC-3:30" {{ old('timezone', $user->timezone) === 'UTC-3:30' ? 'selected' : '' }}>UTC-3:30 (Newfoundland Time)</option>
                                    <option value="UTC-4" {{ old('timezone', $user->timezone) === 'UTC-4' ? 'selected' : '' }}>UTC-4 (Atlantic Time)</option>
                                    <option value="UTC-5" {{ old('timezone', $user->timezone) === 'UTC-5' ? 'selected' : '' }}>UTC-5 (Eastern Time)</option>
                                    <option value="UTC-6" {{ old('timezone', $user->timezone) === 'UTC-6' ? 'selected' : '' }}>UTC-6 (Central Time)</option>
                                    <option value="UTC-7" {{ old('timezone', $user->timezone) === 'UTC-7' ? 'selected' : '' }}>UTC-7 (Mountain Time)</option>
                                    <option value="UTC-8" {{ old('timezone', $user->timezone) === 'UTC-8' ? 'selected' : '' }}>UTC-8 (Pacific Time)</option>
                                    <option value="UTC-9" {{ old('timezone', $user->timezone) === 'UTC-9' ? 'selected' : '' }}>UTC-9 (Alaska Time)</option>
                                    <option value="UTC-10" {{ old('timezone', $user->timezone) === 'UTC-10' ? 'selected' : '' }}>UTC-10 (Hawaii Time)</option>
                                    <option value="UTC-11" {{ old('timezone', $user->timezone) === 'UTC-11' ? 'selected' : '' }}>UTC-11 (Samoa Time)</option>
                                    <option value="UTC-12" {{ old('timezone', $user->timezone) === 'UTC-12' ? 'selected' : '' }}>UTC-12 (Baker Island Time)</option>
                                </optgroup>
                            </select>
                            @error('timezone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        @if($canChangeRole)
                        <div class="col-xl-6">
                            <label for="profile-role" class="form-label">Role :</label>
                            <select class="form-control @error('role') is-invalid @enderror" id="profile-role" name="role" data-trigger>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="trainer" {{ $user->role === 'trainer' ? 'selected' : '' }}>Trainer</option>
                                <option value="client" {{ $user->role === 'client' ? 'selected' : '' }}>Client</option>
                            </select>
                            @error('role')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        @endif

                        @if(Auth::user()->role === 'admin')
                        <div class="col-xl-6">
                            <label for="profile-status" class="form-label">Account Status :</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="profile-status" name="status">
                                <option value="1" {{ old('status', $user->email_verified_at ? 1 : 0) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $user->email_verified_at ? 1 : 0) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        @endif

                        <div class="col-xl-6">
                            <label for="profile-created" class="form-label">Member Since :</label>
                            <input type="text" class="form-control" id="profile-created" value="{{ \Carbon\Carbon::parse($user->created_at)->format('F d, Y') }}" readonly>
                        </div>
                        <!-- <div class="col-xl-6">
                            <label for="profile-verified" class="form-label">Email Status :</label>
                            <input type="text" class="form-control" id="profile-verified" value="{{ $user->email_verified_at ? 'Verified' : 'Not Verified' }}" readonly>
                        </div> -->
                        
                        @if(!$isOwnProfile && Auth::user()->role === 'admin')
                        <div class="col-xl-12">
                            <div class="border-top pt-3 mt-3">
                                <h6 class="fw-semibold mb-3">Change Password</h6>
                                <div class="row gy-3">
                                    <div class="col-xl-6">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter New Password">
                                        @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                        <small class="text-muted">Leave blank to keep current password. Minimum 8 characters.</small>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" placeholder="Confirm New Password">
                                        @error('password_confirmation')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                        <small class="text-muted">Re-enter the new password to confirm.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        @if($isOwnProfile)
                        <a href="{{ route('profile.index') }}" class="btn btn-light btn-wave">Cancel</a>
                        @else
                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-light btn-wave">Cancel</a>
                        @endif
                        <button type="button" id="saveAccountBtn" class="btn btn-primary btn-wave float-end">Save Account</button>
                    </div>
                </div>
            </div>

            {{-- Trainer-specific fields - Separate card for trainers --}}
            @if($user->role === 'trainer')
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            Trainer Profile Information
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row gy-3">
                            <div class="col-xl-6">
                                <label for="profile-designation" class="form-label">Designation :</label>
                                <input type="text" class="form-control @error('designation') is-invalid @enderror" id="profile-designation" name="designation" value="{{ old('designation', $user->designation) }}" placeholder="Enter Designation">
                                @error('designation')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="col-xl-6">
                                <label for="profile-experience" class="form-label">Experience :</label>
                                <select class="form-control @error('experience') is-invalid @enderror" id="profile-experience" name="experience" data-trigger>
                                    <option value="">Select Experience Level</option>
                                    <option value="less_than_1_year" {{ old('experience', $user->experience) === 'less_than_1_year' ? 'selected' : '' }}>Less than 1 year</option>
                                    <option value="1_year" {{ old('experience', $user->experience) === '1_year' ? 'selected' : '' }}>1 year</option>
                                    <option value="2_years" {{ old('experience', $user->experience) === '2_years' ? 'selected' : '' }}>2 years</option>
                                    <option value="3_years" {{ old('experience', $user->experience) === '3_years' ? 'selected' : '' }}>3 years</option>
                                    <option value="4_years" {{ old('experience', $user->experience) === '4_years' ? 'selected' : '' }}>4 years</option>
                                    <option value="5_years" {{ old('experience', $user->experience) === '5_years' ? 'selected' : '' }}>5 years</option>
                                    <option value="6_years" {{ old('experience', $user->experience) === '6_years' ? 'selected' : '' }}>6 years</option>
                                    <option value="7_years" {{ old('experience', $user->experience) === '7_years' ? 'selected' : '' }}>7 years</option>
                                    <option value="8_years" {{ old('experience', $user->experience) === '8_years' ? 'selected' : '' }}>8 years</option>
                                    <option value="9_years" {{ old('experience', $user->experience) === '9_years' ? 'selected' : '' }}>9 years</option>
                                    <option value="10_years" {{ old('experience', $user->experience) === '10_years' ? 'selected' : '' }}>10 years</option>
                                    <option value="more_than_10_years" {{ old('experience', $user->experience) === 'more_than_10_years' ? 'selected' : '' }}>More than 10 years</option>
                                </select>
                                @error('experience')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="col-xl-12">
                                <label for="profile-about" class="form-label">About Me :</label>
                                <textarea class="form-control @error('about') is-invalid @enderror" id="profile-about" name="about" rows="3" placeholder="Tell clients about yourself, your background, and what makes you unique as a trainer...">{{ old('about', $user->about) }}</textarea>
                                @error('about')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="col-xl-12">
                                <label for="profile-training-philosophy" class="form-label">Training Philosophy :</label>
                                <textarea class="form-control @error('training_philosophy') is-invalid @enderror" id="profile-training-philosophy" name="training_philosophy" rows="3" placeholder="Describe your approach to training, your beliefs about fitness, and how you help clients achieve their goals...">{{ old('training_philosophy', $user->training_philosophy) }}</textarea>
                                @error('training_philosophy')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" id="saveTrainerBtn" class="btn btn-primary btn-wave">Save Trainer Info</button>
                        </div>
                    </div>
            </div>
        </div>
        @endif
        
    </form>
        
        {{-- Location Section --}}
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            Location Information
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4" id="locationDisplay">
                            @if($user->location)
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-success-transparent me-2">
                                    <i class="ri-map-pin-fill me-1"></i>Location Set
                                </span>
                                <span class="badge" id="locationStatusBadge">
                                    @if($user->location->country && $user->location->state && $user->location->city)
                                        bg-info-transparent">Complete
                                    @else
                                        bg-warning-transparent">Incomplete
                                    @endif
                                </span>
                            </div>
                            <div class="text-muted mb-3" id="currentAddress">
                                <strong>Current Address:</strong>
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
                            </div>
                            @else
                            <div class="text-center py-3" id="noLocation">
                                <i class="ri-map-pin-line fs-24 text-muted mb-2"></i>
                                <p class="text-muted mb-0">No location information available.</p>
                            </div>
                            @endif
                        </div>

                        <div class="border-top pt-4">
                            <h6 class="fw-semibold mb-3">{{ $user->location ? 'Update Location' : 'Add Location' }}</h6>
                            <form method="POST" action="{{ route('profile.update-location') }}" id="locationForm">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                @if($user->location)
                                    <input type="hidden" name="location_id" value="{{ $user->location->id }}">
                                @endif
                                <div class="row gy-3">
                                    <div class="col-xl-6">
                                        <label for="location-country" class="form-label">Country</label>
                                        <input type="text" class="form-control @error('country') is-invalid @enderror" id="location-country" name="country" value="{{ old('country', $user->location->country ?? '') }}" placeholder="Enter country name" maxlength="100">
                                        @error('country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="location-state" class="form-label">State/Province</label>
                                        <input type="text" class="form-control @error('state') is-invalid @enderror" id="location-state" name="state" value="{{ old('state', $user->location->state ?? '') }}" placeholder="Enter state or province" maxlength="100">
                                        @error('state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="location-city" class="form-label">City</label>
                                        <input type="text" class="form-control @error('city') is-invalid @enderror" id="location-city" name="city" value="{{ old('city', $user->location->city ?? '') }}" placeholder="Enter city name" maxlength="100">
                                        @error('city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="location-zipcode" class="form-label">Zip/Postal Code</label>
                                        <input type="text" class="form-control @error('zipcode') is-invalid @enderror" id="location-zipcode" name="zipcode" value="{{ old('zipcode', $user->location->zipcode ?? '') }}" placeholder="Enter zip/postal code" maxlength="20">
                                        @error('zipcode')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-xl-12">
                                        <label for="location-address" class="form-label">Street Address</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" id="location-address" name="address" rows="2" placeholder="Enter street address" maxlength="255">{{ old('address', $user->location->address ?? '') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-xl-12">
                                        <button type="submit" class="btn btn-primary btn-wave">
                                            <i class="ri-save-line me-1"></i>{{ $user->location ? 'Update Location' : 'Add Location' }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

    </form>
</div>
<!--End::row-1 -->

<!-- Hidden form for deleting profile image -->
@if($user->profile_image)
<form id="deleteImageForm" method="POST" action="{{ route('profile.delete-image') }}" style="display: none;">
    @csrf
</form>
@endif

@if($user->role === 'trainer' && $user->business_logo)
<form id="deleteBusinessLogoForm" method="POST" action="{{ route('profile.delete-business-logo') }}" style="display: none;">
    @csrf
</form>
@endif

@endsection

@section('scripts')
<script>
    /**
     * Handle profile image preview and update avatar display
     */
    document.getElementById('profileImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            console.log('File selected:', file.name);

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: 'Please select a valid image file (JPEG, PNG, JPG, or GIF)',
                    confirmButtonText: 'OK'
                });
                this.value = '';
                return;
            }

            // Validate file size (2MB = 2048KB)
            if (file.size > 2048 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: 'File size must be less than 2MB',
                    confirmButtonText: 'OK'
                });
                this.value = '';
                return;
            }

            // Preview the selected image
            const reader = new FileReader();
            reader.onload = function(e) {
                const avatarContainer = document.getElementById('profileAvatarContainer');
                let avatarImg = document.getElementById('profileAvatarImage');
                
                if (avatarContainer) {
                    // Check if current element is a span (placeholder) or img
                    if (avatarImg && avatarImg.tagName === 'SPAN') {
                        // Replace span with img element
                        const newImg = document.createElement('img');
                        newImg.id = 'profileAvatarImage';
                        newImg.src = e.target.result;
                        newImg.alt = '{{ $user->name }}';
                        newImg.onerror = function() {
                            this.onerror = null;
                            this.src = '{{ asset('build/assets/images/faces/9.jpg') }}';
                        };
                        avatarContainer.replaceChild(newImg, avatarImg);
                        avatarImg = newImg;
                    } else if (avatarImg && avatarImg.tagName === 'IMG') {
                        // Update existing image src
                        avatarImg.src = e.target.result;
                    } else {
                        // Create new img element if neither exists
                        avatarImg = document.createElement('img');
                        avatarImg.id = 'profileAvatarImage';
                        avatarImg.src = e.target.result;
                        avatarImg.alt = '{{ $user->name }}';
                        avatarImg.onerror = function() {
                            this.onerror = null;
                            this.src = '{{ asset('build/assets/images/faces/9.jpg') }}';
                        };
                        avatarContainer.appendChild(avatarImg);
                    }
                }

                // Show delete button if it doesn't exist
                const deleteBtn = document.getElementById('deleteImageBtn');
                if (!deleteBtn) {
                    const btnList = document.querySelector('.btn-list');
                    if (btnList) {
                        const newDeleteBtn = document.createElement('button');
                        newDeleteBtn.type = 'button';
                        newDeleteBtn.className = 'btn btn-sm btn-light btn-wave';
                        newDeleteBtn.id = 'deleteImageBtn';
                        newDeleteBtn.onclick = deleteProfileImage;
                        newDeleteBtn.innerHTML = '<i class="ri-delete-bin-line me-1"></i>Remove';
                        btnList.appendChild(newDeleteBtn);
                    }
                }
            };
            reader.readAsDataURL(file);
        }
    });

    const businessLogoInput = document.getElementById('businessLogo');
    if (businessLogoInput) {
        businessLogoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Please select a valid image file (JPEG, PNG, JPG, GIF, or WEBP)',
                        confirmButtonText: 'OK'
                    });
                    this.value = '';
                    return;
                }
                if (file.size > 2048 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'File size must be less than 2MB',
                        confirmButtonText: 'OK'
                    });
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('businessLogoPreview');
                    if (img) {
                        img.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function deleteBusinessLogo() {
        Swal.fire({
            title: 'Delete Business Logo?',
            text: 'Are you sure you want to delete your business logo? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const deleteForm = document.getElementById('deleteBusinessLogoForm');
                if (deleteForm) {
                    deleteForm.submit();
                }
            }
        });
    }

    /**
     * Delete profile image function
     */
    function deleteProfileImage() {
        Swal.fire({
            title: 'Delete Profile Image?',
            text: 'Are you sure you want to delete your profile image? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const deleteForm = document.getElementById('deleteImageForm');
                if (deleteForm) {
                    deleteForm.submit();
                }
            }
        });
    }

    /**
     * Auto-hide alerts after 5 seconds
     */
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                if (alert && alert.classList.contains('show')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        });

        // Handle role change to show/hide trainer fields
        const roleSelect = document.getElementById('profile-role');
        if (roleSelect) {
            roleSelect.addEventListener('change', function() {
                if (this.value === 'trainer') {
                    // Show message that trainer fields will be available after save
                    Swal.fire({
                        icon: 'info',
                        title: 'Role Changed to Trainer',
                        text: 'After changing role to Trainer, please save and then edit again to add trainer-specific information.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        // Handle Save Account button
        const saveAccountBtn = document.getElementById('saveAccountBtn');
        if (saveAccountBtn) {
            saveAccountBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = document.getElementById('profileForm');
                const formSection = document.getElementById('formSection');
                if (form && formSection) {
                    formSection.value = 'account';
                    submitProfileForm(form, saveAccountBtn);
                }
            });
        }

        // Handle Save Trainer Info button
        const saveTrainerBtn = document.getElementById('saveTrainerBtn');
        if (saveTrainerBtn) {
            saveTrainerBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = document.getElementById('profileForm');
                const formSection = document.getElementById('formSection');
                if (form && formSection) {
                    formSection.value = 'trainer';
                    submitProfileForm(form, saveTrainerBtn);
                }
            });
        }
    });

    /**
     * Submit profile form via AJAX
     */
    function submitProfileForm(form, submitBtn) {
        const formData = new FormData(form);
        const originalText = submitBtn.innerHTML;
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ri-loader-4-line me-1"></i>Saving...';
        
        // Clear previous errors
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        
        // Get method from form
        const method = form.querySelector('input[name="_method"]')?.value || 'POST';
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
            },
            redirect: 'follow'
        })
        .then(response => {
            // Check if response is a redirect
            if (response.redirected) {
                window.location.href = response.url;
                return { success: true };
            }
            
            // Check content type
            const contentType = response.headers.get('content-type') || '';
            
            // If HTML response (likely a redirect page), reload
            if (contentType.includes('text/html')) {
                window.location.reload();
                return { success: true };
            }
            
            // Try to parse as JSON
            if (contentType.includes('application/json')) {
                return response.json();
            }
            
            // Default: try to parse as JSON, fallback to success
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    // If can't parse, assume success and reload
                    window.location.reload();
                    return { success: true };
                }
            });
        })
        .then(data => {
            if (data && data.success !== false) {
                showAlert('success', data.message || 'Profile updated successfully!');
                // Reload page after a short delay to show the success message
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('danger', data.message || 'Failed to update profile');
                
                // Display validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback';
                            errorDiv.textContent = data.errors[field][0];
                            input.parentNode.appendChild(errorDiv);
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', error.message || 'An error occurred. Please try again.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    /**
     * Show alert message using SweetAlert
     */
    function showAlert(type, message) {
        const iconMap = {
            'success': 'success',
            'error': 'error',
            'danger': 'error',
            'warning': 'warning',
            'info': 'info'
        };
        
        Swal.fire({
            icon: iconMap[type] || 'info',
            title: type === 'success' ? 'Success!' : type === 'error' || type === 'danger' ? 'Error!' : type === 'warning' ? 'Warning!' : 'Info',
            text: message,
            confirmButtonText: 'OK',
            timer: 5000,
            timerProgressBar: true,
            showCloseButton: true
        });
    }

    /**
     * Handle Location Form Submission
     */
    const locationForm = document.getElementById('locationForm');
    if (locationForm) {
        console.log('Location form found:', locationForm.action);
        locationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            const form = this;
            
            // Extract form values for fallback
            const locationFormData = {
                country: formData.get('country') || '',
                state: formData.get('state') || '',
                city: formData.get('city') || '',
                address: formData.get('address') || '',
                zipcode: formData.get('zipcode') || ''
            };
            
            console.log('Submitting location to:', this.action, 'Method:', this.querySelector('input[name="_method"]')?.value || 'POST');
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line me-1"></i>Saving...';
            
            // Clear previous errors
            this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            this.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            
            const method = this.querySelector('input[name="_method"]')?.value || 'POST';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
                }
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || `HTTP error! status: ${response.status}`);
                    }).catch(() => {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    });
                }
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // Try to parse as JSON anyway
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            // If HTML redirect, treat as success
                            return { success: true, message: 'Location saved successfully', data: locationFormData };
                        }
                    });
                }
            })
            .then(data => {
                if (data.success || (data.message && !data.message.includes('Failed'))) {
                    // Update location display
                    const locationData = data.data || locationFormData;
                    updateLocationDisplay(locationData);
                    
                    // Update form to include location_id if we got one back
                    if (data.data && data.data.id) {
                        let locationIdInput = form.querySelector('input[name="location_id"]');
                        if (!locationIdInput) {
                            locationIdInput = document.createElement('input');
                            locationIdInput.type = 'hidden';
                            locationIdInput.name = 'location_id';
                            form.appendChild(locationIdInput);
                        }
                        locationIdInput.value = data.data.id;
                        
                        // Update heading text
                        const heading = form.closest('.card-body').querySelector('h6');
                        if (heading) {
                            heading.textContent = 'Update Location';
                        }
                        submitBtn.innerHTML = '<i class="ri-save-line me-1"></i>Update Location';
                    }
                    
                    // Show success message
                    showAlert('success', data.message || 'Location saved successfully!');
                } else {
                    showAlert('danger', data.message || 'Failed to save location');
                    
                    // Display validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                errorDiv.textContent = data.errors[field][0];
                                input.parentNode.appendChild(errorDiv);
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', error.message || 'An error occurred. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    /**
     * Update location display
     */
    function updateLocationDisplay(locationData) {
        const locationDisplay = document.getElementById('locationDisplay');
        const noLocation = document.getElementById('noLocation');
        
        // Build address string
        const addressParts = [];
        if (locationData.address) addressParts.push(locationData.address);
        if (locationData.city) addressParts.push(locationData.city);
        if (locationData.state) addressParts.push(locationData.state);
        if (locationData.country) addressParts.push(locationData.country);
        const address = addressParts.join(', ');
        const zipcode = locationData.zipcode ? ` - ${locationData.zipcode}` : '';
        const fullAddress = address + zipcode;
        
        // Check if complete
        const isComplete = locationData.country && locationData.state && locationData.city;
        
        if (noLocation) {
            noLocation.remove();
        }
        
        // Create or update location display
        let locationInfo = locationDisplay.querySelector('.location-info');
        if (!locationInfo) {
            locationInfo = document.createElement('div');
            locationInfo.className = 'mb-4 location-info';
            locationDisplay.insertBefore(locationInfo, locationDisplay.firstChild);
        }
        
        locationInfo.innerHTML = `
            <div class="d-flex align-items-center mb-2">
                <span class="badge bg-success-transparent me-2">
                    <i class="ri-map-pin-fill me-1"></i>Location Set
                </span>
                <span class="badge ${isComplete ? 'bg-info-transparent' : 'bg-warning-transparent'}" id="locationStatusBadge">
                    ${isComplete ? 'Complete' : 'Incomplete'}
                </span>
            </div>
            <div class="text-muted mb-3" id="currentAddress">
                <strong>Current Address:</strong> ${fullAddress || 'Not specified'}
            </div>
        `;
    }
</script>
@endsection