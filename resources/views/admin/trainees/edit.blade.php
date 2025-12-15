@extends('layouts.master')

@section('styles')
<!-- Filepond CSS -->
<link rel="stylesheet" href="{{asset('build/assets/libs/filepond/filepond.min.css')}}">
<link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css')}}">
@endsection

@section('scripts')
<!-- Filepond JS -->
<script src="{{asset('build/assets/libs/filepond/filepond.min.js')}}"></script>
<script src="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js')}}"></script>
<script src="{{asset('build/assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js')}}"></script>
<script src="{{asset('build/assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js')}}"></script>
<script src="{{asset('build/assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js')}}"></script>
<script src="{{asset('build/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js')}}"></script>
<script src="{{asset('build/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js')}}"></script>
<script src="{{asset('build/assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js')}}"></script>
<script src="{{asset('build/assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js')}}"></script>
<script src="{{asset('build/assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js')}}"></script>


<script>
$(document).ready(function() {
    // Register FilePond plugins
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginImageExifOrientation,
        FilePondPluginFileValidateSize,
        FilePondPluginFileEncode,
        FilePondPluginImageEdit,
        FilePondPluginFileValidateType,
        FilePondPluginImageCrop,
        FilePondPluginImageResize,
        FilePondPluginImageTransform
    );

    // Initialize FilePond for profile image
    const profileImageInput = document.querySelector('#profile_image');
    if (profileImageInput) {
        const pond = FilePond.create(profileImageInput, {
            acceptedFileTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'],
            maxFileSize: '2MB',
            imagePreviewHeight: 120,
            imageCropAspectRatio: '1:1',
            imageResizeTargetWidth: 200,
            imageResizeTargetHeight: 200,
            stylePanelLayout: 'compact',
            styleLoadIndicatorPosition: 'center bottom',
            styleProgressIndicatorPosition: 'right bottom',
            styleButtonRemoveItemPosition: 'left bottom',
            styleButtonProcessItemPosition: 'right bottom',
            labelIdle: 'Drag & Drop profile image or <span class="filepond--label-action">Browse</span>',
        });
        
        @if($trainee->profile_image)
        // Load existing image
        pond.addFile('{{ asset("storage/" . $trainee->profile_image) }}');
        @endif
    }

    /**
     * Goals Management
     */
    let selectedGoals = [];
    let goalsToRemove = [];
    
    // Handle existing goals selection
    const existingGoalsSelect = document.getElementById('existingGoalsSelect');
    if (existingGoalsSelect) {
        existingGoalsSelect.addEventListener('change', function() {
            const goalName = this.value;
            if (goalName && goalName !== '') {
                if (!selectedGoals.includes(goalName)) {
                    selectedGoals.push(goalName);
                    updateSelectedGoalsDisplay();
                    updateFitnessGoalsInput();
                    // Reset select to first option
                    this.value = '';
                } else {
                    showAlert('warning', 'This goal is already added');
                    this.value = '';
                }
            }
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
    
    // Remove goal from selected list
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
    
    // Remove existing goal
    function removeExistingGoal(goalId, goalName) {
        Swal.fire({
            title: 'Remove Goal?',
            text: `Are you sure you want to remove "${goalName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                if (!goalsToRemove.includes(goalId)) {
                    goalsToRemove.push(goalId);
                }
                document.getElementById('goalsToRemoveInput').value = JSON.stringify(goalsToRemove);
                
                // Hide the goal badge
                event.target.closest('.badge').remove();
                
                showAlert('success', 'Goal will be removed on save');
            }
        });
    }
    
    // Update hidden input with selected goals
    function updateFitnessGoalsInput() {
        const input = document.getElementById('fitnessGoalsInput');
        if (input) {
            input.value = JSON.stringify(selectedGoals);
        }
    }

    // Form validation
    $('#traineeForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Show loading state
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...').prop('disabled', true);
        
        // Submit form via AJAX
        const formData = new FormData(this);
        formData.append('_method', 'PUT');
        
        // Add goals to form data
        if (selectedGoals.length > 0) {
            selectedGoals.forEach(goal => {
                formData.append('fitness_goals[]', goal);
            });
        }
        
        // Add goals to remove
        if (goalsToRemove.length > 0) {
            formData.append('goals_to_remove', JSON.stringify(goalsToRemove));
        }
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showAlert('success', response.message);
                    
                    // Redirect after 2 seconds
                    setTimeout(function() {
                        window.location.href = '{{ route("admin.trainees.index") }}';
                    }, 2000);
                } else {
                    showAlert('error', response.message || 'Failed to update trainee');
                    submitBtn.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                submitBtn.html(originalText).prop('disabled', false);
                
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                    });
                    showAlert('error', 'Please fix the validation errors below.');
                } else {
                    const message = xhr.responseJSON?.message || 'Failed to update trainee';
                    showAlert('error', message);
                }
            }
        });
    });

    // Password confirmation validation
    $('#password_confirmation').on('keyup', function() {
        const password = $('#password').val();
        const confirmation = $(this).val();
        
        if (confirmation && password !== confirmation) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Delete profile image
    $('#deleteImageBtn').on('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure you want to delete the profile image?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.trainees.index") }}/{{ $trainee->id }}/delete-image',
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#currentImage').hide();
                            showAlert('success', response.message);
                        } else {
                            showAlert('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to delete image';
                        showAlert('error', message);
                    }
                });
            }
        });
    });
});

// Show alert function
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#alertContainer').html(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}
</script>
@endsection

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">Edit Trainee</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.trainees.index') }}">Trainees</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="{{ route('admin.trainees.index') }}" class="btn btn-outline-primary btn-wave">
            <i class="ri-arrow-left-line fw-semibold align-middle me-1"></i> Back to List
        </a>
    </div>
</div>
<!-- Page Header Close -->

<!-- Alert Container -->
<div id="alertContainer"></div>

<!-- Main Content -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    Edit Trainee Information
                </div>
            </div>
            <div class="card-body">
                <form id="traineeForm" action="{{ route('admin.trainees.update', $trainee->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Current Profile Image -->
                        @if($trainee->profile_image)
                        <div class="col-xl-12 mb-4" id="currentImage">
                            <div class="text-center">
                                <label class="form-label">Current Profile Image</label>
                                <div class="d-flex justify-content-center align-items-center flex-column">
                                    <img src="{{ asset('storage/' . $trainee->profile_image) }}" alt="Current Profile" class="avatar avatar-xxl avatar-rounded mb-2">
                                    <button type="button" class="btn btn-sm btn-danger btn-wave" id="deleteImageBtn">
                                        <i class="ri-delete-bin-line me-1"></i> Delete Image
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Profile Image Upload -->
                        <div class="col-xl-12 mb-4">
                            <div class="text-center">
                                <label class="form-label">{{ $trainee->profile_image ? 'Update Profile Image' : 'Profile Image' }}</label>
                                <input type="file" id="profile_image" name="profile_image" accept="image/*">
                                <small class="text-muted d-block mt-2">Upload a profile image (JPEG, PNG, JPG, GIF). Max size: 2MB</small>
                            </div>
                        </div>
                        
                        <!-- Basic Information -->
                        <div class="col-xl-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $trainee->name) }}" placeholder="Enter full name" required>
                        </div>
                        
                        <div class="col-xl-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $trainee->email) }}" placeholder="Enter email address" required>
                        </div>
                        
                        <div class="col-xl-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone', $trainee->phone) }}" placeholder="Enter phone number">
                        </div>
                        
                        <!-- Status Information -->
                        <div class="col-xl-6 mb-3">
                            <label class="form-label">Account Status</label>
                            <div class="form-control-plaintext">
                                @if($trainee->email_verified_at)
                                    <span class="badge bg-success-transparent">Active</span>
                                @else
                                    <span class="badge bg-danger-transparent">Inactive</span>
                                @endif
                                <small class="text-muted d-block">Created: {{ $trainee->created_at->format('d-m-Y H:i') }}</small>
                            </div>
                        </div>
                        
                        <!-- Password Fields -->
                        <div class="col-xl-12 mb-3">
                            <div class="alert alert-info">
                                <i class="ri-information-line me-2"></i>
                                <strong>Password Update:</strong> Leave password fields empty if you don't want to change the password.
                            </div>
                        </div>
                        
                        <div class="col-xl-6 mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="ri-eye-line" id="password-icon"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimum 8 characters required</small>
                        </div>
                        
                        <div class="col-xl-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="ri-eye-line" id="password_confirmation-icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Goals Section --}}
                    <div class="row">
                        <div class="col-xl-12 mb-3">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <div class="card-title mb-0">
                                        <h6 class="mb-0">Fitness Goals</h6>
                                    </div>
                                </div>
                                <div class="card-body">
                                    {{-- Current Goals Display --}}
                                    @php
                                        $currentGoals = $trainee->goals;
                                    @endphp
                                    @if($currentGoals && $currentGoals->count() > 0)
                                        <div class="mb-3">
                                            <label class="form-label">Current Goals</label>
                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                @foreach($currentGoals as $goal)
                                                    <span class="badge bg-primary-transparent d-inline-flex align-items-center gap-1">
                                                        {{ $goal->name }}
                                                        <button type="button" class="btn-close btn-close-sm" onclick="removeExistingGoal({{ $goal->id }}, '{{ $goal->name }}')" aria-label="Remove"></button>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- Add New Goal --}}
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
                                    
                                    {{-- Select Existing Goals --}}
                                    <div class="mb-3">
                                        <label class="form-label">Select from Existing Goals</label>
                                        <select class="form-control @error('existing_goals') is-invalid @enderror" id="existingGoalsSelect" data-trigger>
                                            <option value="">Select a goal to add</option>
                                            @php
                                                $availableGoals = \App\Models\Goal::where('status', 1)
                                                    ->where(function($q) use ($trainee) {
                                                        $q->whereHas('user', function($uq) {
                                                            $uq->where('role', 'client');
                                                        })
                                                        ->orWhereNull('user_id');
                                                    })
                                                    ->where('user_id', '!=', $trainee->id)
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
                                        <label class="form-label">Goals to Add</label>
                                        <div id="selectedGoalsList" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                    
                                    {{-- Hidden input to store goals to add --}}
                                    <input type="hidden" name="fitness_goals" id="fitnessGoalsInput" value="">
                                    <input type="hidden" name="goals_to_remove" id="goalsToRemoveInput" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('admin.trainees.index') }}" class="btn btn-light btn-wave">
                                    <i class="ri-close-line fw-semibold align-middle me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-wave" id="submitBtn">
                                    <i class="ri-save-line fw-semibold align-middle me-1"></i> Update Trainee
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'ri-eye-off-line';
    } else {
        field.type = 'password';
        icon.className = 'ri-eye-line';
    }
}
</script>
@endsection