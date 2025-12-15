@extends('layouts.master')

@section('styles')

@endsection

@section('content')

<!-- Start::page-header -->
<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-center justify-content-between flex-wrap">
        <h1 class="page-title fw-medium fs-18 mb-0">
            Create New Trainee
        </h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.trainees.index') }}">Trainees</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Trainee</li>
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
    <form method="POST" action="{{ route('admin.trainees.store') }}" enctype="multipart/form-data" id="traineeForm">
        @csrf
        <input type="hidden" name="role" value="client">

        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Account Information
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

                        <div class="col-xl-6">
                            <label for="profile-user-name" class="form-label">Full Name <span class="text-danger">*</span>:</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="profile-user-name" name="name" value="{{ old('name') }}" placeholder="Enter Full Name" required>
                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-email" class="form-label">Email <span class="text-danger">*</span>:</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="profile-email" name="email" value="{{ old('email') }}" placeholder="Enter Email" required>
                            @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-xl-6">
                            <label for="profile-phn-no" class="form-label">Phone No :</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="profile-phn-no" name="phone" value="{{ old('phone') }}" placeholder="Enter Phone Number">
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
        </div>
        
        {{-- Goals Section --}}
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Fitness Goals (Optional)
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Assign Goals to Trainee</label>
                        <p class="text-muted fs-12 mb-3">You can select existing goals or create new ones for this trainee.</p>
                        
                        {{-- Existing Goals Selection --}}
                        <div class="mb-3">
                            <label class="form-label">Select Existing Goals</label>
                            <select class="form-control @error('existing_goals') is-invalid @enderror" id="existingGoalsSelect" data-trigger>
                                <option value="">Select a goal to add</option>
                                @php
                                    $availableGoals = \App\Models\Goal::where('status', 1)
                                        ->where(function($q) {
                                            $q->whereHas('user', function($uq) {
                                                $uq->where('role', 'client');
                                            })
                                            ->orWhereNull('user_id');
                                        })
                                        ->orderBy('name')
                                        ->get();
                                @endphp
                                @if($availableGoals->count() > 0)
                                    @foreach($availableGoals as $goal)
                                        <option value="{{ $goal->name }}" data-goal-id="{{ $goal->id }}">{{ $goal->name }}</option>
                                    @endforeach
                                @else
                                    <option disabled>No existing goals available</option>
                                @endif
                            </select>
                            <small class="text-muted d-block mt-1">Select a goal and it will be added to the list below</small>
                            @error('existing_goals')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Selected Goals Display --}}
                        <div id="selectedGoalsContainer" class="mb-3" style="display: none;">
                            <label class="form-label">Selected Goals</label>
                            <div id="selectedGoalsList" class="d-flex flex-wrap gap-2"></div>
                        </div>
                        
                        {{-- Add New Goal Input --}}
                        <div class="mb-3">
                            <label class="form-label">Add New Goal</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="newGoalInput" placeholder="Enter goal name (e.g., Lose 10 pounds, Build muscle)">
                                <button type="button" class="btn btn-primary" id="addGoalBtn">
                                    <i class="ri-add-line me-1"></i>Add Goal
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">Enter a goal name and click Add to include it</small>
                        </div>
                        
                        {{-- Hidden input to store selected goals --}}
                        <input type="hidden" name="fitness_goals" id="fitnessGoalsInput" value="">
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
                <a href="{{ route('admin.trainees.index') }}" class="btn btn-light btn-wave">Cancel</a>
                <button type="button" id="saveTraineeBtn" class="btn btn-primary btn-wave float-end">Create Trainee</button>
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
                    if (avatarImg && avatarImg.tagName === 'SPAN') {
                        const newImg = document.createElement('img');
                        newImg.id = 'profileAvatarImage';
                        newImg.src = e.target.result;
                        newImg.alt = 'Profile Image';
                        avatarContainer.replaceChild(newImg, avatarImg);
                        avatarImg = newImg;
                    } else if (avatarImg && avatarImg.tagName === 'IMG') {
                        avatarImg.src = e.target.result;
                    }
                }

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
     * Goals Management
     */
    let selectedGoals = [];
    
    // Handle existing goals selection
    const existingGoalsSelect = document.getElementById('existingGoalsSelect');
    if (existingGoalsSelect) {
        existingGoalsSelect.addEventListener('change', function() {
            const selectedOptions = Array.from(this.selectedOptions);
            selectedOptions.forEach(option => {
                const goalName = option.value;
                if (!selectedGoals.includes(goalName)) {
                    selectedGoals.push(goalName);
                }
            });
            updateSelectedGoalsDisplay();
            updateFitnessGoalsInput();
        });
    }
    
    // Handle add new goal button
    const addGoalBtn = document.getElementById('addGoalBtn');
    const newGoalInput = document.getElementById('newGoalInput');
    if (addGoalBtn && newGoalInput) {
        addGoalBtn.addEventListener('click', function() {
            const goalName = newGoalInput.value.trim();
            if (goalName) {
                if (!selectedGoals.includes(goalName)) {
                    selectedGoals.push(goalName);
                    updateSelectedGoalsDisplay();
                    updateFitnessGoalsInput();
                    newGoalInput.value = '';
                } else {
                    showAlert('warning', 'This goal is already added');
                }
            }
        });
        
        // Allow Enter key to add goal
        newGoalInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addGoalBtn.click();
            }
        });
    }
    
    // Update selected goals display
    function updateSelectedGoalsDisplay() {
        const container = document.getElementById('selectedGoalsContainer');
        const list = document.getElementById('selectedGoalsList');
        
        if (selectedGoals.length > 0) {
            container.style.display = 'block';
            list.innerHTML = selectedGoals.map((goal, index) => `
                <span class="badge bg-primary-transparent d-inline-flex align-items-center gap-1">
                    ${goal}
                    <button type="button" class="btn-close btn-close-sm" onclick="removeGoal(${index})" aria-label="Remove"></button>
                </span>
            `).join('');
        } else {
            container.style.display = 'none';
        }
    }
    
    // Remove goal
    function removeGoal(index) {
        selectedGoals.splice(index, 1);
        updateSelectedGoalsDisplay();
        updateFitnessGoalsInput();
        
        // Also deselect from dropdown
        if (existingGoalsSelect) {
            const options = Array.from(existingGoalsSelect.options);
            options.forEach(option => {
                if (option.selected) {
                    option.selected = false;
                }
            });
        }
    }
    
    // Update hidden input with selected goals
    function updateFitnessGoalsInput() {
        const input = document.getElementById('fitnessGoalsInput');
        if (input) {
            input.value = JSON.stringify(selectedGoals);
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

        // Handle Create Trainee button
        const saveTraineeBtn = document.getElementById('saveTraineeBtn');
        if (saveTraineeBtn) {
            saveTraineeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = document.getElementById('traineeForm');
                
                // Basic validation
                const name = document.getElementById('profile-user-name').value.trim();
                const email = document.getElementById('profile-email').value.trim();
                const password = document.getElementById('password').value;
                const passwordConfirmation = document.getElementById('password_confirmation').value;
                
                if (!name) {
                    showAlert('error', 'Please enter trainee name');
                    return;
                }
                
                if (!email) {
                    showAlert('error', 'Please enter email address');
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
                    submitTraineeForm(form, saveTraineeBtn);
                }
            });
        }
    });

    /**
     * Submit trainee form via AJAX
     */
    function submitTraineeForm(form, submitBtn) {
        const formData = new FormData(form);
        
        // Add goals to form data
        if (selectedGoals.length > 0) {
            selectedGoals.forEach(goal => {
                formData.append('fitness_goals[]', goal);
            });
        }
        
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
            if (response.redirected) {
                window.location.href = response.url;
                return { success: true };
            }
            
            const contentType = response.headers.get('content-type') || '';
            
            if (contentType.includes('text/html')) {
                window.location.href = '{{ route("admin.trainees.index") }}';
                return { success: true };
            }
            
            if (contentType.includes('application/json')) {
                return response.json();
            }
            
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    window.location.href = '{{ route("admin.trainees.index") }}';
                    return { success: true };
                }
            });
        })
        .then(data => {
            if (data && data.success !== false) {
                showAlert('success', data.message || 'Trainee created successfully!');
                setTimeout(() => {
                    window.location.href = data.redirect || '{{ route("admin.trainees.index") }}';
                }, 1500);
            } else {
                showAlert('danger', data.message || 'Failed to create trainee');
                
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
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
