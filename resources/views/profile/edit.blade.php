@extends('layouts.master')

@section('styles')
<style>
    .avatar-initial {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        font-size: 2.5rem;
        font-weight: 600;
        color: #fff;
        text-transform: uppercase;
    }
    
    .bg-primary-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
</style>
@endsection

@section('content')

<!-- Start::page-header -->
<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-center justify-content-between flex-wrap">
        <h1 class="page-title fw-medium fs-18 mb-0">Profile Settings</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile Settings</li>
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
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    Account
                </div>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profileForm">
                    @csrf
                    <div class="row gy-3">
                        <div class="col-xl-6">
                            <div class="d-flex align-items-start flex-wrap gap-3">
                                <div>
                                    <span class="avatar avatar-xxl" id="profileAvatarContainer">
                                        @if($user->profile_image)
                                            <img id="profileAvatarImage" src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}">
                                        @else
                                            <span id="profileAvatarPlaceholder" class="avatar-initial rounded-circle bg-primary-gradient">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
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
                        
                            <div class="col-xl-6">
                                <div class="d-flex align-items-start flex-wrap gap-3">
                                    <div>
                                        <span class="avatar avatar-xl">
                                            @if($user->business_logo)
                                                <img id="businessLogoPreview" src="{{ asset('storage/' . $user->business_logo) }}" alt="">
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
                            <select class="form-select @error('timezone') is-invalid @enderror" id="profile-timezone" name="timezone">
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
                            <div class="form-text">
                                <small class="text-muted">Your timezone is used for scheduling and notifications</small>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-role" class="form-label">Role :</label>
                            @if(Auth::user()->role === 'admin')
                                <select class="form-select @error('role') is-invalid @enderror" id="profile-role" name="role">
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="trainer" {{ $user->role === 'trainer' ? 'selected' : '' }}>Trainer</option>
                                    <option value="client" {{ $user->role === 'client' ? 'selected' : '' }}>Client</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="form-text">
                                    <small class="text-muted">As an admin, you can change user roles</small>
                                </div>
                            @else
                                <input type="text" class="form-control" id="profile-role" value="{{ ucfirst($user->role) }}" readonly>
                                <div class="form-text">
                                    <small class="text-muted">Contact administrator to change your role</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-created" class="form-label">Member Since :</label>
                            <input type="text" class="form-control" id="profile-created" value="{{ \Carbon\Carbon::parse($user->created_at)->format('F d, Y') }}" readonly>
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-verified" class="form-label">Email Status :</label>
                            <input type="text" class="form-control" id="profile-verified" value="{{ $user->email_verified_at ? 'Verified' : 'Not Verified' }}" readonly>
                        </div>
                        
                        {{-- Trainer-specific fields - Only show for trainers --}}
                        @if($user->role === 'trainer')
                            <div class="col-xl-12">
                                <hr class="my-4">
                                <h6 class="fw-semibold mb-3 text-primary">
                                    <i class="ri-user-star-line me-2"></i>Trainer Profile Information
                                </h6>
                            </div>


                            <div class="col-xl-6">
                                <label for="profile-designation" class="form-label">Designation :</label>
                                <input type="text" class="form-control @error('designation') is-invalid @enderror" id="profile-designation" name="designation" value="{{ old('designation', $user->designation) }}" placeholder="e.g., Senior Fitness Trainer">
                                @error('designation')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="form-text">
                                    <small class="text-muted">Your professional title or designation</small>
                                </div>
                            </div>
                            
                            <div class="col-xl-6">
                                <label for="profile-experience" class="form-label">Experience :</label>
                                <select class="form-select @error('experience') is-invalid @enderror" id="profile-experience" name="experience">
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
                                <textarea class="form-control @error('about') is-invalid @enderror" id="profile-about" name="about" rows="4" placeholder="Tell clients about yourself, your background, and what makes you unique as a trainer...">{{ old('about', $user->about) }}</textarea>
                                @error('about')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="form-text">
                                    <small class="text-muted">Share your background, specializations, and what makes you unique (max 1000 characters)</small>
                                </div>
                            </div>
                            
                            <div class="col-xl-12">
                                <label for="profile-training-philosophy" class="form-label">Training Philosophy :</label>
                                <textarea class="form-control @error('training_philosophy') is-invalid @enderror" id="profile-training-philosophy" name="training_philosophy" rows="4" placeholder="Describe your approach to training, your beliefs about fitness, and how you help clients achieve their goals...">{{ old('training_philosophy', $user->training_philosophy) }}</textarea>
                                @error('training_philosophy')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="form-text">
                                    <small class="text-muted">Explain your training approach and philosophy (max 1000 characters)</small>
                                </div>
                            </div>
                            
                            <div class="col-xl-12">
                                <label for="profile-specializations" class="form-label">Specializations :</label>
                                <select class="form-select @error('specializations') is-invalid @enderror" id="profile-specializations" name="specializations[]" >
                                    @php
                                        $specializations = \App\Models\Specialization::where('status', 1)->orderBy('name')->get();
                                        $userSpecializations = $user->specializations->pluck('id')->toArray();
                                    @endphp
                                    @foreach($specializations as $specialization)
                                        <option value="{{ $specialization->id }}" 
                                            {{ in_array($specialization->id, old('specializations', $userSpecializations)) ? 'selected' : '' }}>
                                            {{ $specialization->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('specializations')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="form-text">
                                    <small class="text-muted">Select your areas of expertise and specialization (hold Ctrl/Cmd to select multiple)</small>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('profile.index') }}" class="btn btn-light ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
            alert('Please select a valid image file (JPEG, PNG, JPG, or GIF)');
            this.value = '';
            return;
        }
        
        // Validate file size (2MB = 2048KB)
        if (file.size > 2048 * 1024) {
            alert('File size must be less than 2MB');
            this.value = '';
            return;
        }
        
        // Preview the selected image
        const reader = new FileReader();
        reader.onload = function(e) {
            const avatarContainer = document.getElementById('profileAvatarContainer');
            if (avatarContainer) {
                // Check if there's already an img element
                let avatarImg = document.getElementById('profileAvatarImage');
                
                if (avatarImg) {
                    // Update existing image
                    avatarImg.src = e.target.result;
                } else {
                    // Remove placeholder and create new image element
                    const placeholder = document.getElementById('profileAvatarPlaceholder');
                    if (placeholder) {
                        placeholder.remove();
                    }
                    
                    // Create and append new image
                    avatarImg = document.createElement('img');
                    avatarImg.id = 'profileAvatarImage';
                    avatarImg.src = e.target.result;
                    avatarImg.alt = 'Profile Image';
                    avatarContainer.appendChild(avatarImg);
                }
                
                // Show delete button if it doesn't exist
                const deleteBtn = document.getElementById('deleteImageBtn');
                if (!deleteBtn) {
                    const btnList = document.querySelector('.btn-list');
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
                alert('Please select a valid image file (JPEG, PNG, JPG, GIF, or WEBP)');
                this.value = '';
                return;
            }
            if (file.size > 2048 * 1024) {
                alert('File size must be less than 2MB');
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
    if (confirm('Are you sure you want to delete your business logo?')) {
        const deleteForm = document.getElementById('deleteBusinessLogoForm');
        if (deleteForm) {
            deleteForm.submit();
        }
    }
}
/**
 * Delete profile image function
 */
function deleteProfileImage() {
    if (confirm('Are you sure you want to delete your profile image?')) {
        const deleteForm = document.getElementById('deleteImageForm');
        if (deleteForm) {
            deleteForm.submit();
        }
    }
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
});

/**
 * Form validation before submission
 */
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const name = document.getElementById('profile-user-name').value.trim();
    const email = document.getElementById('profile-email').value.trim();
    const phone = document.getElementById('profile-phn-no').value.trim();
    
    if (!name || !email || !phone) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return false;
    }
    
    return true;
});
</script>
@endsection