@extends('layouts.master')

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">{{ $plan->plan_name }}</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('client.dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{route('client.nutrition.plans.index')}}">Nutrition Plans</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $plan->plan_name }}</li>
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

<div class="row">
    <div class="col-xl-8">
        <!-- Plan Details -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Plan Details</div>
            </div>
            <div class="card-body">
                @if($plan->description)
                    <p class="mb-3">{{ $plan->description }}</p>
                @endif
                
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>Trainer:</strong> {{ $plan->trainer ? $plan->trainer->name : 'N/A' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Goal Type:</strong> {{ $plan->goal_type ? ucfirst(str_replace('_', ' ', $plan->goal_type)) : 'N/A' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Duration:</strong> {{ $plan->duration_text }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Status:</strong> <span class="badge bg-success-transparent">{{ ucfirst($plan->status) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Trainer Recommendations -->
        @if($plan->recommendations)
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-target-line me-2"></i> Trainer Recommendations
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-12">
                        <div class="border rounded p-3 bg-primary text-white mb-3">
                            <h3 class="mb-1">{{ number_format($plan->recommendations->target_calories) }}</h3>
                            <small>Daily Target Calories</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <h5 class="text-success mb-1">{{ $plan->recommendations->protein }}g</h5>
                            <small class="text-muted">Protein</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <h5 class="text-warning mb-1">{{ $plan->recommendations->carbs }}g</h5>
                            <small class="text-muted">Carbs</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <h5 class="text-danger mb-1">{{ $plan->recommendations->fats }}g</h5>
                            <small class="text-muted">Fats</small>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mb-0">
                    <small><i class="ri-information-line me-1"></i> These are recommendations from your trainer. You can set your own targets separately.</small>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Meals Section -->
        @if($plan->meals->count() > 0)
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Meals ({{ $plan->meals->count() }})</div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($plan->meals as $meal)
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ $meal->title }}</h6>
                                    <span class="badge bg-secondary-transparent">{{ ucfirst(str_replace('_', ' ', $meal->meal_type)) }}</span>
                                </div>
                                @if($meal->description)
                                    <p class="text-muted small mb-2">{{ Str::limit($meal->description, 80) }}</p>
                                @endif
                                <div class="small text-muted">
                                    <i class="ri-fire-line me-1"></i> {{ $meal->calories_per_serving }} cal
                                    @if($meal->prep_time || $meal->cook_time)
                                        <span class="ms-2"><i class="ri-time-line me-1"></i> {{ ($meal->prep_time ?? 0) + ($meal->cook_time ?? 0) }} min</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Sidebar -->
    <div class="col-xl-4">
        <!-- My Targets -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">My Targets</div>
            </div>
            <div class="card-body">
                @if($clientTargets)
                    <div class="text-center mb-3">
                        <h4 class="text-primary mb-1">{{ number_format($clientTargets->target_calories) }}</h4>
                        <small class="text-muted">Daily Calories</small>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border rounded p-2 mb-2">
                                <h6 class="text-success mb-1">{{ $clientTargets->protein }}g</h6>
                                <small class="text-muted">Protein</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 mb-2">
                                <h6 class="text-warning mb-1">{{ $clientTargets->carbs }}g</h6>
                                <small class="text-muted">Carbs</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 mb-2">
                                <h6 class="text-danger mb-1">{{ $clientTargets->fats }}g</h6>
                                <small class="text-muted">Fats</small>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid mt-3">
                        <a href="{{route('client.nutrition.targets')}}" class="btn btn-primary btn-sm btn-wave">
                            <i class="ri-edit-line me-1"></i> Update Targets
                        </a>
                    </div>
                @else
                    <div class="text-center py-3">
                        <p class="text-muted mb-3">You haven't set your own targets yet.</p>
                        <a href="{{route('client.nutrition.targets')}}" class="btn btn-primary btn-sm btn-wave">
                            <i class="ri-target-line me-1"></i> Set Targets
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Plan Stats -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Plan Statistics</div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <h5 class="mb-1">{{ $stats['total_meals'] }}</h5>
                            <small class="text-muted">Meals</small>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <h5 class="mb-1">{{ number_format($stats['total_calories']) }}</h5>
                            <small class="text-muted">Total Calories</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

