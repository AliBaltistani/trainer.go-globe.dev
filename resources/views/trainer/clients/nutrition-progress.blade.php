@extends('layouts.master')

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">Nutrition Progress - {{ $client->name }}</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('trainer.dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{route('trainer.clients.index')}}">Clients</a></li>
                    <li class="breadcrumb-item"><a href="{{route('trainer.clients.show', $client->id)}}">{{ $client->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nutrition Progress</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="{{route('trainer.clients.show', $client->id)}}" class="btn btn-secondary btn-wave waves-effect waves-light">
            <i class="ri-arrow-left-line me-1"></i> Back to Client
        </a>
    </div>
</div>
<!-- Page Header Close -->

<!-- Date Range Filter -->
<div class="row mb-4">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-body">
                <form method="GET" action="{{ route('trainer.clients.nutrition-progress', $client->id) }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-wave">
                            <i class="ri-filter-line me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-12 mb-4">
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <x-widgets.stat-card-style1
                    title="Total Entries"
                    value="{{ $stats['total_entries'] }}"
                    icon="ri-file-list-line"
                    color="primary"
                />
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <x-widgets.stat-card-style1
                    title="Avg Daily Calories"
                    value="{{ number_format($stats['avg_daily_calories']) }}"
                    icon="ri-fire-line"
                    color="danger"
                />
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <x-widgets.stat-card-style1
                    title="Avg Daily Protein"
                    value="{{ number_format($stats['avg_daily_protein'], 1) }}g"
                    icon="ri-restaurant-line"
                    color="success"
                />
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <x-widgets.stat-card-style1
                    title="Days Tracked"
                    value="{{ count($dailySummaries) }}"
                    icon="ri-calendar-line"
                    color="info"
                />
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recommendations & Targets Comparison -->
    <div class="col-xl-4">
        @if($trainerRecommendations)
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-target-line me-2"></i> My Recommendations
                </div>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h4 class="text-primary mb-1">{{ number_format($trainerRecommendations->target_calories) }}</h4>
                    <small class="text-muted">Daily Calories</small>
                </div>
                <div class="row text-center">
                    <div class="col-4 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="text-success mb-1">{{ $trainerRecommendations->protein }}g</h6>
                            <small class="text-muted">Protein</small>
                        </div>
                    </div>
                    <div class="col-4 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="text-warning mb-1">{{ $trainerRecommendations->carbs }}g</h6>
                            <small class="text-muted">Carbs</small>
                        </div>
                    </div>
                    <div class="col-4 mb-2">
                        <div class="border rounded p-2">
                            <h6 class="text-danger mb-1">{{ $trainerRecommendations->fats }}g</h6>
                            <small class="text-muted">Fats</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @if($clientTargets)
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-user-line me-2"></i> Client's Targets
                </div>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h4 class="text-info mb-1">{{ number_format($clientTargets->target_calories) }}</h4>
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
            </div>
        </div>
        @endif
    </div>
    
    <!-- Food Diary Entries -->
    <div class="col-xl-8">
        @if(count($dailySummaries) > 0)
            @foreach($dailySummaries as $date => $summary)
            <div class="card custom-card mb-3">
                <div class="card-header">
                    <div class="card-title">
                        {{ $summary['date'] }}
                        <span class="badge bg-primary-transparent ms-2">{{ $summary['entry_count'] }} entries</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Daily Summary -->
                    <div class="row mb-3">
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-2">
                                <h6 class="text-danger mb-1">{{ number_format($summary['total_calories']) }}</h6>
                                <small class="text-muted">Calories</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-2">
                                <h6 class="text-success mb-1">{{ number_format($summary['total_protein'], 1) }}g</h6>
                                <small class="text-muted">Protein</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-2">
                                <h6 class="text-warning mb-1">{{ number_format($summary['total_carbs'], 1) }}g</h6>
                                <small class="text-muted">Carbs</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-2">
                                <h6 class="text-info mb-1">{{ number_format($summary['total_fats'], 1) }}g</h6>
                                <small class="text-muted">Fats</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Entries List -->
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Meal</th>
                                    <th>Calories</th>
                                    <th>Protein</th>
                                    <th>Carbs</th>
                                    <th>Fats</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary['entries'] as $entry)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($entry->logged_at)->format('H:i') }}</td>
                                    <td>{{ $entry->meal_name }}</td>
                                    <td>{{ number_format($entry->calories) }}</td>
                                    <td>{{ number_format($entry->protein, 1) }}g</td>
                                    <td>{{ number_format($entry->carbs, 1) }}g</td>
                                    <td>{{ number_format($entry->fats, 1) }}g</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="ri-file-list-line fs-48 text-muted mb-3"></i>
                    <h5 class="text-muted">No Food Diary Entries</h5>
                    <p class="text-muted">No food diary entries found for the selected date range.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

