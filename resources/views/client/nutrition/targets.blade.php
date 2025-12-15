@extends('layouts.master')

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">My Nutrition Targets</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('client.dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nutrition Targets</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="{{route('client.nutrition.plans.index')}}" class="btn btn-secondary btn-wave waves-effect waves-light">
            <i class="ri-arrow-left-line me-1"></i> Back to Plans
        </a>
    </div>
</div>
<!-- Page Header Close -->

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="ri-check-line me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <!-- Targets Form -->
    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-target-line me-2"></i> Set Your Nutrition Targets
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4" role="alert">
                    <i class="ri-information-line me-2"></i>
                    <strong>Note:</strong> These are your personal targets. They are separate from your trainer's recommendations and can be adjusted based on your preferences.
                </div>
                
                <form id="targetsForm" action="{{ route('client.nutrition.targets.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="target_calories" class="form-label">Target Calories (per day) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="target_calories" name="target_calories" 
                                   placeholder="Enter target calories" min="500" max="10000" step="1" 
                                   value="{{ old('target_calories', $clientTargets->target_calories ?? '') }}" required>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Your daily calorie goal</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="protein" class="form-label">Protein (grams) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="protein" name="protein" 
                                   placeholder="Enter protein target" min="0" max="1000" step="0.1" 
                                   value="{{ old('protein', $clientTargets->protein ?? '') }}" required>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Daily protein requirement</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="carbs" class="form-label">Carbs (grams) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="carbs" name="carbs" 
                                   placeholder="Enter carbs target" min="0" max="1000" step="0.1" 
                                   value="{{ old('carbs', $clientTargets->carbs ?? '') }}" required>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Daily carbohydrate requirement</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="fats" class="form-label">Fats (grams) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="fats" name="fats" 
                                   placeholder="Enter fats target" min="0" max="1000" step="0.1" 
                                   value="{{ old('fats', $clientTargets->fats ?? '') }}" required>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Daily fat requirement</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Macro Calories</label>
                            <div class="form-control bg-light" id="macroCalories" readonly>
                                <span id="calculatedCalories">0</span> calories
                            </div>
                            <small class="text-muted">Calculated: (Protein × 4) + (Carbs × 4) + (Fats × 9)</small>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-wave">
                            <i class="ri-save-line me-1"></i> Save Targets
                        </button>
                        @if($clientTargets)
                            <button type="button" class="btn btn-danger btn-wave" id="deleteTargetsBtn">
                                <i class="ri-delete-bin-line me-1"></i> Delete Targets
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Trainer Recommendations Sidebar -->
    <div class="col-xl-4">
        @if($plan && $plan->recommendations)
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-user-star-line me-2"></i> Trainer Recommendations
                </div>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h4 class="text-primary mb-1">{{ number_format($plan->recommendations->target_calories) }}</h4>
                    <small class="text-muted">Daily Calories</small>
                </div>
                <div class="row text-center">
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="text-success mb-1">{{ $plan->recommendations->protein }}g</h6>
                            <small class="text-muted">Protein</small>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="text-warning mb-1">{{ $plan->recommendations->carbs }}g</h6>
                            <small class="text-muted">Carbs</small>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="text-danger mb-1">{{ $plan->recommendations->fats }}g</h6>
                            <small class="text-muted">Fats</small>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="text-info mb-1">{{ $plan->trainer->name }}</h6>
                            <small class="text-muted">Trainer</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="alert alert-info mb-0">
                    <small><i class="ri-information-line me-1"></i> These are recommendations from your trainer. You can use them as a reference when setting your own targets.</small>
                </div>
            </div>
        </div>
        @else
        <div class="card custom-card">
            <div class="card-body text-center py-4">
                <i class="ri-user-star-line fs-48 text-muted mb-3"></i>
                <h5 class="text-muted">No Active Plan</h5>
                <p class="text-muted">You don't have an active nutrition plan with trainer recommendations yet.</p>
            </div>
        </div>
        @endif
        
        @if($clientTargets)
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Current Targets</div>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h4 class="text-primary mb-1">{{ number_format($clientTargets->target_calories) }}</h4>
                    <small class="text-muted">Daily Calories</small>
                </div>
                <div class="row text-center">
                    <div class="col-4 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="text-success mb-1">{{ $clientTargets->protein }}g</h6>
                            <small class="text-muted">Protein</small>
                        </div>
                    </div>
                    <div class="col-4 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="text-warning mb-1">{{ $clientTargets->carbs }}g</h6>
                            <small class="text-muted">Carbs</small>
                        </div>
                    </div>
                    <div class="col-4 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="text-danger mb-1">{{ $clientTargets->fats }}g</h6>
                            <small class="text-muted">Fats</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <strong>Last Updated:</strong><br>
                    {{ $clientTargets->updated_at->format('M d, Y H:i') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="{{asset('build/assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>

<script>
$(document).ready(function() {
    // Calculate macro calories on input change
    function calculateMacroCalories() {
        const protein = parseFloat($('#protein').val()) || 0;
        const carbs = parseFloat($('#carbs').val()) || 0;
        const fats = parseFloat($('#fats').val()) || 0;
        
        const macroCalories = (protein * 4) + (carbs * 4) + (fats * 9);
        $('#calculatedCalories').text(Math.round(macroCalories));
        
        // Highlight if mismatch with target calories
        const targetCalories = parseFloat($('#target_calories').val()) || 0;
        if (targetCalories > 0 && Math.abs(macroCalories - targetCalories) > 50) {
            $('#macroCalories').removeClass('bg-light').addClass('bg-warning text-dark');
        } else {
            $('#macroCalories').removeClass('bg-warning text-dark').addClass('bg-light');
        }
    }
    
    $('#protein, #carbs, #fats, #target_calories').on('input', calculateMacroCalories);
    calculateMacroCalories();
    
    // Form submission
    $('#targetsForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="ri-loader-2-line spin me-1"></i> Saving...').prop('disabled', true);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Nutrition targets updated successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(key) {
                        const field = $('#' + key);
                        field.addClass('is-invalid');
                        field.siblings('.invalid-feedback').text(errors[key][0]);
                    });
                    
                    Swal.fire('Validation Error', 'Please check the form for errors', 'error');
                } else {
                    Swal.fire('Error!', 'Failed to update targets', 'error');
                }
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Delete targets
    $('#deleteTargetsBtn').on('click', function() {
        Swal.fire({
            title: 'Delete Targets?',
            text: "Are you sure you want to delete your nutrition targets?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("client.nutrition.targets.delete") }}',
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', 'Your nutrition targets have been deleted.', 'success')
                            .then(() => {
                                location.reload();
                            });
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Failed to delete targets', 'error');
                    }
                });
            }
        });
    });
});

// Spin animation
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);
</script>
@endsection

