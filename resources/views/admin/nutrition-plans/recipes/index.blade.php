@extends('layouts.master')

@section('styles')
<style>
.recipe-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.recipe-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.recipe-image {
    height: 150px;
    width: 100%;
    object-fit: cover;
    border-radius: 6px;
}
</style>
@endsection

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">Manage Recipes - {{ $plan->plan_name }}</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{route('admin.nutrition-plans.index')}}">Nutrition Plans</a></li>
                    <li class="breadcrumb-item"><a href="{{route('admin.nutrition-plans.show', $plan->id)}}">{{ $plan->plan_name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Recipes</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="{{route('admin.nutrition-plans.recipes.create', $plan->id)}}" class="btn btn-primary btn-wave waves-effect waves-light me-2">
            <i class="ri-add-line me-1"></i> Add New Recipe
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

<!-- Recipes Management -->
<div class="row">
    <div class="col-xl-12">
        <x-tables.card title="Recipes ({{ $plan->recipes->count() }})">
            <x-slot:tools>
                <!-- <div class="d-flex gap-2">
                    <button class="btn btn-light btn-sm" onclick="toggleView()">
                        <i class="ri-layout-grid-line me-1" id="viewToggleIcon"></i> <span id="viewToggleText">List View</span>
                    </button>
                </div> -->
            </x-slot:tools>
            
            <div class="card-body p-0">
                @if($plan->recipes->count() > 0)
                    <x-tables.table 
                        :headers="['Recipe', 'Created', 'Order', 'Actions']"
                        :bordered="true"
                        id="recipesTable"
                    >
                        <tbody>
                            @foreach($plan->recipes as $recipe)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($recipe->image_url)
                                                <img src="{{ $recipe->image_url }}" alt="{{ $recipe->title }}" class="avatar avatar-lg rounded me-2">
                                            @else
                                                <div class="avatar avatar-lg bg-light rounded me-2 d-flex align-items-center justify-content-center">
                                                    <i class="ri-restaurant-line text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $recipe->title }}</h6>
                                                @if($recipe->description)
                                                    <small class="text-muted">{{ Str::limit($recipe->short_description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            <i class="ri-calendar-line me-1"></i> {{ $recipe->formatted_date }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ $recipe->sort_order }}
                                    </td>
                                    <td>
                                        <x-tables.actions 
                                            view="{{ route('admin.nutrition-plans.recipes.show', [$plan->id, $recipe->id]) }}"
                                            edit="{{ route('admin.nutrition-plans.recipes.edit', [$plan->id, $recipe->id]) }}"
                                            delete="deleteRecipe('{{ $recipe->id }}')"
                                        >
                                        </x-tables.actions>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-tables.table>
                @else
                    <div class="text-center py-5">
                        <i class="ri-book-open-line fs-48 text-muted mb-3"></i>
                        <h5 class="text-muted">No recipes added yet</h5>
                        <p class="text-muted">Start building this nutrition plan by adding recipes.</p>
                        <a href="{{ route('admin.nutrition-plans.recipes.create', $plan->id) }}" class="btn btn-primary btn-wave">
                            <i class="ri-add-line me-1"></i> Add First Recipe
                        </a>
                    </div>
                @endif
            </div>
        </x-tables.card>
    </div>
</div>

<!-- Plan Summary Card -->
@if($plan->recipes->count() > 0)
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    Recipe Summary
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h4 class="mb-1 text-primary">{{ $plan->recipes->count() }}</h4>
                            <small class="text-muted">Total Recipes</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h4 class="mb-1 text-success">{{ $plan->recipes->where('image_url', '!=', null)->count() }}</h4>
                            <small class="text-muted">With Images</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h4 class="mb-1 text-info">{{ $plan->recipes->where('description', '!=', null)->count() }}</h4>
                            <small class="text-muted">With Descriptions</small>
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
<script>
let isGridView = true;

$(document).ready(function() {
    // Initialize view
    updateViewDisplay();
});

// Toggle between grid and list view
function toggleView() {
    isGridView = !isGridView;
    updateViewDisplay();
}

function updateViewDisplay() {
    const container = $('#recipesContainer');
    const icon = $('#viewToggleIcon');
    const text = $('#viewToggleText');
    
    if (isGridView) {
        container.removeClass('list-view').addClass('row');
        $('.recipe-item').removeClass('col-12').addClass('col-lg-6 col-xl-4');
        icon.removeClass('ri-layout-grid-line').addClass('ri-list-check');
        text.text('List View');
    } else {
        container.removeClass('row').addClass('list-view');
        $('.recipe-item').removeClass('col-lg-6 col-xl-4').addClass('col-12');
        icon.removeClass('ri-list-check').addClass('ri-layout-grid-line');
        text.text('Grid View');
    }
}

// Delete recipe function
function deleteRecipe(recipeId) {
    Swal.fire({
        title: 'Delete Recipe',
        text: 'Are you sure you want to delete this recipe? This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/nutrition-plans/{{ $plan->id }}/recipes/' + recipeId,
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
                    Swal.fire('Error!', 'Failed to delete recipe', 'error');
                }
            });
        }
    });
}
</script>
@endsection