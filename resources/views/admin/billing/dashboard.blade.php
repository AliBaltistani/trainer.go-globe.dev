@extends('layouts.master')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Billing Dashboard</h1>
            <div>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Billing</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-primary-transparent me-2">
                            <i class="ri-wallet-3-line fs-20"></i>
                        </div>
                        <div class="flex-fill">
                            <h6 class="fw-semibold mb-1">Total Revenue</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($totals['revenue'] ?? 0, 2) }}</h3>
                            <p class="text-muted mb-0 fs-12">Sum of successful transactions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-success-transparent me-2">
                            <i class="ri-exchange-dollar-line fs-20"></i>
                        </div>
                        <div class="flex-fill">
                            <h6 class="fw-semibold mb-1">Trainer Payouts</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($totals['trainer_payouts'] ?? 0, 2) }}</h3>
                            <p class="text-muted mb-0 fs-12">Total paid to trainers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-warning-transparent me-2">
                            <i class="ri-time-line fs-20"></i>
                        </div>
                        <div class="flex-fill">
                            <h6 class="fw-semibold mb-1">Pending Payouts</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($totals['pending_payouts'] ?? 0, 2) }}</h3>
                            <p class="text-muted mb-0 fs-12">Awaiting completion</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-info-transparent me-2">
                            <i class="ri-percent-line fs-20"></i>
                        </div>
                        <div class="flex-fill">
                            <h6 class="fw-semibold mb-1">Fees Collected</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($totals['fees_collected'] ?? 0, 2) }}</h3>
                            <p class="text-muted mb-0 fs-12">Application fees</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Recent Transactions
                    </div>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-primary-light">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">User</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivity as $activity)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    @if(isset($activity['user']) && $activity['user']->profile_image)
                                                        <img src="{{ asset('storage/' . $activity['user']->profile_image) }}" alt="img" class="rounded-circle">
                                                    @else
                                                        <span class="avatar-initial rounded-circle bg-primary-transparent">{{ substr($activity['user']->name ?? 'U', 0, 1) }}</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $activity['user']->name ?? 'Unknown' }}</div>
                                                    <span class="text-muted fs-12">{{ ucfirst($activity['user']->role ?? '') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($activity['type'] == 'payment')
                                                <span class="badge bg-success-transparent">Incoming Payment</span>
                                            @else
                                                <span class="badge bg-warning-transparent">Trainer Payout</span>
                                            @endif
                                        </td>
                                        <td>{{ $activity['description'] }}</td>
                                        <td>
                                            <span class="fw-semibold {{ $activity['type'] == 'payment' ? 'text-success' : 'text-danger' }}">
                                                {{ $activity['type'] == 'payment' ? '+' : '-' }} {{ number_format($activity['amount'], 2) }} {{ $activity['currency'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($activity['status'] == 'success' || $activity['status'] == 'paid')
                                                <span class="badge bg-success-transparent">Completed</span>
                                            @elseif($activity['status'] == 'pending')
                                                <span class="badge bg-warning-transparent">Pending</span>
                                            @elseif($activity['status'] == 'failed')
                                                <span class="badge bg-danger-transparent">Failed</span>
                                            @else
                                                <span class="badge bg-secondary-transparent">{{ ucfirst($activity['status']) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($activity['date'])->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No recent activity found</td>
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
