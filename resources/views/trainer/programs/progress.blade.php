@extends('layouts.master')

@section('title', 'Program Progress - ' . $program->name)

@section('content')
<div class="container-fluid">
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h4 class="mb-0">Program Progress: {{ $program->name }}</h4>
            <p class="mb-0 text-muted">Client: {{ $client->name }}</p>
        </div>
        <div class="main-dashboard-header-right">
            <a href="{{ route('trainer.clients.show', $client->id) }}" class="btn btn-light btn-sm">
                <i class="ri-arrow-left-line me-1"></i> Back to Client
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted">Total Workouts</p>
                            <h4 class="mb-0 fw-semibold">{{ $totalWorkouts }}</h4>
                        </div>
                        <div class="avatar avatar-md bg-primary-transparent text-primary">
                            <i class="ri-calendar-check-line fs-20"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted">Exercises Done</p>
                            <h4 class="mb-0 fw-semibold">{{ $totalExercisesCompleted }}</h4>
                        </div>
                        <div class="avatar avatar-md bg-success-transparent text-success">
                            <i class="ri-checkbox-circle-line fs-20"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted">Total Volume</p>
                            <h4 class="mb-0 fw-semibold">{{ number_format($totalVolume) }} lbs</h4>
                        </div>
                        <div class="avatar avatar-md bg-warning-transparent text-warning">
                            <i class="ri-weight-line fs-20"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted">Completion</p>
                            <h4 class="mb-0 fw-semibold">{{ $completionPercentage }}%</h4>
                        </div>
                        <div class="avatar avatar-md bg-info-transparent text-info">
                            <i class="ri-pie-chart-line fs-20"></i>
                        </div>
                    </div>
                    <div class="progress progress-xs mt-2">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $completionPercentage }}%" aria-valuenow="{{ $completionPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Row -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Volume History</div>
                </div>
                <div class="card-body">
                    <div id="volumeChart"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Row -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Program Schedule</div>
                </div>
                <div class="card-body">
                    @foreach($program->weeks->sortBy('week_number') as $week)
                        <div class="mb-4">
                            <h5 class="fw-bold text-primary mb-3">Week {{ $week->week_number }} @if($week->title)- {{ $week->title }}@endif</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Day</th>
                                            <th>Workout</th>
                                            <th>Exercises</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($week->days->sortBy('day_number') as $day)
                                            @php
                                                $isCompleted = false;
                                                $completedDate = null;
                                                foreach($day->circuits as $circuit) {
                                                    foreach($circuit->programExercises as $exercise) {
                                                        if($exercise->clientProgress->isNotEmpty()) {
                                                            $isCompleted = true;
                                                            $completedDate = $exercise->clientProgress->first()->completed_at;
                                                            break 2;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <tr>
                                                <td class="align-middle">Day {{ $day->day_number }}</td>
                                                <td class="align-middle">
                                                    <div class="fw-semibold">{{ $day->title }}</div>
                                                    @if($day->description)
                                                        <small class="text-muted text-wrap d-block" style="max-width: 300px;">{{ Str::limit($day->description, 100) }}</small>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    {{ $day->circuits->sum(function($c) { return $c->programExercises->count(); }) }} Exercises
                                                </td>
                                                <td class="align-middle">
                                                    @if($isCompleted)
                                                        <span class="badge bg-success">Completed</span>
                                                        @if($completedDate)
                                                            <small class="d-block text-muted mt-1">{{ $completedDate->format('M d, Y') }}</small>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-warning-transparent">Pending</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    @if(!$isCompleted)
                                                        <form action="{{ route('trainer.programs.mark-day-complete', ['program' => $program->id, 'day' => $day->id]) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary-light" onclick="return confirm('Are you sure you want to mark this day as complete? This will log all exercises with target reps/weight.')">
                                                                <i class="ri-check-double-line me-1"></i> Mark Complete
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button class="btn btn-sm btn-light" disabled>
                                                            <i class="ri-checkbox-circle-line me-1"></i> Done
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Row -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Detailed History
                    </div>
                </div>
                <div class="card-body">
                    @forelse($progress as $date => $exercises)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="ri-calendar-check-line me-2 text-primary"></i>
                                    {{ \Carbon\Carbon::parse($date)->format('l, M d, Y') }}
                                </h6>
                                <span class="badge bg-light text-dark">{{ $exercises->count() }} Exercises</span>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-sm text-nowrap table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Exercise</th>
                                            <th>Workout / Day</th>
                                            <th>Logged</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($exercises as $log)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $log->programExercise->name }}</div>
                                                    <small class="text-muted">Set {{ $log->set_number }}</small>
                                                </td>
                                                <td>
                                                    {{ $log->programExercise->circuit->day->title }}
                                                    <small class="text-muted d-block">Week {{ $log->programExercise->circuit->day->week->week_number }}</small>
                                                </td>
                                                <td>
                                                    @if($log->logged_reps)
                                                        <span class="me-2">{{ $log->logged_reps }} Reps</span>
                                                    @endif
                                                    @if($log->logged_weight)
                                                        <span>{{ \App\Support\UnitConverter::kgToLbs($log->logged_weight) }} lbs</span>
                                                    @endif
                                                </td>
                                                <td>{{ $log->notes ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-5">
                            <i class="ri-file-list-3-line fs-1 text-muted d-block mb-3"></i>
                            <h5 class="text-muted">No workouts completed yet</h5>
                            <p class="text-muted mb-0">The client hasn't logged any progress for this program.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var chartData = @json($chartData);
        
        if (chartData.length > 0) {
            var options = {
                series: [{
                    name: 'Volume (lbs)',
                    data: chartData.map(item => item.volume)
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    zoom: {
                        enabled: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    categories: chartData.map(item => item.date),
                },
                yaxis: {
                    title: {
                        text: 'Volume (lbs)'
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.9,
                        stops: [0, 90, 100]
                    }
                },
                theme: {
                    mode: 'light', 
                    palette: 'palette1', 
                },
                colors: ['#845adf']
            };

            var chart = new ApexCharts(document.querySelector("#volumeChart"), options);
            chart.render();
        } else {
            document.querySelector("#volumeChart").innerHTML = '<div class="text-center p-4 text-muted">Not enough data to display chart</div>';
        }
    });
</script>
@endsection
