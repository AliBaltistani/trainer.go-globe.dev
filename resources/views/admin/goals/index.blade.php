@extends('layouts.master')

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">Goals Management</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Goals</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="{{route('goals.create')}}" class="btn btn-primary btn-wave waves-effect waves-light me-2">
            <i class="ri-add-line me-1"></i> Create New Goal
        </a>
    </div>
</div>
<!-- Page Header Close -->

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Total Goals"
            value="{{ $stats['total_goals'] }}"
            icon="ti ti-target"
            color="primary"
        />
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Active Goals"
            value="{{ $stats['active_goals'] }}"
            icon="ri-check-line"
            color="success"
        />
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Inactive Goals"
            value="{{ $stats['inactive_goals'] }}"
            icon="ri-pause-line"
            color="warning"
        />
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Goals with Users"
            value="{{ $stats['goals_with_users'] }}"
            icon="ti ti-users"
            color="info"
        />
    </div>
</div>

<!-- Success/Error Messages -->
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

<!-- Main Content -->
<div class="row">
    <div class="col-xl-12">
        <x-tables.card title="Goals List">
            <x-slot:tools>
                <div class="d-flex">
                    <div class="me-3">
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="dropdown me-3">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="ri-filter-3-line me-1"></i> Filters
                        </button>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">Filter by User</h6></li>
                            <li><a class="dropdown-item filter-user" href="#" data-user="">All Users</a></li>
                            <li><a class="dropdown-item filter-user" href="#" data-user="assigned">With Users</a></li>
                            <li><a class="dropdown-item filter-user" href="#" data-user="unassigned">Unassigned</a></li>
                        </ul>
                    </div>
                </div>
            </x-slot:tools>
            
            <x-tables.table 
                id="goalsTable"
                :headers="['Sr.#', 'Goal Name', 'User', 'Status', 'Created Date', 'Updated Date', 'Actions']"
                :bordered="true"
                :striped="true"
                :hover="true"
            >
                <!-- Data will be loaded via AJAX -->
            </x-tables.table>
        </x-tables.card>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    console.log('jQuery loaded:', typeof $ !== 'undefined');
    console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');
    console.log('Table element found:', $('#goalsTable').length > 0);
    
    // Initialize DataTable
    try {
        var table = $('#goalsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("goals.index") }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: function(d) {
                    d.status_filter = $('#statusFilter').val();
                    d.user_filter = $('#userFilter').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables AJAX Error:', error, thrown);
                    console.error('Response:', xhr.responseText);
                }
            },
            columns: [
                { data: 'id', name: 'id', width: '5%', orderable: false },
                { 
                    data: 'name', 
                    name: 'name',
                    render: function(data, type, row) {
                        return '<div class="fw-semibold">' + data + '</div>';
                    }
                },
                { 
                    data: 'user', 
                    name: 'user',
                    render: function(data, type, row) {
                        return data || '<span class="text-muted">Unassigned</span>';
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data, type, row) {
                        if (data == 1) {
                            return '<span class="badge bg-success-transparent">Active</span>';
                        } else {
                            return '<span class="badge bg-danger-transparent">Inactive</span>';
                        }
                    }
                },
                { 
                    data: 'created_at', 
                    name: 'created_at',
                    render: function(data, type, row) {
                        return '<small class="text-muted">' + data + '</small>';
                    }
                },
                { 
                    data: 'updated_at', 
                    name: 'updated_at',
                    render: function(data, type, row) {
                        return '<small class="text-muted">' + data + '</small>';
                    }
                },
                { 
                    data: 'actions', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    width: '15%'
                }
            ],
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            language: {
                search: "",
                searchPlaceholder: "Search goals...",
                paginate: {
                    next: '<i class="ri-arrow-right-s-line"></i>',
                    previous: '<i class="ri-arrow-left-s-line"></i>'
                }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            drawCallback: function() {
                // Initialize tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
        
        console.log('DataTable initialized successfully');
        
    } catch (error) {
        console.error('DataTable initialization failed:', error);
        $('#goalsTable').html('<tr><td colspan="7" class="text-center text-danger">Failed to initialize data table: ' + error.message + '</td></tr>');
    }

    // Search functionality
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Status filter change handler
    $('#statusFilter').on('change', function() {
        table.ajax.reload();
    });

    // Filter functionality
    $('.filter-user').on('click', function(e) {
        e.preventDefault();
        var user = $(this).data('user');
        if (!$('#userFilter').length) {
            $('body').append('<input type="hidden" id="userFilter">');
        }
        $('#userFilter').val(user);
        table.ajax.reload();
    });
});

// Action Functions
function toggleStatus(goalId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to change the status of this goal?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("goals.toggle-status", ":id") }}'.replace(':id', goalId),
                type: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success');
                        $('#goalsTable').DataTable().ajax.reload();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    var message = 'Failed to toggle goal status';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', message, 'error');
                }
            });
        }
    });
}

function deleteGoal(goalId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this goal? This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("goals.destroy", ":id") }}'.replace(':id', goalId),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        $('#goalsTable').DataTable().ajax.reload();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    var message = 'Failed to delete goal';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', message, 'error');
                }
            });
        }
    });
}
</script>
@endsection