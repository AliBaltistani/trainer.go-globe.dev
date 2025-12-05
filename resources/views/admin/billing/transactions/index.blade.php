@extends('layouts.master')

@section('title', 'Transactions')

@section('content')
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <p class="fw-semibold fs-18 mb-0">Transactions</p>
        <span class="fs-semibold text-muted">Billing & Payments</span>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Total Transactions"
            value="{{ $stats['total_transactions'] }}"
            icon="ri-arrow-left-right-line"
            color="primary"
            badgeText="All time records"
        />
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Successful"
            value="{{ $stats['success_count'] }}"
            icon="ri-checkbox-circle-line"
            color="success"
            badgeText="Completed payments"
        />
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Total Revenue"
            value="${{ number_format($stats['total_revenue'], 2) }}"
            icon="ri-money-dollar-circle-line"
            color="info"
            badgeText="Generated income"
        />
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <x-widgets.stat-card-style1
            title="Failed"
            value="{{ $stats['failed_count'] }}"
            icon="ri-close-circle-line"
            color="danger"
            badgeText="Unsuccessful attempts"
        />
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <x-tables.card title="All Transactions">
            <x-slot:tools>
                <div class="d-flex gap-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Statuses</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
            </x-slot:tools>

            <x-tables.table 
                id="transactionsTable"
                :headers="['Sr.#', 'User', 'Description', 'Amount', 'Gateway', 'Status', 'Date', 'Action']"
                :bordered="true"
            >
            </x-tables.table>
        </x-tables.card>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;

        $(function(){
            var table = $('#transactionsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.transactions.index') }}",
                    data: function (d) {
                        d.status = $('select[name="status"]').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id', orderable: false },
                    { data: 'user', name: 'user', orderable: false, searchable: false },
                    { data: 'description', name: 'description', orderable: false, searchable: false },
                    { data: 'amount', name: 'amount' },
                    { data: 'gateway', name: 'gateway', orderable: false, searchable: false },
                    { data: 'status', name: 'status' },
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

    function confirmRefund(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to refund this transaction? This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, refund it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('refund-form-' + id).submit();
            }
        });
    }
</script>
@endsection
