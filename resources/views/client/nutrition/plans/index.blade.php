@extends('layouts.master')

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">My Nutrition Plans</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('client.dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nutrition Plans</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="{{route('client.nutrition.targets')}}" class="btn btn-primary btn-wave waves-effect waves-light me-2">
            <i class="ri-target-line me-1"></i> My Targets
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
    @if($plans->count() > 0)
        @foreach($plans as $plan)
            <div class="col-xl-6 col-lg-6 col-md-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">{{ $plan->plan_name }}</h5>
                                @if($plan->trainer)
                                    <p class="text-muted small mb-0">
                                        <i class="ri-user-line me-1"></i> Trainer: {{ $plan->trainer->name }}
                                    </p>
                                @endif
                            </div>
                            <span class="badge bg-success-transparent">{{ ucfirst($plan->status) }}</span>
                        </div>
                        
                        @if($plan->description)
                            <p class="text-muted mb-3">{{ Str::limit($plan->description, 150) }}</p>
                        @endif
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="ri-restaurant-line text-primary me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Meals</small>
                                        <strong>{{ $plan->meals->count() }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="ri-calendar-line text-info me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Duration</small>
                                        <strong>{{ $plan->duration_text }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($plan->recommendations)
                            <div class="alert alert-info mb-3" role="alert">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Trainer Recommendations:</strong>
                                        <div class="small mt-1">
                                            {{ number_format($plan->recommendations->target_calories) }} cal • 
                                            {{ $plan->recommendations->protein }}g P • 
                                            {{ $plan->recommendations->carbs }}g C • 
                                            {{ $plan->recommendations->fats }}g F
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="d-grid">
                            <a href="{{ route('client.nutrition.plans.show', $plan->id) }}" class="btn btn-primary btn-wave">
                                <i class="ri-eye-line me-1"></i> View Plan Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="ri-restaurant-line fs-48 text-muted mb-3"></i>
                    <h5 class="text-muted">No Nutrition Plans Assigned</h5>
                    <p class="text-muted">Your trainer hasn't assigned any nutrition plans yet.</p>
                    <a href="{{route('client.dashboard')}}" class="btn btn-primary btn-wave">
                        <i class="ri-arrow-left-line me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

