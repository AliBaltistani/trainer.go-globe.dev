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
            <form method="GET" class="d-flex">
                <select name="status" class="form-select form-select-sm me-2" style="max-width:180px">
                    <option value="">All Status</option>
                    @foreach(['processing','completed','failed'] as $st)
                        <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary">Filter</button>
            </form>
        </x-slot:tools>

        <x-tables.table 
            id="payoutsTable" 
            :headers="['ID', 'Trainer', 'Amount', 'Currency', 'Fee', 'Status', 'Scheduled', 'Created', 'Action']"
        >
            @forelse($payouts as $payout)
                <tr>
                    <td>{{ $payout->id }}</td>
                    <td class="fw-semibold">{{ $payout->trainer->name ?? '#' }}</td>
                    <td>{{ number_format($payout->amount,2) }}</td>
                    <td>{{ strtoupper($payout->currency) }}</td>
                    <td>{{ number_format($payout->fee_amount,2) }}</td>
                    <td>
                        @if($payout->payout_status==='processing')
                            <span class="badge bg-warning-transparent">Processing</span>
                        @elseif($payout->payout_status==='completed')
                            <span class="badge bg-success-transparent">Completed</span>
                        @else
                            <span class="badge bg-danger-transparent">Failed</span>
                        @endif
                    </td>
                    <td>{{ $payout->scheduled_at ? $payout->scheduled_at->format('M d, Y H:i') : '—' }}</td>
                    <td>{{ $payout->created_at->format('M d, Y') }}</td>
                    <td>
                        @if($payout->payout_status !== 'completed')
                            <button type="button" class="btn btn-sm btn-primary-light" title="Process Payout" onclick="confirmPayout('{{ $payout->id }}')">
                                <i class="ri-bank-card-line me-1"></i> Pay
                            </button>
                            <form id="payout-form-{{ $payout->id }}" action="{{ route('admin.payouts.process', $payout->id) }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        @else
                            <span class="badge bg-success-transparent"><i class="ri-check-double-line me-1"></i> Paid</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="ri-exchange-dollar-line fs-1 text-muted mb-2"></i>
                            <h6 class="fw-semibold mb-1">No Payouts Found</h6>
                            <p class="text-muted mb-0">No records match your filter.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-tables.table>

        @if($payouts->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <p class="text-muted mb-0">Showing {{ $payouts->firstItem() }} to {{ $payouts->lastItem() }} of {{ $payouts->total() }} results</p>
                {{ $payouts->links() }}
            </div>
        @endif
    </x-tables.card>
@endsection

@section('scripts')
    <script>
        $(function(){
            @if($payouts->count() > 0)
            $('#payoutsTable').DataTable({responsive:true, ordering:false, paging:false, searching:false, info:false});
            @endif
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
