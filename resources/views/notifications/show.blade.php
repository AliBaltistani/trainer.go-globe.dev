@extends('layouts.master')

@section('content')
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Notification Details</h1>
            <div class="">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Notification Details</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="ms-auto pageheader-btn">
            <a href="javascript:history.back()" class="btn btn-secondary btn-wave waves-effect waves-light">
                <i class="ri-arrow-left-line align-middle me-1"></i> Back
            </a>
        </div>
    </div>
    <!-- Page Header Close -->

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        {{ $notification->title }}
                    </div>
                    <div class="d-flex">
                        <span class="badge bg-light text-dark border">
                            {{ $notification->created_at->format('d M Y, h:i A') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2">Message:</h6>
                        <p class="text-muted mb-0">{{ $notification->message }}</p>
                    </div>
                    
                    @if($notification->payload)
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-2">Additional Details:</h6>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0">{{ json_encode($notification->payload, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer d-none border-top-0">
                    <!-- Optional actions based on notification type could go here -->
                </div>
            </div>
        </div>
    </div>
@endsection
