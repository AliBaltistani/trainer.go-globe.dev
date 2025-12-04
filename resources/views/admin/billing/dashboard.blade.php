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
            <x-widgets.stat-card-style1
                title="Total Revenue"
                value="{{ number_format($totals['revenue'] ?? 0, 2) }}"
                icon="ri-wallet-3-line"
                color="primary"
                badgeText="Sum of successful transactions"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Trainer Payouts"
                value="{{ number_format($totals['trainer_payouts'] ?? 0, 2) }}"
                icon="ri-exchange-dollar-line"
                color="success"
                badgeText="Total paid to trainers"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Pending Payouts"
                value="{{ number_format($totals['pending_payouts'] ?? 0, 2) }}"
                icon="ri-time-line"
                color="warning"
                badgeText="Awaiting completion"
            />
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <x-widgets.stat-card-style1
                title="Fees Collected"
                value="{{ number_format($totals['fees_collected'] ?? 0, 2) }}"
                icon="ri-percent-line"
                color="info"
                badgeText="Application fees"
            />
        </div>
    </div>

    <div class="row">
        <!-- Recent Activity -->
        <div class="col-xl-12">
            <x-tables.card title="Recent Activity">
                <x-slot:tools>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </x-slot:tools>
                <x-tables.table :headers="['ID', 'Type', 'Description', 'Amount', 'Status', 'Date']" :hover="true">
                    @forelse($recentActivity as $activity)
                        <tr>
                            <td>
                                <a href="#" class="fw-semibold text-primary">
                                    #{{ $activity['id'] }}
                                </a>
                            </td>
                            <td>
                                @if($activity['type'] == 'payment')
                                    <span class="badge bg-success-transparent">Payment</span>
                                @elseif($activity['type'] == 'refund')
                                    <span class="badge bg-info-transparent">Refund</span>
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
                </x-tables.table>
            </x-tables.card>
        </div>
    </div>
@endsection
