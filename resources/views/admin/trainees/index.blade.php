@extends('layouts.master')

@section('styles')
 
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
    // Initialize DataTable
    let traineesTable = $('#traineesTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("admin.trainees.index") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            { data: 'id', name: 'id', width: '5%', orderable: false },
            { 
                data: 'name', 
                name: 'name', 
                width: '25%',
                render: function(data, type, row) {
                    let avatar = '';
                    if (row.profile_image) {
                        avatar = `<img src="${row.profile_image}" alt="Profile" class="avatar avatar-sm avatar-rounded me-2">`;
                    } else {
                        avatar = `<span class="avatar avatar-sm avatar-rounded bg-info-transparent me-2">
                                    <i class="ri-user-line"></i>
                                </span>`;
                    }
                    return `<div class="d-flex align-items-center">
                                ${avatar}
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">${data}</span>
                                    <small class="text-muted">${row.email || ''}</small>
                                </div>
                            </div>`;
                }
            },
            { data: 'phone', name: 'phone', width: '10%' },
            { 
                data: 'goals_count', 
                name: 'goals_count', 
                width: '8%',
                render: function(data, type, row) {
                    return `<span class="badge bg-primary-transparent">${data} Goals</span>`;
                }
            },
            { 
                data: 'status', 
                name: 'status', 
                width: '8%',
                render: function(data, type, row) {
                    if (data === 'Active') {
                        return '<span class="badge bg-success-transparent">Active</span>';
                    }
                    return '<span class="badge bg-danger-transparent">Inactive</span>';
                }
            },
            { data: 'created_at', name: 'created_at', width: '12%' },
            { 
                data: 'id', 
                name: 'actions', 
                orderable: false, 
                searchable: false,
                width: '15%',
                render: function(data, type, row) {
                    return `
                      <div class="hstack gap-2 fs-15 justify-content-end">
                            <button type="button" class="btn btn-icon btn-sm btn-info-transparent rounded-pill" onclick="viewTrainee(${data})" title="View">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-sm btn-primary-transparent rounded-pill" onclick="editTrainee(${data})" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-sm btn-warning-transparent rounded-pill" onclick="toggleTraineeStatus(${data})" title="Toggle Status">
                                <i class="ri-toggle-line"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill" onclick="deleteTrainee(${data})" title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                      </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: 'No trainees found',
            zeroRecords: 'No matching trainees found'
        }
    });

    // Filter handlers
    $('#statusFilter').on('change', function() {
        traineesTable.ajax.reload();
    });

    // Refresh button
    $('#refreshBtn').on('click', function() {
        traineesTable.ajax.reload();
    });
});

// View trainee function
function viewTrainee(id) {
    window.location.href = `{{ route('admin.users.index') }}/${id}`;
}

// Edit trainee function
function editTrainee(id) {
    window.location.href = `{{ route('admin.users.index') }}/${id}/edit`;
}

// Toggle trainee status
function toggleTraineeStatus(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to toggle this trainee's status?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, toggle it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/trainees/${id}/toggle-status`,
                method: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PATCH'
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    console.log('Toggle status response:', response);
                    if (response.success) {
                        // Reload DataTable and wait for it to complete
                        $('#traineesTable').DataTable().ajax.reload(function(json) {
                            console.log('DataTable reloaded, new data:', json);
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Trainee status updated successfully',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }, false); // false = don't reset pagination
                    } else {
                        Swal.fire('Error!', response.message || 'Failed to update status', 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Toggle status error:', xhr);
                    let message = 'Failed to update status';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        message = 'Route not found. Please check the route configuration.';
                    } else if (xhr.status === 403) {
                        message = 'You do not have permission to perform this action.';
                    } else if (xhr.status === 500) {
                        message = 'Server error. Please try again later.';
                    }
                    Swal.fire('Error!', message, 'error');
                }
            });
        }
    });
}

// Delete trainee function
function deleteTrainee(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this! This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ route('admin.trainees.index') }}/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#traineesTable').DataTable().ajax.reload();
                        Swal.fire('Deleted!', response.message, 'success');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let message = 'Failed to delete trainee';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', message, 'error');
                }
            });
        }
    });
}

// Show alert function
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#alertContainer').html(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}
</script>
@endsection

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">Trainees Management</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Trainees</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="{{ route('admin.trainees.create') }}" class="btn btn-primary btn-wave">
            <i class="ri-add-line fw-semibold align-middle me-1"></i> Add New Trainee
        </a>
    </div>
</div>
<!-- Page Header Close -->

<!-- Alert Container -->
<div id="alertContainer"></div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Total Trainees"
            value="{{ $stats['total_trainees'] }}"
            icon="ri-user-line"
            color="primary"
        />
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Active Trainees"
            value="{{ $stats['active_trainees'] }}"
            icon="ri-user-follow-line"
            color="success"
        />
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Inactive Trainees"
            value="{{ $stats['inactive_trainees'] }}"
            icon="ri-user-unfollow-line"
            color="danger"
        />
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="With Goals"
            value="{{ $stats['trainees_with_goals'] }}"
            icon="ri-flag-line"
            color="info"
        />
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-xl-12">
        <x-tables.card title="Trainees List">
            <x-slot:tools>
                <div>
                    <select class="form-select" id="statusFilter">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <button class="btn btn-outline-light btn-wave" id="refreshBtn">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>
            </x-slot:tools>

            <x-tables.table 
                id="traineesTable" 
                :headers="['Sr.#', 'Profile', 'Phone', 'Goals', 'Status', 'Created', 'Actions']"
                :bordered="true"
                :striped="true"
                :hover="true"
            />
        </x-tables.card>
    </div>
</div>
@endsection