@extends('layouts.master')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Billing Dashboard</h1>
            <div>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Billing</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4">
            <x-widgets.stat-card-style1
                title="Total Revenue"
                value="{{ number_format($totals['revenue'] ?? 0, 2) }}"
                icon="ri-money-dollar-circle-line"
                color="primary"
                badgeText="Sum of paid invoices"
            />
        </div>
        <div class="col-xl-4">
            <x-widgets.stat-card-style1
                title="Pending Payouts"
                value="{{ number_format($totals['pending_payouts'] ?? 0, 2) }}"
                icon="ri-time-line"
                color="warning"
                badgeText="Awaiting completion"
            />
        </div>
        <div class="col-xl-4">
            <x-widgets.stat-card-style1
                title="Fees Collected"
                value="{{ number_format($totals['fees_collected'] ?? 0, 2) }}"
                icon="ri-hand-coin-line"
                color="info"
                badgeText="Application fees (Stripe)"
            />
        </div>
    </div>
@endsection
