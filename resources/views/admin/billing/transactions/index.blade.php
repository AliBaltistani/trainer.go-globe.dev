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
                <form action="{{ route('admin.transactions.index') }}" method="GET" class="d-flex gap-2">
                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </form>
            </x-slot:tools>

            <x-tables.table 
                :headers="['ID', 'User', 'Description', 'Amount', 'Gateway', 'Status', 'Date', 'Action']"
                :bordered="true"
            >
                @forelse($transactions as $transaction)
                    <tr>
                        <td>#{{ $transaction->transaction_id ?? $transaction->id }}</td>
                        <td>
                            @php
                                $user = $transaction->invoice->client ?? $transaction->invoice->trainer ?? null;
                            @endphp
                            @if($user)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        @if($user->profile_image)
                                            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="img" class="rounded-circle">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-primary-transparent">{{ substr($user->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        <span class="text-muted fs-12">{{ ucfirst($user->role) }}</span>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Unknown User</span>
                            @endif
                        </td>
                        <td>
                            Payment for Invoice #{{ $transaction->invoice->invoice_number ?? 'N/A' }}
                        </td>
                        <td>
                            <span class="fw-semibold text-success">
                                {{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}
                            </span>
                        </td>
                        <td>
                            @if(optional($transaction->gateway)->type == 'stripe')
                                <div class="d-flex align-items-center">
                                    <i class="ri-visa-line text-primary fs-18 me-1"></i> Stripe
                                </div>
                            @elseif(optional($transaction->gateway)->type == 'paypal')
                                <div class="d-flex align-items-center">
                                    <i class="ri-paypal-line text-info fs-18 me-1"></i> PayPal
                                </div>
                            @else
                                {{ optional($transaction->gateway)->name ?? 'N/A' }}
                            @endif
                        </td>
                        <td>
                            @if($transaction->status == 'success')
                                <span class="badge bg-success-transparent">Success</span>
                            @elseif($transaction->status == 'pending')
                                <span class="badge bg-warning-transparent">Pending</span>
                            @elseif($transaction->status == 'failed')
                                <span class="badge bg-danger-transparent">Failed</span>
                            @else
                                <span class="badge bg-secondary-transparent">{{ ucfirst($transaction->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $transaction->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            <x-tables.actions>
                                <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-primary-light rounded-pill" data-bs-toggle="tooltip" title="View Details">
                                    <i class="ri-eye-line"></i>
                                </a>
                                @if($transaction->status == 'success')
                                    <button type="button" class="btn btn-sm btn-icon btn-danger-light rounded-pill" data-bs-toggle="tooltip" title="Refund" onclick="confirmRefund('{{ $transaction->id }}')">
                                        <i class="ri-refund-line"></i>
                                    </button>
                                    <form id="refund-form-{{ $transaction->id }}" action="{{ route('admin.transactions.refund', $transaction->id) }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                @endif
                            </x-tables.actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No transactions found</td>
                    </tr>
                @endforelse
            </x-tables.table>
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </x-tables.card>
    </div>
</div>
@endsection

@section('scripts')
<script>
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
