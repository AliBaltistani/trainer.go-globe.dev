@extends('layouts.master')

@section('title', 'Add New Client')

@section('content')

<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-center justify-content-between flex-wrap">
        <h1 class="page-title fw-medium fs-18 mb-0">Add New Client</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trainer.clients.index') }}">Clients</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Client</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    Client Information
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('trainer.clients.store') }}">
                    @csrf
                    
                    <!-- Personal Information -->
                    <h6 class="mb-3 fw-bold text-primary">Personal Details</h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name') }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name') }}" required>
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                            <div class="form-text">An invitation email will be sent to this address.</div>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Fitness Profile -->
                    <h6 class="mb-3 fw-bold text-primary">Fitness Profile (Optional)</h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fitness Level</label>
                            <select class="form-select @error('fitness_level') is-invalid @enderror" name="fitness_level">
                                <option value="">Select Level</option>
                                <option value="Beginner" {{ old('fitness_level') == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="Intermediate" {{ old('fitness_level') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="Advanced" {{ old('fitness_level') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                            </select>
                            @error('fitness_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label d-block">Fitness Goals</label>
                            <div class="btn-group-toggle d-flex flex-wrap gap-2" data-toggle="buttons">
                                @php $goals = ['Lose Weight', 'Build Muscle', 'Improve Endurance', 'Flexibility', 'General Health']; @endphp
                                @foreach($goals as $goal)
                                    <input type="checkbox" class="btn-check" id="goal_{{ Str::slug($goal) }}" name="fitness_goals[]" value="{{ $goal }}" {{ in_array($goal, old('fitness_goals', [])) ? 'checked' : '' }}>
                                    <label class="btn btn-outline-light text-dark" for="goal_{{ Str::slug($goal) }}">{{ $goal }}</label>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Health Considerations / Injuries</label>
                            <textarea class="form-control @error('health_considerations') is-invalid @enderror" name="health_considerations" rows="3">{{ old('health_considerations') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('trainer.clients.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Client & Send Invite</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
