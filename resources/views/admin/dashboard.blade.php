@extends('layouts.master')

@section('styles')

@endsection

@section('content')

<!-- Start::page-header -->
<div class="d-flex align-items-center justify-content-between mb-3 page-header-breadcrumb flex-wrap gap-2">
    <div>
        <h1 class="page-title fw-medium fs-20 mb-0">Admin Dashboard</h1>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </div>
</div>
<!-- End::page-header -->

<!-- Display Success Messages -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ri-check-circle-line me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Start:: row-1 -->
<div class="row">
    <div class="col-xxl-9">
        <div class="row">
            
            <!-- Stats Cards Row 1 -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card dashboard-main-card overflow-hidden primary">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-fill">
                                <span class="fs-13 fw-medium">Total Users</span>
                                <h4 class="fw-semibold my-2 lh-1">{{ $stats['total_users'] }}</h4>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-12 d-block text-muted">Registered users</span>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-md bg-primary-transparent svg-primary">
                                    <i class="ri-user-line fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card dashboard-main-card overflow-hidden success">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-fill">
                                <span class="fs-13 fw-medium">Total Trainers</span>
                                <h4 class="fw-semibold my-2 lh-1">{{ $stats['total_trainers'] }}</h4>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-12 d-block text-muted">Active trainers</span>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-md bg-success-transparent svg-success">
                                    <i class="ri-user-star-line fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card dashboard-main-card overflow-hidden info">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-fill">
                                <span class="fs-13 fw-medium">Total Clients</span>
                                <h4 class="fw-semibold my-2 lh-1">{{ $stats['total_clients'] }}</h4>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-12 d-block text-muted">Active clients</span>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-md bg-info-transparent svg-info">
                                    <i class="ri-user-heart-line fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card dashboard-main-card overflow-hidden warning">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-fill">
                                <span class="fs-13 fw-medium">Total Reviews</span>
                                <h4 class="fw-semibold my-2 lh-1">{{ $stats['total_testimonials'] }}</h4>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-12 d-block text-muted">Feedbacks received</span>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-md bg-warning-transparent svg-warning">
                                    <i class="ri-chat-3-line fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards Row 2 -->
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card dashboard-main-card overflow-hidden secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-fill">
                                <span class="fs-13 fw-medium">Total Goals</span>
                                <h4 class="fw-semibold my-2 lh-1">{{ $stats['total_goals'] }}</h4>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-12 d-block text-muted">Active goals</span>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-md bg-secondary-transparent svg-secondary">
                                    <i class="ri-flag-2-line fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card dashboard-main-card overflow-hidden danger">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-fill">
                                <span class="fs-13 fw-medium">Total Workouts</span>
                                <h4 class="fw-semibold my-2 lh-1">{{ $stats['total_workouts'] }}</h4>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-12 d-block text-muted">Workouts created</span>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-md bg-danger-transparent svg-danger">
                                    <i class="ri-run-line fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card dashboard-main-card overflow-hidden primary">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-fill">
                                <span class="fs-13 fw-medium">Total Admins</span>
                                <h4 class="fw-semibold my-2 lh-1">{{ $stats['total_admins'] }}</h4>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-12 d-block text-muted">System admins</span>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-md bg-primary-transparent svg-primary">
                                    <i class="ri-admin-line fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="col-xxl-3">
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card shadow-none card-bg-primary overflow-hidden dashboard-banner-card">
                    <div class="card-body">
                        <div class="dashboard-banner-card-background">
                            <img src="{{asset('build/assets/images/media/backgrounds/8.png')}}" alt="">
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <h5 class="fw-semibold mb-1 text-fixed-white">Welcome Back! &#128075;</h5>
                                <span class="d-block fs-14 mb-3 pe-5 text-fixed-white">Manage your fitness platform efficiently.</span>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-wave mt-1">Manage Users <i class="ti ti-arrow-narrow-right align-middle"></i></a>
                            </div>
                            <div class="dashboard-banner-image d-sm-block d-none">
                                <!-- SVG Placeholder to maintain layout -->
                                <svg xmlns="http://www.w3.org/2000/svg" data-name="Layer 1" width="150" height="150" viewBox="0 0 748.82965 557.20035" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><path d="M276.60133,642.057c-3.59968,0-7.20676.1709-10.72934.51563l-.51267.0498c-23.36542,2.37793-40.45611,16.17286-39.75323,32.08985l.13588,2.87793c.08961,1.81933.18508,3.77539.26367,5.94531.04255,1.07031,1.31106,1.8916,2.88946,1.8916H803.62751c40.52148-.2832,80.89606-.63965,119.9956-1.05957a177.65216,177.65216,0,0,0,21.95374-1.30957c10.86591-1.48047,18.40759-4.415,23.05841-8.97168h.00147c5.78332-5.65039,5.96551-12.76562,5.6159-20.80469-.71246-16.23242-1.419-33.28515-2.12414-50.28613-.62281-14.9834-1.24274-29.92676-1.86261-44.2334-.38343-8.49023-1.23536-15.98437-7.80317-21.71191-7.11865-6.19434-20.10137-9.334-38.58831-9.334-.22333,0-.44507,0-.67133.001-57.9361.23731-115.34192,23.1709-142.8454,57.06732-3.16711,3.90332-6.11822,8.082-8.97247,12.124a158.91584,158.91584,0,0,1-13.7804,17.66406,66.94863,66.94863,0,0,1-9.10321,8.16894c-.22326.17774-.47888.36817-.7492.54883-.61847.46387-1.26477.91114-1.895,1.30957a62.04148,62.04148,0,0,1-11.60926,6.07617l-.40692.15723c-.30627.126-.6383.252-.97027.36719a68.20886,68.20886,0,0,1-7.17,2.20605c-10.484,2.66016-22.40545,3.29785-35.43151,1.89356a151.75182,151.75182,0,0,1-35.2295-8.26465c-12.06244-4.38867-23.3529-9.86035-34.27173-15.15137-4.79614-2.32519-9.75683-4.72851-14.711-7.00586-.93134-.42871-1.84723-.84765-2.7771-1.2666-6.05066-2.71484-11.5365-4.957-16.77045-6.85449a146.28118,146.28118,0,0,0-39.96478-8.52149c-14.40918-.9082-27.10688.80469-37.73191,5.07911a60.6496,60.6496,0,0,0-8.53469,4.23339c-12.58466,7.52344-20.343,18.627-27.845,29.36524-9.33233,13.35644-18.98275,27.16894-38.0382,34.3291-21.98825,8.26953-54.08371,4.834-76.791-1.14062-7.13919-1.87891-14.26956-4.04-21.16565-6.13086q-4.17768-1.26719-8.36136-2.51661c-5.48071-1.6289-9.96621-2.877-14.116-3.93066l-1.31253-.33594c-.73008-.18261-1.46088-.36523"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            Quick Actions
                        </div>  
                    </div>
                    <div class="card-body my-2">    
                        <div class="d-grid gap-2">
                            <a href="{{ route('goals.index') }}" class="btn btn-outline-success btn-wave text-start">
                                <i class="ri-target-line me-2"></i>Manage Goals
                            </a>
                            <a href="{{ route('workouts.index') }}" class="btn btn-outline-info btn-wave text-start">
                                <i class="ri-run-line me-2"></i>Manage Workouts
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-wave text-start">
                                <i class="ri-user-settings-line me-2"></i>Manage Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End:: row-1 -->

<!-- Start:: row-2 -->
<div class="row">
    <div class="col-xxl-6 col-md-12 boxed-col-6">
        <div class="card custom-card overflow-hidden">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    Recent Users ({{ $stats['recent_users']->count() }})
                </div>
                <a href="{{ route('admin.users.index') }}" class="text-muted fs-12 text-decoration-underline">View All<i class="ti ti-arrow-narrow-right"></i></a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($stats['recent_users'] as $user)
                    <li class="list-group-item">
                        <div class="d-flex align-items-center gap-3">
                            <div class="lh-1">
                                <span class="avatar avatar-lg bg-light border border-dashed p-1">
                                    @if($user->profile_image)
                                        <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}">
                                    @else
                                        <div class="header-link-icon avatar bg-primary-transparent avatar-rounded w-100 h-100 d-flex align-items-center justify-content-center">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </span>
                            </div>
                            <div class="flex-fill">
                                <span class="fw-semibold mb-1 d-block">{{ $user->name }}</span>
                                <div class="d-flex align-items-center gap-2 fw-medium">
                                    <div class="fs-12 text-muted">{{ ucfirst($user->role) }}</div>
                                    <div class="vr"></div>
                                    <span class="text-success fs-12"><i class="ri-circle-fill me-1 fs-7 align-middle"></i>{{ $user->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $user->role === 'admin' ? 'dark' : ($user->role === 'trainer' ? 'success' : 'info') }}-transparent">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center py-4">
                        <p class="text-muted mb-0">No recent users found.</p>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-xxl-6 col-md-12 boxed-col-6">
        <div class="card custom-card recent-activity-card">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    Recent Testimonials ({{ $stats['recent_testimonials']->count() }})
                </div>
            </div>
            <div class="card-body px-sm-5">
                <ul class="list-unstyled recent-activity-list">
                    @forelse($stats['recent_testimonials'] as $testimonial)
                    <li>
                        <div class="recent-activity-time text-end">
                            <span class="fw-semibold d-block">{{ $testimonial->created_at ? $testimonial->created_at->format('d, M') : 'N/A' }}</span>
                            <span class="d-block text-muted fs-12">{{ $testimonial->created_at ? $testimonial->created_at->format('h:i A') : '' }}</span>
                        </div>
                        <div>
                            <span class="d-block fs-13 mt-1">
                                <span class="fw-medium text-default">{{ isset($testimonial->trainer) ? $testimonial->trainer->name : 'Unknown' }}</span>
                                received a
                                <span class="fw-medium text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $testimonial->rate) <i class="ri-star-fill"></i> @else <i class="ri-star-line"></i> @endif
                                    @endfor
                                </span>
                                review.
                            </span>
                            <p class="text-muted fs-13 mb-0 mt-1">"{{ Str::limit($testimonial->comments, 100) }}"</p>
                        </div>
                    </li>
                    @empty
                    <li class="text-center py-4">
                        <p class="text-muted mb-0">No recent testimonials found.</p>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- End:: row-2 -->

@endsection

@section('scripts')

@endsection
