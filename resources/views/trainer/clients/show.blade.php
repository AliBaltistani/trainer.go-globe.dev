@extends('layouts.master')

@section('title', $client->name . ' - Profile')

@section('content')

<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-center justify-content-between flex-wrap">
        <h1 class="page-title fw-medium fs-18 mb-0">Client Profile</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trainer.clients.index') }}">Clients</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $client->name }}</li>
        </ol>
    </div>
</div>

<div class="row">
    <!-- Left Sidebar -->
    <div class="col-xl-3">
        <div class="card custom-card">
            <div class="card-body text-center">
                <span class="avatar avatar-xxl avatar-rounded mb-3">
                    <img src="{{ $client->profile_image ? asset('storage/'.$client->profile_image) : asset('assets/images/faces/9.jpg') }}" alt="img">
                </span>
                <h5 class="fw-semibold mb-1">{{ $client->name }}</h5>
                <p class="text-muted mb-2">Client</p>
                
                <div class="d-flex justify-content-center gap-2 mb-3">
                    @if($client->phone)
                    <a href="tel:{{ $client->phone }}" class="btn btn-sm btn-icon btn-outline-primary rounded-circle">
                        <i class="ri-phone-line"></i>
                    </a>
                    @endif
                    <a href="mailto:{{ $client->email }}" class="btn btn-sm btn-icon btn-outline-primary rounded-circle">
                        <i class="ri-mail-line"></i>
                    </a>
                </div>
                
                <div class="text-start mt-4">
                    <h6 class="fw-semibold mb-3">Contact Details</h6>
                    <div class="mb-2">
                        <span class="fw-medium text-muted me-2">Email:</span>
                        <span>{{ $client->email }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="fw-medium text-muted me-2">Phone:</span>
                        <span>{{ $client->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="fw-medium text-muted me-2">Joined:</span>
                        <span>{{ $client->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Subscription Status -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Subscription</div>
            </div>
            <div class="card-body">
                @php
                    $subscription = $client->subscriptionsAsClient->where('trainer_id', Auth::id())->first();
                @endphp
                
                @if($subscription)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Status:</span>
                        @if($subscription->status == 'active')
                            <span class="badge bg-success-transparent">Active</span>
                        @else
                            <span class="badge bg-danger-transparent">{{ ucfirst($subscription->status) }}</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Since:</span>
                        <span>{{ \Carbon\Carbon::parse($subscription->start_date)->format('M d, Y') }}</span>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">No active subscription found.</div>
                @endif
            </div>
        </div>

        <!-- Latest Note -->
         <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Latest Note</div>
                <a href="#" onclick="document.querySelector('[href=\'#notes-tab-pane\']').click()" class="btn btn-sm btn-light">View All</a>
            </div>
            <div class="card-body">
                @if($latestNote)
                    <p class="mb-0 text-muted">{{ Str::limit($latestNote->note, 100) }}</p>
                    <div class="mt-2 text-end">
                        <small class="text-muted">{{ $latestNote->created_at->diffForHumans() }}</small>
                    </div>
                @else
                    <p class="text-muted mb-0">No notes added yet.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Content -->
    <div class="col-xl-9">
        <div class="card custom-card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#overview-tab-pane" role="tab">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#weight-tab-pane" role="tab">Weight Log</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#health-tab-pane" role="tab">Health Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#programs-tab-pane" role="tab">Programs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#notes-tab-pane" role="tab">Notes</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview-tab-pane" role="tabpanel">
                        <div class="row">
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                <div class="card custom-card bg-primary-transparent">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="avatar avatar-md bg-primary text-white">
                                                    <i class="ri-dumbbell-line fs-18"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-1 text-muted">Workouts Completed</p>
                                                <h5 class="mb-0 fw-semibold">{{ $workoutsCompleted }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                <div class="card custom-card bg-success-transparent">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="avatar avatar-md bg-success text-white">
                                                    <i class="ri-calendar-check-line fs-18"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-1 text-muted">Total Sessions</p>
                                                <h5 class="mb-0 fw-semibold">{{ $totalSessions }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                <div class="card custom-card bg-info-transparent">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="avatar avatar-md bg-info text-white">
                                                    <i class="ri-scales-3-line fs-18"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-1 text-muted">Weight Change</p>
                                                <h5 class="mb-0 fw-semibold">
                                                    {{ ($weightChange > 0 ? '+' : '') . number_format($weightChange, 1) }} 
                                                    {{ $weightLogs->last()->unit ?? 'lbs' }}
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Active Goals</h6>
                                @if($client->goals->where('status', 1)->count() > 0)
                                    <div class="list-group">
                                        @foreach($client->goals->where('status', 1) as $goal)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="ri-checkbox-circle-line text-success me-2"></i>
                                                    {{ $goal->name }}
                                                </div>
                                                <small class="text-muted">Set {{ $goal->created_at->format('M d') }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">No active goals.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Weight Tab -->
                    <div class="tab-pane fade" id="weight-tab-pane" role="tabpanel">
                        <div class="d-flex justify-content-between mb-3">
                            <h6>Weight History</h6>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addWeightModal">
                                <i class="ri-add-line me-1"></i> Log Weight
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table text-nowrap table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Weight</th>
                                        <th>Unit</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allWeightLogs as $log)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($log->logged_at)->format('M d, Y') }}</td>
                                            <td>{{ $log->weight }}</td>
                                            <td>{{ $log->unit }}</td>
                                            <td>{{ $log->notes ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No weight logs found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $allWeightLogs->links() }}
                        </div>
                    </div>

                    <!-- Health Tab -->
                    <div class="tab-pane fade" id="health-tab-pane" role="tabpanel">
                        <form action="{{ route('trainer.clients.health-profile.update', $client->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row gy-3">
                                <div class="col-12">
                                    <label class="form-label">Fitness Level</label>
                                    <select name="fitness_level" class="form-control">
                                        <option value="">Select Level</option>
                                        <option value="beginner" {{ ($healthProfile->fitness_level ?? '') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="intermediate" {{ ($healthProfile->fitness_level ?? '') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="advanced" {{ ($healthProfile->fitness_level ?? '') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Chronic Conditions (Comma separated)</label>
                                    <textarea name="chronic_conditions" class="form-control" rows="3">{{ is_array($healthProfile->chronic_conditions ?? null) ? implode(',', $healthProfile->chronic_conditions) : ($healthProfile->chronic_conditions ?? '') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Allergies (Comma separated)</label>
                                    <textarea name="allergies" class="form-control" rows="3">{{ is_array($healthProfile->allergies ?? null) ? implode(',', $healthProfile->allergies) : ($healthProfile->allergies ?? '') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Programs Tab -->
                    <div class="tab-pane fade" id="programs-tab-pane" role="tabpanel">
                        <div class="d-flex justify-content-between mb-3">
                            <h6>Assigned Programs</h6>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignProgramModal">
                                <i class="ri-add-line me-1"></i> Assign Program
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table text-nowrap table-bordered">
                                <thead>
                                    <tr>
                                        <th>Program Name</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($clientPrograms as $program)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $program->name }}</div>
                                                <small class="text-muted">{{ Str::limit($program->description, 30) }}</small>
                                            </td>
                                            <td>{{ $program->duration }} Weeks</td>
                                            <td>
                                                @if($program->is_active)
                                                    <span class="badge bg-success-transparent">Active</span>
                                                @else
                                                    <span class="badge bg-light text-dark">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $program->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="hstack gap-2 fs-15">
                                                    <a href="{{ route('trainer.programs.show', $program->id) }}" class="btn btn-icon btn-sm btn-info-light" data-bs-toggle="tooltip" title="View Program">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="{{ route('trainer.programs.progress', $program->id) }}" class="btn btn-icon btn-sm btn-success-light" data-bs-toggle="tooltip" title="Workouts Completed">
                                                        <i class="ri-checkbox-circle-line"></i>
                                                    </a>
                                                    <a href="{{ route('trainer.programs.edit', $program->id) }}" class="btn btn-icon btn-sm btn-warning-light" data-bs-toggle="tooltip" title="Edit Program">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" onclick="deleteProgram('{{ $program->id }}')" class="btn btn-icon btn-sm btn-danger-light" data-bs-toggle="tooltip" title="Delete Program">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">
                                                <i class="ri-file-list-3-line fs-2 d-block mb-2"></i>
                                                No programs assigned yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Notes Tab -->
                    <div class="tab-pane fade" id="notes-tab-pane" role="tabpanel">
                        <div class="mb-4">
                            <h6>Add Note</h6>
                            <form action="{{ route('trainer.clients.notes.store', $client->id) }}" method="POST">
                                @csrf
                                <div class="input-group">
                                    <textarea name="note" class="form-control" rows="2" placeholder="Type a note..." required></textarea>
                                    <button class="btn btn-primary" type="submit">Add Note</button>
                                </div>
                            </form>
                        </div>
                        
                        <h6>History</h6>
                        <div class="list-group">
                            @forelse($allNotes as $note)
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $note->trainer->name }}</h6>
                                        <small>{{ $note->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">{{ $note->note }}</p>
                                </div>
                            @empty
                                <div class="text-center p-3 text-muted">No notes found.</div>
                            @endforelse
                        </div>
                        <div class="mt-3">
                            {{ $allNotes->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Weight Modal -->
<div class="modal fade" id="addWeightModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Weight</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('trainer.clients.weight.store', $client->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="logged_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Weight</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="weight" class="form-control" required>
                            <select name="unit" class="form-select" style="max-width: 80px;">
                                <option value="lbs">lbs</option>
                                <option value="kg">kg</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Log</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Program Modal -->
<div class="modal fade" id="assignProgramModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignProgramForm" method="POST">
                @csrf
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Program Template</label>
                        <select id="programSelect" class="form-select" required>
                            <option value="">Choose a program...</option>
                            @foreach($programTemplates as $template)
                                <option value="{{ $template->id }}" data-url="{{ route('trainer.programs.assign', $template->id) }}">
                                    {{ $template->name }} ({{ $template->duration }} Weeks)
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">This will create a copy of the program assigned to this client.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Assign Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Program Assignment
        const programSelect = document.getElementById('programSelect');
        const assignForm = document.getElementById('assignProgramForm');
        
        if(programSelect && assignForm) {
            programSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if(selectedOption.value) {
                    assignForm.action = selectedOption.dataset.url;
                }
            });

            assignForm.addEventListener('submit', function(e) {
                if(!programSelect.value) {
                    e.preventDefault();
                    alert('Please select a program');
                }
            });
        }
    });

    // Handle Program Deletion
    function deleteProgram(id) {
        if(confirm('Are you sure you want to delete this program?')) {
            fetch('{{ url("trainer/programs") }}/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                } else {
                    alert('Error deleting program: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the program');
            });
        }
    }
</script>

@endsection