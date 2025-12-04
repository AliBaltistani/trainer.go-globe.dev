@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Workout Programs</h1>
            <p class="mb-0 text-muted">Manage workout programs and assign them to clients</p>
        </div>
        <a href="{{ route('programs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create New Program
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <x-widgets.stat-card-style1
                title="Total Programs"
                value="0"
                icon="ri-file-list-line"
                color="primary"
                valueId="total-programs"
            />
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <x-widgets.stat-card-style1
                title="Active Programs"
                value="0"
                icon="ri-checkbox-circle-line"
                color="success"
                valueId="active-programs"
            />
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <x-widgets.stat-card-style1
                title="Assigned Programs"
                value="0"
                icon="ri-user-follow-line"
                color="info"
                valueId="assigned-programs"
            />
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <x-widgets.stat-card-style1
                title="Unassigned Programs"
                value="0"
                icon="ri-user-unfollow-line"
                color="warning"
                valueId="unassigned-programs"
            />
        </div>
    </div>

    <!-- Main Content -->
    <x-tables.card title="Programs List">
        <x-tables.table 
            id="programsTable"
            :headers="['ID', 'Program Name', 'Trainer', 'Client', 'Duration', 'Weeks', 'Status', 'Created', 'Actions']"
            :bordered="true"
            width="100%" 
            cellspacing="0"
        >
            <tbody>
                <!-- Data will be loaded via AJAX -->
            </tbody>
        </x-tables.table>
    </x-tables.card>
</div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#programsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('programs.index') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'trainer', name: 'trainer' },
                    { data: 'client', name: 'client' },
                    { data: 'duration', name: 'duration' },
                    { data: 'weeks_count', name: 'weeks_count', orderable: false, searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
                }
            });

            // Load statistics
            loadStatistics();

            $(document).on('click', '.program-pdf-show', function() {
                var id = $(this).data('program-id');
                window.open('/admin/programs/' + id + '/pdf-view', '_blank');
            });

            $(document).on('click', '.program-pdf-download', function() {
                var btn = this;
                var id = $(btn).data('program-id');
                var original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Downloading...';
                var a = document.createElement('a');
                a.href = '/admin/programs/' + id + '/pdf-download';
                a.download = 'program-' + id + '.pdf';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                setTimeout(function(){ btn.disabled = false; btn.innerHTML = original; }, 1500);
            });
        });

        function loadStatistics() {
            $.ajax({
                url: "{{ route('programs.stats') }}",
                type: 'GET',
                success: function(data) {
                    $('#total-programs').text(data.total_programs);
                    $('#active-programs').text(data.active_programs);
                    $('#assigned-programs').text(data.assigned_programs);
                    $('#unassigned-programs').text(data.unassigned_programs);
                },
                error: function() {
                    $('#total-programs, #active-programs, #assigned-programs, #unassigned-programs').text('Error');
                }
            });
        }

        function deleteProgram(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this! This will also delete all associated weeks, days, circuits, and exercises.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/programs/${id}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                );
                                $('#programsTable').DataTable().ajax.reload();
                                loadStatistics();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'An error occurred while deleting the program.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        function fetchProgramPdfUrl(id) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: '/admin/programs/' + id + '/pdf-data',
                    type: 'GET',
                    success: function(resp) {
                        if (resp && resp.success && resp.data && resp.data.pdf_view_url) {
                            resolve(resp.data.pdf_view_url);
                        } else {
                            reject();
                        }
                    },
                    error: function() { reject(); }
                });
            });
        }

        
    </script>
@endsection
