@extends('layouts.master')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Payouts</h1>
            <div>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payouts</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="ms-auto pageheader-btn">
            <a href="{{ route('admin.payouts.export') }}" class="btn btn-success btn-wave waves-effect waves-light">
                <i class="ri-download-line fw-semibold align-middle me-1"></i> Export CSV
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Total Payouts"
                value="{{ $stats['total_payouts'] }}"
                icon="ri-exchange-dollar-line"
                color="primary"
                badgeText="All time requests"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Total Paid"
                value="${{ number_format($stats['total_paid'], 2) }}"
                icon="ri-check-double-line"
                color="success"
                badgeText="Successfully transferred"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Pending Amount"
                value="${{ number_format($stats['pending_amount'], 2) }}"
                icon="ri-time-line"
                color="warning"
                badgeText="Awaiting processing"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Pending Requests"
                value="{{ $stats['pending_count'] }}"
                icon="ri-file-list-line"
                color="info"
                badgeText="To be processed"
            />
        </div>
    </div>

    <x-tables.card title="All Payouts">
        <x-slot:tools>
            <div class="d-flex">
                <select name="status" class="form-select form-select-sm me-2" style="max-width:180px">
                    <option value="">All Status</option>
                    @foreach(['processing','completed','failed'] as $st)
                        <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:tools>

        <x-tables.table 
            id="payoutsTable" 
            :headers="['Sr.#', 'Trainer', 'Amount', 'Currency', 'Fee', 'Status', 'Scheduled', 'Created', 'Action']"
        >
        </x-tables.table>
    </x-tables.card>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jQuery === 'undefined') return;
            var $ = jQuery;

            $(function(){
                var table = $('#payoutsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('admin.payouts.index') }}",
                        data: function (d) {
                            d.status = $('select[name="status"]').val();
                        }
                    },
                    columns: [
                        { data: 'id', name: 'id', orderable: false },
                        { data: 'trainer', name: 'trainer' },
                        { data: 'amount', name: 'amount' },
                        { data: 'currency', name: 'currency' },
                        { data: 'fee', name: 'fee_amount', searchable: false },
                        { data: 'status', name: 'payout_status' },
                        { data: 'scheduled', name: 'scheduled_at', searchable: false },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    order: [[0, 'desc']]
                });

                $('select[name="status"]').change(function(){
                    table.draw();
                });
            });
        });

        function confirmPayout(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to process this payout?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, process it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('payout-form-' + id).submit();
                }
            });
        }
    </script>
@endsection
