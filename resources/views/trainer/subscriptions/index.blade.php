@extends('layouts.master')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Client Subscriptions</h1>
            <div>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Subscriptions</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="ms-auto pageheader-btn">
            <form method="GET" action="{{ route('trainer.subscriptions.index') }}" class="d-flex gap-2">
                <select name="status" class="form-select" style="width:auto">
                    <option value="" {{ $status==='' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ $status==='active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status==='inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header"><div class="card-title">Subscriptions</div></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Subscribed At</th>
                            <th>Unsubscribed At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $sub)
                            <tr>
                                <td>{{ optional($sub->client)->name }}</td>
                                <td>{{ optional($sub->client)->email }}</td>
                                <td class="text-capitalize">{{ $sub->status }}</td>
                                <td>{{ $sub->subscribed_at ? $sub->subscribed_at->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $sub->unsubscribed_at ? $sub->unsubscribed_at->format('d/m/Y H:i') : '—' }}</td>
                                <td>
                                    @if($sub->status === 'active')
                                        <form method="POST" action="{{ route('trainer.subscriptions.destroy', $sub->id) }}" onsubmit="return confirm('Remove this subscription?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                        </form>
                                    @else
                                        <span class="text-muted">Removed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No subscriptions found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $subscriptions->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
