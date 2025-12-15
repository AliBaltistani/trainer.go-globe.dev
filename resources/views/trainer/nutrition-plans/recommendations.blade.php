@extends('layouts.master')

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">Nutrition Recommendations - {{ $plan->plan_name }}</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('trainer.dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{route('trainer.nutrition-plans.index')}}">Nutrition Plans</a></li>
                    <li class="breadcrumb-item"><a href="{{route('trainer.nutrition-plans.show', $plan->id)}}">{{ $plan->plan_name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Recommendations</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="{{route('trainer.nutrition-plans.show', $plan->id)}}" class="btn btn-secondary btn-wave waves-effect waves-light">
            <i class="ri-arrow-left-line me-1"></i> Back to Plan
        </a>
    </div>
</div>
<!-- Page Header Close -->

<!-- Success/Error Messages -->
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

<!-- Plan Info -->
<div class="row mb-4">
    <div class="col-xl-12">
        <div class="alert alert-info" role="alert">
            <div class="d-flex align-items-center">
                <i class="ri-information-line me-2 fs-16"></i>
                <div>
                    <strong>Plan:</strong> {{ $plan->plan_name }}
                    @if($plan->client)
                        <span class="ms-2">• <strong>Client:</strong> {{ $plan->client->name }}</span>
                    @endif
                    @if($plan->goal_type)
                        <span class="ms-2">• <strong>Goal:</strong> {{ ucfirst(str_replace('_', ' ', $plan->goal_type)) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recommendations Form -->
    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-target-line me-2"></i> Macronutrient Recommendations
                </div>
            </div>
            <div class="card-body">
                <form id="recommendationsForm" action="{{ route('trainer.nutrition-plans.update-recommendations', $plan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="target_calories" class="form-label">Target Calories (per day) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="target_calories" name="target_calories" 
                                   placeholder="Enter target calories" min="800" max="5000" step="1" 
                                   value="{{ old('target_calories', $plan->recommendations->target_calories ?? '') }}" required>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Recommended daily calorie intake for this client</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="protein" class="form-label">Protein (grams) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="protein" name="protein" 
                                   placeholder="Enter protein target" min="0" max="500" step="0.1" 
                                   value="{{ old('protein', $plan->recommendations->protein ?? '') }}" required>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Daily protein requirement in grams</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="carbs" class="form-label">Carbs (grams) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="carbs" name="carbs" 
                                   placeholder="Enter carbs target" min="0" max="800" step="0.1" 
                                   value="{{ old('carbs', $plan->recommendations->carbs ?? '') }}" required>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Daily carbohydrate requirement in grams</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="fats" class="form-label">Fats (grams) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="fats" name="fats" 
                                   placeholder="Enter fats target" min="0" max="300" step="0.1" 
                                   value="{{ old('fats', $plan->recommendations->fats ?? '') }}" required>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Daily fat requirement in grams</small>
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
                            <i class="ri-save-line me-1"></i> Save Recommendations
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Current Recommendations Display -->
    <div class="col-xl-4">
        @if($plan->recommendations)
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-bookmark-line me-2"></i> Current Recommendations
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3 bg-primary text-white">
                            <h4 class="mb-1">{{ number_format($plan->recommendations->target_calories) }}</h4>
                            <small>Daily Calories</small>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="mb-1 text-success">{{ $plan->recommendations->protein }}g</h6>
                            <small class="text-muted">Protein</small>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="mb-1 text-warning">{{ $plan->recommendations->carbs }}g</h6>
                            <small class="text-muted">Carbs</small>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="mb-1 text-danger">{{ $plan->recommendations->fats }}g</h6>
                            <small class="text-muted">Fats</small>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="mb-1 text-info">{{ number_format(($plan->recommendations->protein * 4) + ($plan->recommendations->carbs * 4) + ($plan->recommendations->fats * 9)) }}</h6>
                            <small class="text-muted">Macro Calories</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <strong>Last Updated:</strong><br>
                    {{ $plan->recommendations->updated_at->format('M d, Y H:i') }}
                </div>
            </div>
        </div>
        @else
        <div class="card custom-card">
            <div class="card-body text-center py-4">
                <i class="ri-target-line fs-48 text-muted mb-3"></i>
                <h5 class="text-muted">No Recommendations Set</h5>
                <p class="text-muted">Set macronutrient targets for your client using the form.</p>
            </div>
        </div>
        @endif
        
        <!-- Help Card -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-question-line me-2"></i> Help
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info" role="alert">
                    <h6 class="alert-heading">Setting Recommendations</h6>
                    <ul class="mb-0 ps-3 small">
                        <li>These recommendations will be visible to your client</li>
                        <li>Use the calculator to generate recommendations based on client data</li>
                        <li>Macro calories should ideally match target calories</li>
                        <li>Clients can set their own targets separately</li>
                    </ul>
                </div>
            </div>
        </div>
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
    $('#recommendationsForm').on('submit', function(e) {
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
                    text: 'Nutrition recommendations updated successfully',
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
                    Swal.fire('Error!', 'Failed to update recommendations', 'error');
                }
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
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

