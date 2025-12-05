@extends('layouts.master')

@section('title', 'Invoice Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h4 class="mb-0">Invoice #{{ $invoice->id }}</h4>
            <p class="mb-0 text-muted">View invoice details and history.</p>
        </div>
        <div class="main-dashboard-header-right">
            <div class="d-flex my-xl-auto right-content align-items-center">
                <div class="pe-1 mb-xl-0">
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-danger">
                        <i class="ri-arrow-left-line me-1 align-middle"></i> Back to Invoices
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="row">
        <div class="col-xl-9">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Invoice Details
                    </div>
                    <div class="d-flex">
                        <span class="badge bg-{{ match($invoice->status) {
                            'paid' => 'success-transparent',
                            'pending' => 'warning-transparent',
                            'failed' => 'danger-transparent',
                            'cancelled' => 'secondary-transparent',
                            default => 'info-transparent'
                        } }} fs-12">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h6 class="fw-bold mb-3">From:</h6>
                            <div>
                                <p class="fw-bold mb-1">{{ $invoice->trainer->name ?? 'N/A' }}</p>
                                <p class="mb-1 text-muted">{{ $invoice->trainer->email ?? '' }}</p>
                                <p class="mb-0 text-muted">Trainer</p>
                            </div>
                        </div>
                        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                            <h6 class="fw-bold mb-3">To:</h6>
                            <div>
                                <p class="fw-bold mb-1">{{ $invoice->client->name ?? 'N/A' }}</p>
                                <p class="mb-1 text-muted">{{ $invoice->client->email ?? '' }}</p>
                                <p class="mb-0 text-muted">Client</p>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->title }}</div>
                                            @if($item->workout)
                                                <small class="text-muted">Workout: {{ $item->workout->title }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->qty }}</td>
                                        <td class="text-end">{{ number_format($item->amount, 2) }} {{ strtoupper($invoice->currency) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No items found</td>
                                    </tr>
                                @endforelse
                                <tr>
                                    <td colspan="2" class="fw-bold text-end">Total</td>
                                    <td class="fw-bold text-end fs-16">{{ number_format($invoice->total_amount, 2) }} {{ strtoupper($invoice->currency) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($invoice->notes)
                        <div class="mt-4">
                            <h6 class="fw-bold">Notes:</h6>
                            <p class="text-muted">{{ $invoice->notes }}</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="ri-printer-line me-1 align-middle"></i> Print Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Invoice Info
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="fw-semibold text-muted">Invoice ID:</span>
                        <span class="d-block fs-14">#{{ $invoice->id }}</span>
                    </div>
                    <div class="mb-3">
                        <span class="fw-semibold text-muted">Date Created:</span>
                        <span class="d-block fs-14">{{ $invoice->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="mb-3">
                        <span class="fw-semibold text-muted">Due Date:</span>
                        <span class="d-block fs-14">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    @if($invoice->transactions->isNotEmpty())
                        <div class="mb-0">
                            <span class="fw-semibold text-muted">Payment Status:</span>
                            <div class="mt-2">
                                @foreach($invoice->transactions as $transaction)
                                    <div class="mb-2 p-2 border rounded bg-light">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-success-transparent">Paid</span>
                                            <small class="text-muted">{{ $transaction->created_at->format('M d') }}</small>
                                        </div>
                                        <div class="fs-12 fw-semibold">{{ $transaction->transaction_id }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
