@extends('layouts.master')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Payment Gateways</h1>
            <div>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payment Gateways</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="ms-auto pageheader-btn">
            <button class="btn btn-primary btn-wave waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#gatewayModal">
                <i class="ri-add-line fw-semibold align-middle me-1"></i> Add Gateway
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Total Gateways"
                value="{{ $stats['total_gateways'] }}"
                icon="ri-bank-card-line"
                color="primary"
                badgeText="Configured gateways"
            />
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Active Gateways"
                value="{{ $stats['active_gateways'] }}"
                icon="ri-checkbox-circle-line"
                color="success"
                badgeText="Currently enabled"
            />
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Default Gateway"
                value="{{ $stats['default_gateway'] }}"
                icon="ri-star-line"
                color="warning"
                badgeText="Primary method"
            />
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <x-tables.card title="All Gateways">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-line me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <x-tables.table 
                    id="gatewaysTable" 
                    :headers="['Sr.#', 'Name', 'Type', 'Status', 'Default', 'Actions']"
                >
                </x-tables.table>
            </x-tables.card>
        </div>
    </div>

    <!-- Edit Gateway Modal -->
    <div class="modal fade" id="editGatewayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Gateway</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editGatewayForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Public Key</label>
                            <input type="text" class="form-control" name="public_key" id="edit_public_key">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Secret Key</label>
                            <input type="text" class="form-control" name="secret_key" id="edit_secret_key">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Webhook Secret</label>
                            <input type="text" class="form-control" name="webhook_secret" id="edit_webhook_secret">
                        </div>
                        <div class="mb-3" id="edit_stripe_connect_div" style="display: none;">
                            <label class="form-label">Stripe Connect Client ID</label>
                            <input type="text" class="form-control" name="connect_client_id" id="edit_connect_client_id">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="gatewayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Add Payment Gateway</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.payment-gateways.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Stripe" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" required>
                                <option value="stripe">Stripe</option>
                                <option value="paypal">PayPal</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Public Key</label>
                            <input type="text" class="form-control" name="public_key">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Secret Key</label>
                            <input type="text" class="form-control" name="secret_key">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Webhook Secret</label>
                            <input type="text" class="form-control" name="webhook_secret">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stripe Connect Client ID (Stripe)</label>
                            <input type="text" class="form-control" name="connect_client_id">
                        </div>
                        <input type="hidden" name="enabled" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Gateway</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jQuery === 'undefined') return;
            var $ = jQuery;

            $(document).ready(function() {
                $('#gatewaysTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: "{{ route('admin.payment-gateways.index') }}",
                    columns: [
                        { data: 'id', name: 'id', orderable: false },
                        { data: 'name', name: 'name' },
                        { data: 'type', name: 'type' },
                        { data: 'status', name: 'status', orderable: false, searchable: false },
                        { data: 'default', name: 'default', orderable: false, searchable: false },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    language: {
                        processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
                    }
                });
            });
        });

        function editGateway(data) {
            // Populate form
            $('#edit_name').val(data.name);
            $('#edit_public_key').val(data.public_key);
            $('#edit_secret_key').val(data.secret_key);
            $('#edit_webhook_secret').val(data.webhook_secret);
            $('#edit_connect_client_id').val(data.connect_client_id);
            
            if (data.type === 'stripe') {
                $('#edit_stripe_connect_div').show();
            } else {
                $('#edit_stripe_connect_div').hide();
            }

            // Set action URL
            let url = "{{ route('admin.payment-gateways.update', 'GATEWAY_ID') }}";
            url = url.replace('GATEWAY_ID', data.id);
            $('#editGatewayForm').attr('action', url);

            // Show modal
            new bootstrap.Modal(document.getElementById('editGatewayModal')).show();
        }
    </script>
@endsection
