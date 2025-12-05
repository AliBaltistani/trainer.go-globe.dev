@extends('layouts.master')

@section('title', 'My Clients')

@section('content')

<!-- Page Header -->
<div class="page-header-breadcrumb mb-3">
    <div class="d-flex align-center justify-content-between flex-wrap">
        <h1 class="page-title fw-medium fs-18 mb-0">My Clients</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Clients</li>
        </ol>
    </div>
</div>

<!-- Success Message -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Search and Filter -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card custom-card">
            <div class="card-body">
                <form method="GET" action="{{ route('trainer.clients.index') }}" class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-3 text-end">
                        <a href="{{ route('trainer.clients.create') }}" class="btn btn-success">
                            <i class="ri-user-add-line me-1"></i> Add New Client
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Clients List -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clients as $client)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm me-2 avatar-rounded">
                                            <img src="{{ $client->profile_image ? asset('storage/'.$client->profile_image) : asset('assets/images/faces/9.jpg') }}" alt="img">
                                        </span>
                                        <div>
                                            <div class="fw-semibold">{{ $client->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $client->email }}</td>
                                <td>{{ $client->phone ?? 'N/A' }}</td>
                                <td>
                                    @if($client->subscriptionsAsClient->where('trainer_id', Auth::id())->where('status', 'active')->count() > 0)
                                        <span class="badge bg-success-transparent">Active</span>
                                    @else
                                        <span class="badge bg-danger-transparent">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $client->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="hstack gap-2 fs-15">
                                        <a href="{{ route('trainer.clients.show', $client->id) }}" class="btn btn-icon btn-sm btn-info-light" data-bs-toggle="tooltip" title="View Profile">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <!-- Add more actions like edit subscription, etc. here -->
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="ri-user-unfollow-line fs-2 d-block mb-2"></i>
                                    No clients found matching your criteria.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-end mt-3">
                    {{ $clients->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
