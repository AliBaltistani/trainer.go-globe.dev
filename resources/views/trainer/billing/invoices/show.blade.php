@extends('layouts.master')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Invoice #{{ $invoice->id }}</h1>
            <div>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('trainer.billing.invoices.index') }}">Invoices</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="ms-auto pageheader-btn">
            <a href="{{ route('trainer.billing.invoices.edit', $invoice->id) }}" class="btn btn-warning">Edit</a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Details</div></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-md-4">Trainer</dt>
                        <dd class="col-md-8">{{ optional($invoice->trainer)->name }}</dd>
                        <dt class="col-md-4">Client</dt>
                        <dd class="col-md-8">{{ optional($invoice->client)->name }}</dd>
                        <dt class="col-md-4">Status</dt>
                        <dd class="col-md-8 text-capitalize">{{ $invoice->status }}</dd>
                        <dt class="col-md-4">Currency</dt>
                        <dd class="col-md-8 text-uppercase">{{ $invoice->currency }}</dd>
                        <dt class="col-md-4">Due Date</dt>
                        <dd class="col-md-8">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}</dd>
                        <dt class="col-md-4">Created</dt>
                        <dd class="col-md-8">{{ $invoice->created_at->format('M d, Y') }}</dd>
                    </dl>
                    <div class="mt-3">
                        <label class="form-label">Notes</label>
                        <div class="border rounded p-2">{{ $invoice->notes ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Totals</div></div>
                <div class="card-body">
                    <h4 class="fw-bold">{{ number_format($invoice->total_amount, 2) }} <span class="text-uppercase">{{ $invoice->currency }}</span></h4>
                    <p class="text-muted mb-0">Sum of items</p>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header"><div class="card-title">Items</div></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Amount</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $it)
                                    <tr>
                                        <td>{{ $it->title }}</td>
                                        <td>{{ number_format($it->amount, 2) }}</td>
                                        <td>{{ $it->qty }}</td>
                                        <td>{{ number_format($it->amount * $it->qty, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No items</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
