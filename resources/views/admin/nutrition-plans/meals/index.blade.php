@extends('layouts.master')

@section('styles')
<style>
.meal-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.meal-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.meal-type-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.macro-info {
    font-size: 0.85rem;
    color: #6c757d;
}
</style>
@endsection

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">Manage Meals - {{ $plan->plan_name }}</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{route('admin.nutrition-plans.index')}}">Nutrition Plans</a></li>
                    <li class="breadcrumb-item"><a href="{{route('admin.nutrition-plans.show', $plan->id)}}">{{ $plan->plan_name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Meals</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="{{route('admin.nutrition-plans.meals.create', $plan->id)}}" class="btn btn-primary btn-wave waves-effect waves-light me-2">
            <i class="ri-add-line me-1"></i> Add New Meal
        </a>
        <a href="{{route('admin.nutrition-plans.show', $plan->id)}}" class="btn btn-secondary btn-wave waves-effect waves-light">
            <i class="ri-arrow-left-line me-1"></i> Back to Plan
        </a>
    </div>
</div>
<!-- Page Header Close -->

<!-- Plan Info Card -->
<div class="row mb-4">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1">{{ $plan->plan_name }}</h5>
                        <p class="text-muted mb-0">{{ $plan->description ?: 'No description provided' }}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <span class="badge bg-{{ $plan->status === 'active' ? 'success' : ($plan->status === 'inactive' ? 'danger' : 'warning') }}-transparent">
                                {{ ucfirst($plan->status) }}
                            </span>
                            @if($plan->is_global)
                                <span class="badge bg-info-transparent">Global Plan</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Meals Management -->
<div class="row">
    <div class="col-xl-12">
        <x-tables.card title="Meals ({{ $plan->meals->count() }})">
            <x-slot:tools>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-filter-3-line me-1"></i> Filter by Type
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item filter-meal-type" href="#" data-type="">All Types</a></li>
                        <li><a class="dropdown-item filter-meal-type" href="#" data-type="breakfast">Breakfast</a></li>
                        <li><a class="dropdown-item filter-meal-type" href="#" data-type="lunch">Lunch</a></li>
                        <li><a class="dropdown-item filter-meal-type" href="#" data-type="dinner">Dinner</a></li>
                        <li><a class="dropdown-item filter-meal-type" href="#" data-type="snack">Snack</a></li>
                        <li><a class="dropdown-item filter-meal-type" href="#" data-type="pre_workout">Pre-Workout</a></li>
                        <li><a class="dropdown-item filter-meal-type" href="#" data-type="post_workout">Post-Workout</a></li>
                    </ul>
                </div>
            </x-slot:tools>

            <div class="card-body p-0">
                @if($plan->meals->count() > 0)
                    <x-tables.table 
                        :headers="['Meal', 'Type', 'Calories', 'Macros', 'Prep Time', 'Actions']"
                        :bordered="true"
                        id="mealsTable"
                    >
                        <tbody>
                            @foreach($plan->meals as $meal)
                                <tr class="meal-item" data-meal-type="{{ $meal->meal_type }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($meal->image_url)
                                                <img src="{{ asset('storage/' . $meal->image_url) }}" alt="{{ $meal->title }}" class="avatar avatar-lg rounded me-2">
                                            @else
                                                <div class="avatar avatar-lg bg-light rounded me-2 d-flex align-items-center justify-content-center">
                                                    <i class="ri-restaurant-line text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $meal->title }}</h6>
                                                @if($meal->description)
                                                    <small class="text-muted">{{ Str::limit($meal->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $meal->meal_type === 'breakfast' ? 'warning' : ($meal->meal_type === 'lunch' ? 'success' : ($meal->meal_type === 'dinner' ? 'primary' : 'info')) }}-transparent">
                                            {{ ucfirst(str_replace('_', ' ', $meal->meal_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $meal->calories_per_serving ?? 0 }} Cal</td>
                                    <td>
                                        <small class="d-block text-muted">P: {{ $meal->protein_per_serving ?? 0 }}g</small>
                                        <small class="d-block text-muted">C: {{ $meal->carbs_per_serving ?? 0 }}g</small>
                                        <small class="d-block text-muted">F: {{ $meal->fats_per_serving ?? 0 }}g</small>
                                    </td>
                                    <td>
                                        {{ ($meal->prep_time ?? 0) + ($meal->cook_time ?? 0) }} min
                                        <br>
                                        <small class="text-muted">{{ $meal->servings }} serving{{ $meal->servings > 1 ? 's' : '' }}</small>
                                    </td>
                                    <td>
                                        <x-tables.actions 
                                            view="{{ route('admin.nutrition-plans.meals.show', [$plan->id, $meal->id]) }}"
                                            edit="{{ route('admin.nutrition-plans.meals.edit', [$plan->id, $meal->id]) }}"
                                            delete="deleteMeal('{{ $meal->id }}')"
                                        >
                                        </x-tables.actions>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-tables.table>
                @else
                    <div class="text-center py-5">
                        <i class="ri-restaurant-line fs-48 text-muted mb-3"></i>
                        <h5 class="text-muted">No meals added yet</h5>
                        <p class="text-muted">Start building this nutrition plan by adding meals.</p>
                        <a href="{{ route('admin.nutrition-plans.meals.create', $plan->id) }}" class="btn btn-primary btn-wave">
                            <i class="ri-add-line me-1"></i> Add First Meal
                        </a>
                    </div>
                @endif
            </div>
        </x-tables.card>
    </div>
</div>

<!-- Plan Summary Card -->
@if($plan->meals->count() > 0)
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    Plan Summary
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="mb-1 text-primary">{{ $plan->meals->count() }}</h4>
                            <small class="text-muted">Total Meals</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="mb-1 text-success">{{ number_format($plan->meals->sum('calories_per_serving')) }}</h4>
                            <small class="text-muted">Total Calories</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="mb-1 text-warning">{{ number_format($plan->meals->sum('protein_per_serving')) }}oz</h4>
                            <small class="text-muted">Total Protein</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="mb-1 text-info">{{ number_format(($plan->meals->avg('prep_time') ?? 0) + ($plan->meals->avg('cook_time') ?? 0)) }}</h4>
                            <small class="text-muted">Avg Prep Time (min)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<!-- Sweet Alert -->
<script src="{{asset('build/assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>

<script>
$(document).ready(function() {
    // Filter meals by type
    $('.filter-meal-type').on('click', function(e) {
        e.preventDefault();
        var type = $(this).data('type');
        
        if (type === '') {
            $('.meal-item').show();
        } else {
            $('.meal-item').hide();
            $('.meal-item[data-meal-type="' + type + '"]').show();
        }
        
        // Update active filter
        $('.filter-meal-type').removeClass('active');
        $(this).addClass('active');
    });
});

// Delete meal function
function deleteMeal(mealId) {
    Swal.fire({
        title: 'Delete Meal',
        text: 'Are you sure you want to delete this meal? This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/nutrition-plans/{{ $plan->id }}/meals/' + mealId,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        location.reload();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Failed to delete meal', 'error');
                }
            });
        }
    });
}
</script>
@endsection