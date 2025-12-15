@extends('layouts.master')

@section('content')
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">Specialization Management</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Specializations</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="{{ route('admin.specializations.create') }}" class="btn btn-primary btn-wave waves-effect waves-light">
            <i class="ri-add-line align-middle me-1"></i>Add New Specialization
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Total Specializations"
            value="{{ $stats['total'] }}"
            icon="ri-award-line"
            color="primary"
        />
    </div>
    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Active Specializations"
            value="{{ $stats['active'] }}"
            icon="ri-check-line"
            color="success"
        />
    </div>
    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Inactive Specializations"
            value="{{ $stats['inactive'] }}"
            icon="ri-close-line"
            color="warning"
        />
    </div>
    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="With Trainers"
            value="{{ $stats['with_trainers'] }}"
            icon="ri-user-star-line"
            color="info"
        />
    </div>
</div>

<!-- Specializations Table -->
<div class="row">
    <div class="col-xl-12">
        <x-tables.card title="Specializations List">
            <x-slot:tools>
                <div class="d-flex">
                    <!-- Status Filter -->
                    <div class="me-3">
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <!-- Export Buttons -->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="exportExcel">
                            <i class="ri-file-excel-line me-1"></i>Excel
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="exportPdf">
                            <i class="ri-file-pdf-line me-1"></i>PDF
                        </button>
                    </div>
                </div>
            </x-slot:tools>

            <x-tables.table 
                id="specializationsTable"
                :headers="['Sr.#', 'Name', 'Description', 'Status', 'Trainers Count', 'Created At', 'Actions']"
                :bordered="true"
                :striped="true"
                :hover="true"
            >
                <!-- Data will be loaded via AJAX -->
            </x-tables.table>
        </x-tables.card>
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

@endsection

@section('scripts')


<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#specializationsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('admin.specializations.index') }}",
            data: function(d) {
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            { data: 'id', name: 'id', orderable: false },
            { data: 'name', name: 'name' },
            { data: 'description', name: 'description', orderable: false },
            { data: 'status', name: 'status', orderable: false },
            { data: 'trainers_count', name: 'trainers_count' },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="ri-file-excel-line"></i> Excel',
                className: 'btn btn-success btn-sm d-none',
                exportOptions: {
                    columns: [0, 1, 2, 4, 5] // Exclude status and actions columns
                }
            },
            {
                extend: 'pdf',
                text: '<i class="ri-file-pdf-line"></i> PDF',
                className: 'btn btn-danger btn-sm d-none',
                exportOptions: {
                    columns: [0, 1, 2, 4, 5] // Exclude status and actions columns
                }
            }
        ],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No specializations found",
            zeroRecords: "No matching specializations found"
        }
    });

    // Status filter change
    $('#statusFilter').on('change', function() {
        table.ajax.reload();
        
        // Update URL without page reload
        var url = new URL(window.location);
        url.searchParams.set('status', this.value);
        window.history.pushState({}, '', url);
    });

    // Export buttons
    $('#exportExcel').on('click', function() {
        table.button('.buttons-excel').trigger();
    });

    $('#exportPdf').on('click', function() {
        table.button('.buttons-pdf').trigger();
    });

    // Toggle Status
    $(document).on('click', '.toggle-status', function(e) {
        e.preventDefault();
        
        var id = $(this).data('id');
        var status = $(this).data('status');
        var button = $(this);
        var originalHtml = button.html();
        
        // Validate required data
        if (!id || status === undefined) {
            showAlert('error', 'Invalid data. Please refresh the page and try again.');
            return;
        }
        
        $.ajax({
            url: "{{ route('admin.specializations.toggle-status', ':id') }}".replace(':id', id),
            type: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}',
                status: status
            },
            beforeSend: function() {
                button.prop('disabled', true);
                button.html('<i class="ri-loader-2-line ri-spin"></i>');
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showAlert('success', response.message);
                    
                    // Reload table to reflect changes
                    table.ajax.reload(null, false);
                } else {
                    showAlert('error', response.message || 'Failed to update status.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                
                var message = 'An error occurred while updating status.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    message = 'Specialization not found.';
                } else if (xhr.status === 403) {
                    message = 'You do not have permission to perform this action.';
                } else if (xhr.status === 422) {
                    message = 'Invalid data provided.';
                } else if (xhr.status === 500) {
                    message = 'Server error occurred. Please try again later.';
                }
                
                showAlert('error', message);
            },
            complete: function() {
                button.prop('disabled', false);
                button.html(originalHtml);
            }
        });
    });

    // Delete Specialization
    $(document).on('click', '.delete-specialization', function() {
        var id = $(this).data('id');
        var button = $(this);
        
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
                    url: "{{ route('admin.specializations.destroy', ':id') }}".replace(':id', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        button.prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            );
                            
                            // Reload table
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON?.message || 'An error occurred while deleting specialization.';
                        Swal.fire(
                            'Error!',
                            message,
                            'error'
                        );
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            }
        });
    });

    // Show Alert Function
    function showAlert(type, message) {
        Swal.fire({
            icon: type,
            title: type.charAt(0).toUpperCase() + type.slice(1),
            text: message,
            timer: 5000,
            timerProgressBar: true
        });
    }
});
</script>
@endsection