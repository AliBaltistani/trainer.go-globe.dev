@extends('layouts.master')

@section('styles')

@endsection

@section('content')

<!-- Start::page-header -->
<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-center justify-content-between flex-wrap">
        <h1 class="page-title fw-medium fs-18 mb-0">
            Create New User
        </h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create User</li>
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
    <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" id="profileForm">
        @csrf
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
                                        <span class="avatar avatar-xxl bg-primary text-white d-flex align-items-center justify-content-center" id="profileAvatarImage" style="font-size:2rem;">
                                            <i class="ri-user-3-line"></i>
                                        </span>
                                    </span>
                                </div>
                                <div>
                                    <span class="fw-medium d-block mb-2">Profile Picture</span>
                                    <div class="btn-list mb-1">
                                        <input type="file" id="profileImage" name="profile_image" accept="image/*" style="display: none;">
                                        <button type="button" class="btn btn-sm btn-primary btn-wave" onclick="document.getElementById('profileImage').click()"><i class="ri-upload-2-line me-1"></i>Upload Image</button>
                                        <button type="button" class="btn btn-sm btn-light btn-wave" onclick="clearProfileImage()" id="clearImageBtn" style="display: none;"><i class="ri-close-line me-1"></i>Clear</button>
                                    </div>
                                    <span class="d-block fs-12 text-muted">Use JPEG, PNG, or GIF. Best size: 200x200 pixels. Keep it under 5MB</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6" id="businessLogoSection" style="display: none;">
                            <div class="d-flex align-items-start flex-wrap gap-3">
                                <div>
                                    <span class="avatar avatar-xl">
                                        <img id="businessLogoPreview" src="{{ asset('build/assets/images/logo.png') }}" alt="">
                                    </span>
                                </div>
                                <div>
                                    <span class="fw-medium d-block mb-2">Business Logo</span>
                                    <div class="btn-list mb-1">
                                        <input type="file" id="businessLogo" name="business_logo" accept="image/*" style="display: none;">
                                        <button type="button" class="btn btn-sm btn-primary btn-wave" onclick="document.getElementById('businessLogo').click()"><i class="ri-upload-2-line me-1"></i>Upload Logo</button>
                                        <button type="button" class="btn btn-sm btn-light btn-wave" onclick="clearBusinessLogo()" id="clearBusinessLogoBtn" style="display: none;"><i class="ri-close-line me-1"></i>Clear</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-user-name" class="form-label">User Name :</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="profile-user-name" name="name" value="{{ old('name') }}" placeholder="Enter Name" required>
                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-email" class="form-label">Email :</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="profile-email" name="email" value="{{ old('email') }}" placeholder="Enter Email" required>
                            @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-phn-no" class="form-label">Phone No :</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="profile-phn-no" name="phone" value="{{ old('phone') }}" placeholder="Enter Number">
                            @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-timezone" class="form-label">Timezone :</label>
                            <select class="form-control @error('timezone') is-invalid @enderror" id="profile-timezone" name="timezone" data-trigger>
                                <option value="UTC" {{ old('timezone', 'UTC') === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                                <optgroup label="UTC Positive Offsets">
                                    <option value="UTC+1" {{ old('timezone') === 'UTC+1' ? 'selected' : '' }}>UTC+1 (Central European Time)</option>
                                    <option value="UTC+2" {{ old('timezone') === 'UTC+2' ? 'selected' : '' }}>UTC+2 (Eastern European Time)</option>
                                    <option value="UTC+3" {{ old('timezone') === 'UTC+3' ? 'selected' : '' }}>UTC+3 (Moscow Time)</option>
                                    <option value="UTC+3:30" {{ old('timezone') === 'UTC+3:30' ? 'selected' : '' }}>UTC+3:30 (Iran Time)</option>
                                    <option value="UTC+4" {{ old('timezone') === 'UTC+4' ? 'selected' : '' }}>UTC+4 (Gulf Standard Time)</option>
                                    <option value="UTC+4:30" {{ old('timezone') === 'UTC+4:30' ? 'selected' : '' }}>UTC+4:30 (Afghanistan Time)</option>
                                    <option value="UTC+5" {{ old('timezone') === 'UTC+5' ? 'selected' : '' }}>UTC+5 (Pakistan Standard Time)</option>
                                    <option value="UTC+5:30" {{ old('timezone') === 'UTC+5:30' ? 'selected' : '' }}>UTC+5:30 (India Standard Time)</option>
                                    <option value="UTC+5:45" {{ old('timezone') === 'UTC+5:45' ? 'selected' : '' }}>UTC+5:45 (Nepal Time)</option>
                                    <option value="UTC+6" {{ old('timezone') === 'UTC+6' ? 'selected' : '' }}>UTC+6 (Bangladesh Time)</option>
                                    <option value="UTC+6:30" {{ old('timezone') === 'UTC+6:30' ? 'selected' : '' }}>UTC+6:30 (Myanmar Time)</option>
                                    <option value="UTC+7" {{ old('timezone') === 'UTC+7' ? 'selected' : '' }}>UTC+7 (Indochina Time)</option>
                                    <option value="UTC+8" {{ old('timezone') === 'UTC+8' ? 'selected' : '' }}>UTC+8 (China/Singapore Time)</option>
                                    <option value="UTC+9" {{ old('timezone') === 'UTC+9' ? 'selected' : '' }}>UTC+9 (Japan/Korea Time)</option>
                                    <option value="UTC+9:30" {{ old('timezone') === 'UTC+9:30' ? 'selected' : '' }}>UTC+9:30 (Australian Central Time)</option>
                                    <option value="UTC+10" {{ old('timezone') === 'UTC+10' ? 'selected' : '' }}>UTC+10 (Australian Eastern Time)</option>
                                    <option value="UTC+11" {{ old('timezone') === 'UTC+11' ? 'selected' : '' }}>UTC+11 (Solomon Islands Time)</option>
                                    <option value="UTC+12" {{ old('timezone') === 'UTC+12' ? 'selected' : '' }}>UTC+12 (New Zealand Time)</option>
                                    <option value="UTC+13" {{ old('timezone') === 'UTC+13' ? 'selected' : '' }}>UTC+13 (Tonga Time)</option>
                                    <option value="UTC+14" {{ old('timezone') === 'UTC+14' ? 'selected' : '' }}>UTC+14 (Line Islands Time)</option>
                                </optgroup>
                                <optgroup label="UTC Negative Offsets">
                                    <option value="UTC-1" {{ old('timezone') === 'UTC-1' ? 'selected' : '' }}>UTC-1 (Azores Time)</option>
                                    <option value="UTC-2" {{ old('timezone') === 'UTC-2' ? 'selected' : '' }}>UTC-2 (South Georgia Time)</option>
                                    <option value="UTC-3" {{ old('timezone') === 'UTC-3' ? 'selected' : '' }}>UTC-3 (Argentina Time)</option>
                                    <option value="UTC-3:30" {{ old('timezone') === 'UTC-3:30' ? 'selected' : '' }}>UTC-3:30 (Newfoundland Time)</option>
                                    <option value="UTC-4" {{ old('timezone') === 'UTC-4' ? 'selected' : '' }}>UTC-4 (Atlantic Time)</option>
                                    <option value="UTC-5" {{ old('timezone') === 'UTC-5' ? 'selected' : '' }}>UTC-5 (Eastern Time)</option>
                                    <option value="UTC-6" {{ old('timezone') === 'UTC-6' ? 'selected' : '' }}>UTC-6 (Central Time)</option>
                                    <option value="UTC-7" {{ old('timezone') === 'UTC-7' ? 'selected' : '' }}>UTC-7 (Mountain Time)</option>
                                    <option value="UTC-8" {{ old('timezone') === 'UTC-8' ? 'selected' : '' }}>UTC-8 (Pacific Time)</option>
                                    <option value="UTC-9" {{ old('timezone') === 'UTC-9' ? 'selected' : '' }}>UTC-9 (Alaska Time)</option>
                                    <option value="UTC-10" {{ old('timezone') === 'UTC-10' ? 'selected' : '' }}>UTC-10 (Hawaii Time)</option>
                                    <option value="UTC-11" {{ old('timezone') === 'UTC-11' ? 'selected' : '' }}>UTC-11 (Samoa Time)</option>
                                    <option value="UTC-12" {{ old('timezone') === 'UTC-12' ? 'selected' : '' }}>UTC-12 (Baker Island Time)</option>
                                </optgroup>
                            </select>
                            @error('timezone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="col-xl-6">
                            <label for="profile-role" class="form-label">Role :</label>
                            <select class="form-control @error('role') is-invalid @enderror" id="profile-role" name="role" data-trigger required>
                                <option value="">Select Role</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="trainer" {{ old('role') === 'trainer' ? 'selected' : '' }}>Trainer</option>
                                <option value="client" {{ old('role') === 'client' ? 'selected' : '' }}>Client</option>
                            </select>
                            @error('role')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="col-xl-6">
                            <label for="profile-status" class="form-label">Account Status :</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="profile-status" name="status">
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        
                        <div class="col-xl-12">
                            <div class="border-top pt-3 mt-3">
                                <h6 class="fw-semibold mb-3">Password <span class="text-danger">*</span></h6>
                                <div class="row gy-3">
                                    <div class="col-xl-6">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter Password" required>
                                        @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                        <small class="text-muted">Minimum 8 characters required.</small>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" required>
                                        @error('password_confirmation')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                        <small class="text-muted">Re-enter the password to confirm.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>

            {{-- Trainer-specific fields - Only shown when trainer role is selected --}}
            <div class="col-xl-12" id="trainerSection" style="display: none;">
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
                                <input type="text" class="form-control @error('designation') is-invalid @enderror" id="profile-designation" name="designation" value="{{ old('designation') }}" placeholder="Enter Designation">
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
                                    <option value="less_than_1_year" {{ old('experience') === 'less_than_1_year' ? 'selected' : '' }}>Less than 1 year</option>
                                    <option value="1_year" {{ old('experience') === '1_year' ? 'selected' : '' }}>1 year</option>
                                    <option value="2_years" {{ old('experience') === '2_years' ? 'selected' : '' }}>2 years</option>
                                    <option value="3_years" {{ old('experience') === '3_years' ? 'selected' : '' }}>3 years</option>
                                    <option value="4_years" {{ old('experience') === '4_years' ? 'selected' : '' }}>4 years</option>
                                    <option value="5_years" {{ old('experience') === '5_years' ? 'selected' : '' }}>5 years</option>
                                    <option value="6_years" {{ old('experience') === '6_years' ? 'selected' : '' }}>6 years</option>
                                    <option value="7_years" {{ old('experience') === '7_years' ? 'selected' : '' }}>7 years</option>
                                    <option value="8_years" {{ old('experience') === '8_years' ? 'selected' : '' }}>8 years</option>
                                    <option value="9_years" {{ old('experience') === '9_years' ? 'selected' : '' }}>9 years</option>
                                    <option value="10_years" {{ old('experience') === '10_years' ? 'selected' : '' }}>10 years</option>
                                    <option value="more_than_10_years" {{ old('experience') === 'more_than_10_years' ? 'selected' : '' }}>More than 10 years</option>
                                </select>
                                @error('experience')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="col-xl-12">
                                <label for="profile-about" class="form-label">About Me :</label>
                                <textarea class="form-control @error('about') is-invalid @enderror" id="profile-about" name="about" rows="3" placeholder="Tell clients about yourself, your background, and what makes you unique as a trainer...">{{ old('about') }}</textarea>
                                @error('about')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="col-xl-12">
                                <label for="profile-training-philosophy" class="form-label">Training Philosophy :</label>
                                <textarea class="form-control @error('training_philosophy') is-invalid @enderror" id="profile-training-philosophy" name="training_philosophy" rows="3" placeholder="Describe your approach to training, your beliefs about fitness, and how you help clients achieve their goals...">{{ old('training_philosophy') }}</textarea>
                                @error('training_philosophy')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Location Section --}}
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Location Information (Optional)
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row gy-3">
                        <div class="col-xl-6">
                            <label for="location-country" class="form-label">Country</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" id="location-country" name="country" value="{{ old('country') }}" placeholder="Enter country name" maxlength="100">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-xl-6">
                            <label for="location-state" class="form-label">State/Province</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" id="location-state" name="state" value="{{ old('state') }}" placeholder="Enter state or province" maxlength="100">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-xl-6">
                            <label for="location-city" class="form-label">City</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="location-city" name="city" value="{{ old('city') }}" placeholder="Enter city name" maxlength="100">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-xl-6">
                            <label for="location-zipcode" class="form-label">Zip/Postal Code</label>
                            <input type="text" class="form-control @error('zipcode') is-invalid @enderror" id="location-zipcode" name="zipcode" value="{{ old('zipcode') }}" placeholder="Enter zip/postal code" maxlength="20">
                            @error('zipcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-xl-12">
                            <label for="location-address" class="form-label">Street Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="location-address" name="address" rows="2" placeholder="Enter street address" maxlength="255">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-12">
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-wave">Cancel</a>
                <button type="button" id="saveAccountBtn" class="btn btn-primary btn-wave float-end">Create User</button>
            </div>
        </div>
    </form>
</div>
<!--End::row-1 -->

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

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: 'File size must be less than 5MB',
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
                        newImg.alt = 'Profile Image';
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
                        avatarImg.alt = 'Profile Image';
                        avatarContainer.appendChild(avatarImg);
                    }
                }

                // Show clear button
                document.getElementById('clearImageBtn').style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        }
    });

    /**
     * Clear profile image
     */
    function clearProfileImage() {
        const fileInput = document.getElementById('profileImage');
        const avatarContainer = document.getElementById('profileAvatarContainer');
        const avatarImg = document.getElementById('profileAvatarImage');
        
        fileInput.value = '';
        
        // Replace img with default icon
        if (avatarImg && avatarImg.tagName === 'IMG') {
            const defaultSpan = document.createElement('span');
            defaultSpan.className = 'avatar avatar-xxl bg-primary text-white d-flex align-items-center justify-content-center';
            defaultSpan.id = 'profileAvatarImage';
            defaultSpan.style.fontSize = '2rem';
            defaultSpan.innerHTML = '<i class="ri-user-3-line"></i>';
            avatarContainer.replaceChild(defaultSpan, avatarImg);
        }
        
        document.getElementById('clearImageBtn').style.display = 'none';
    }

    /**
     * Handle business logo preview
     */
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
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'File size must be less than 5MB',
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
                    document.getElementById('clearBusinessLogoBtn').style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    /**
     * Clear business logo
     */
    function clearBusinessLogo() {
        const fileInput = document.getElementById('businessLogo');
        const logoPreview = document.getElementById('businessLogoPreview');
        
        fileInput.value = '';
        logoPreview.src = '{{ asset('build/assets/images/logo.png') }}';
        document.getElementById('clearBusinessLogoBtn').style.display = 'none';
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

        // Handle role change to show/hide trainer fields and business logo
        const roleSelect = document.getElementById('profile-role');
        const trainerSection = document.getElementById('trainerSection');
        const businessLogoSection = document.getElementById('businessLogoSection');
        
        if (roleSelect) {
            // Check initial value on page load
            if (roleSelect.value === 'trainer') {
                if (trainerSection) trainerSection.style.display = 'block';
                if (businessLogoSection) businessLogoSection.style.display = 'block';
            }
            
            roleSelect.addEventListener('change', function() {
                if (this.value === 'trainer') {
                    if (trainerSection) trainerSection.style.display = 'block';
                    if (businessLogoSection) businessLogoSection.style.display = 'block';
                } else {
                    if (trainerSection) trainerSection.style.display = 'none';
                    if (businessLogoSection) businessLogoSection.style.display = 'none';
                }
            });
        }

        // Handle Save/Create button
        const saveAccountBtn = document.getElementById('saveAccountBtn');
        if (saveAccountBtn) {
            saveAccountBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = document.getElementById('profileForm');
                
                // Basic validation
                const name = document.getElementById('profile-user-name').value.trim();
                const email = document.getElementById('profile-email').value.trim();
                const password = document.getElementById('password').value;
                const passwordConfirmation = document.getElementById('password_confirmation').value;
                const role = document.getElementById('profile-role').value;
                
                if (!name) {
                    showAlert('error', 'Please enter user name');
                    return;
                }
                
                if (!email) {
                    showAlert('error', 'Please enter email address');
                    return;
                }
                
                if (!role) {
                    showAlert('error', 'Please select a role');
                    return;
                }
                
                if (!password) {
                    showAlert('error', 'Please enter a password');
                    return;
                }
                
                if (password.length < 8) {
                    showAlert('error', 'Password must be at least 8 characters');
                    return;
                }
                
                if (password !== passwordConfirmation) {
                    showAlert('error', 'Password and confirmation do not match');
                    return;
                }
                
                // Submit the form
                if (form) {
                    submitProfileForm(form, saveAccountBtn);
                }
            });
        }
    });

    /**
     * Submit profile form via AJAX
     */
    function submitProfileForm(form, submitBtn) {
        const formData = new FormData(form);
        
        // Add location fields to form data
        const locationFields = ['country', 'state', 'city', 'address', 'zipcode'];
        locationFields.forEach(field => {
            const input = document.getElementById(`location-${field}`);
            if (input && input.value.trim()) {
                formData.append(field, input.value.trim());
            }
        });
        
        const originalText = submitBtn.innerHTML;
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ri-loader-4-line me-1"></i>Creating...';
        
        // Clear previous errors
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        
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
                window.location.href = '{{ route("admin.users.index") }}';
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
                    // If can't parse, assume success and redirect
                    window.location.href = '{{ route("admin.users.index") }}';
                    return { success: true };
                }
            });
        })
        .then(data => {
            if (data && data.success !== false) {
                showAlert('success', data.message || 'User created successfully!');
                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = data.redirect || '{{ route("admin.users.index") }}';
                }, 1500);
            } else {
                showAlert('danger', data.message || 'Failed to create user');
                
                // Display validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        // Handle location fields
                        if (['country', 'state', 'city', 'address', 'zipcode'].includes(field)) {
                            const input = document.getElementById(`location-${field}`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                errorDiv.textContent = data.errors[field][0];
                                input.parentNode.appendChild(errorDiv);
                            }
                        } else {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                errorDiv.textContent = data.errors[field][0];
                                input.parentNode.appendChild(errorDiv);
                            }
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

</script>

@endsection
