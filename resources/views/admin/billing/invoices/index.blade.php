@extends('layouts.master')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Invoices</h1>
            <div>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Invoices</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="ms-auto pageheader-btn"></div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Total Invoices"
                value="{{ $stats['total_invoices'] }}"
                icon="ri-file-list-3-line"
                color="primary"
                badgeText="All invoices"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Paid Invoices"
                value="{{ $stats['paid_invoices'] }}"
                icon="ri-check-double-line"
                color="success"
                badgeText="Fully settled"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Total Collected"
                value="${{ number_format($stats['total_collected'], 2) }}"
                icon="ri-money-dollar-circle-line"
                color="info"
                badgeText="Revenue collected"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Pending Invoices"
                value="{{ $stats['pending_count'] }}"
                icon="ri-time-line"
                color="warning"
                badgeText="Awaiting payment"
            />
        </div>
    </div>

    <x-tables.card title="All Invoices">
        <x-slot:tools>
            <div class="d-flex">
                <select name="status" class="form-select form-select-sm me-2" style="max-width:180px">
                    <option value="">All Status</option>
                    @foreach(['draft','pending','paid','failed','cancelled'] as $st)
                        <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:tools>

        <x-tables.table 
            id="invoicesTable" 
            :headers="['Sr.#', 'Trainer', 'Client', 'Total', 'Currency', 'Due Date', 'Status', 'Created', 'Actions']"
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
                var table = $('#invoicesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('admin.invoices.index') }}",
                        data: function (d) {
                            d.status = $('select[name="status"]').val();
                        }
                    },
                    columns: [
                        { data: 'id', name: 'id', orderable: false },
                        { data: 'trainer', name: 'trainer' },
                        { data: 'client', name: 'client' },
                        { data: 'total', name: 'total_amount' },
                        { data: 'currency', name: 'currency' },
                        { data: 'due_date', name: 'due_date' },
                        { data: 'status', name: 'status' },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    order: [[0, 'desc']] // Default sort by ID desc
                });

                $('select[name="status"]').change(function(){
                    table.draw();
                });
            });
        });
    </script>
@endsection
