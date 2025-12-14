@php
    $user = Auth::user();
@endphp
<header class="app-header sticky" id="header">

    <!-- Start::main-header-container -->
    <div class="main-header-container container-fluid">

        <!-- Start::header-content-left -->
        <div class="header-content-left">

            <!-- Start::header-element -->
            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="{{url('index')}}" class="header-logo">
                        <img src="{{asset('build/assets/images/brand-logos/desktop-logo.png')}}" alt="logo" class="desktop-logo">
                        <img src="{{asset('build/assets/images/brand-logos/toggle-logo.png')}}" alt="logo" class="toggle-logo">
                        <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark">
                        <img src="{{asset('build/assets/images/brand-logos/toggle-dark.png')}}" alt="logo" class="toggle-dark">
                    </a>
                </div>
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element mx-lg-0 mx-2">
                <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle" data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
            </div>
            <!-- End::header-element -->

            <div class="header-element header-search d-md-block d-none">
                <!-- Start::header-link -->
                <input type="text" class="header-search-bar form-control bg-white" id="header-search" placeholder="Search" spellcheck=false autocomplete="off" autocapitalize="off">
                <a href="javascript:void(0);" class="header-search-icon border-0">
                    <i class="bi bi-search fs-12"></i>
                </a>
                <!-- End::header-link -->
            </div>

        </div>
        <!-- End::header-content-left -->

        <!-- Start::header-content-right -->
        <ul class="header-content-right">

            <!-- Start::header-element -->
            <li class="header-element d-md-none d-block">
                <a href="javascript:void(0);" class="header-link" data-bs-toggle="modal" data-bs-target="#header-responsive-search">
                    <!-- Start::header-link-icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><circle cx="112" cy="112" r="80" opacity="0.2"/><circle cx="112" cy="112" r="80" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="168.57" y1="168.57" x2="224" y2="224" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>
                    <!-- End::header-link-icon -->
                </a>  
            </li>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <li class="header-element country-selector dropdown d-sm-block d-none">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside" data-bs-toggle="dropdown">
                    <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M215,168.71a96.42,96.42,0,0,1-30.54,37l-9.36-9.37a8,8,0,0,0-3.63-2.09L150,188.59a8,8,0,0,1-5.88-8.9l2.38-16.2a8,8,0,0,1,4.85-6.22l30.45-12.66a8,8,0,0,1,8.47,1.49Z" opacity="0.2"/><path d="M184,74a8,8,0,0,1-1.94,5.22L159.89,105a8,8,0,0,1-5,2.71l-31.46,4.26a8.06,8.06,0,0,1-5.77-1.45l-19.81-13a8,8,0,0,0-11.34,2l-20.94,31.3a8.06,8.06,0,0,0-1.35,4.41L64,171.49a8,8,0,0,1-3.61,6.64l-9.92,6.52A96,96,0,0,1,184,50Z" opacity="0.2"/><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><path d="M184.42,205.68l-9.36-9.37a8,8,0,0,0-3.63-2.09L150,188.59a8,8,0,0,1-5.88-8.9l2.38-16.2a8,8,0,0,1,4.85-6.22l30.45-12.66a8,8,0,0,1,8.47,1.49L215,168.71" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><path d="M50.49,184.65l9.92-6.52A8,8,0,0,0,64,171.49l.21-36.23a8.06,8.06,0,0,1,1.35-4.41l20.94-31.3a8,8,0,0,1,11.34-2l19.81,13a8.06,8.06,0,0,0,5.77,1.45l31.46-4.26a8,8,0,0,0,5-2.71L182.06,79.2A8,8,0,0,0,184,74V50" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <ul class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);">
                            <span class="avatar avatar-rounded avatar-xs lh-1 me-2">
                                <img src="{{asset('build/assets/images/flags/us_flag.jpg')}}" alt="img">
                            </span>
                            English
                        </a>
                    </li>
                </ul>
            </li>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <li class="header-element header-theme-mode">
                <!-- Start::header-link|layout-setting -->
                <a href="javascript:void(0);" class="header-link layout-setting">
                    <span class="light-layout">
                        <!-- Start::header-link-icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M108.11,28.11A96.09,96.09,0,0,0,227.89,147.89,96,96,0,1,1,108.11,28.11Z" opacity="0.2"/><path d="M108.11,28.11A96.09,96.09,0,0,0,227.89,147.89,96,96,0,1,1,108.11,28.11Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>
                        <!-- End::header-link-icon -->
                    </span>
                    <span class="dark-layout">
                        <!-- Start::header-link-icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><circle cx="128" cy="128" r="56" opacity="0.2"/><line x1="128" y1="40" x2="128" y2="32" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><circle cx="128" cy="128" r="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="64" y1="64" x2="56" y2="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="64" y1="192" x2="56" y2="200" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="192" y1="64" x2="200" y2="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="192" y1="192" x2="200" y2="200" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="40" y1="128" x2="32" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="128" y1="216" x2="128" y2="224" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="216" y1="128" x2="224" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>
                        <!-- End::header-link-icon -->
                    </span>
                </a>
                <!-- End::header-link|layout-setting -->
            </li>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <li class="header-element notifications-dropdown d-xl-block d-none dropdown">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="messageDropdown" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M56,104a72,72,0,0,1,144,0c0,35.82,8.3,64.6,14.9,76A8,8,0,0,1,208,192H48a8,8,0,0,1-6.88-12C47.71,168.6,56,139.81,56,104Z" opacity="0.2"/><path d="M96,192a32,32,0,0,0,64,0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><path d="M56,104a72,72,0,0,1,144,0c0,35.82,8.3,64.6,14.9,76A8,8,0,0,1,208,192H48a8,8,0,0,1-6.88-12C47.71,168.6,56,139.81,56,104Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>
                    @php
                        $unreadCount = Auth::check() ? Auth::user()->notifications()->where('status', '!=', 'read')->count() : 0;
                        $notifications = Auth::check() ? Auth::user()->notifications()->where('status', '!=', 'read')->latest()->take(10)->get() : collect();
                    @endphp
                    @if($unreadCount > 0)
                    <span class="header-icon-pulse bg-secondary rounded pulse pulse-secondary"></span>
                    @endif
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <!-- Start::main-header-dropdown -->
                <div class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                    <div class="p-3 bg-primary text-fixed-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0 fs-16">Notifications @if($unreadCount > 0) <span class="badge bg-secondary-transparent text-fixed-white ms-2">{{ $unreadCount }}</span> @endif</p>
                            <a href="{{ route('notifications.clear') }}" class="badge bg-light text-default border" onclick="event.preventDefault(); document.getElementById('clear-notifications-form').submit();">Clear All</a>
                            <form id="clear-notifications-form" action="{{ route('notifications.clear') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <ul class="list-unstyled mb-0" id="header-notification-scroll">
                        @include('layouts.components.notification-items')
                    </ul>
                    <div class="p-5 empty-item1 @if($notifications->isNotEmpty()) d-none @endif">
                        <div class="text-center">
                            <span class="avatar avatar-xl avatar-rounded bg-secondary-transparent">
                                <i class="ri-notification-off-line fs-2"></i>
                            </span>
                            <h6 class="fw-medium mt-3">No New Notifications</h6>
                        </div>
                    </div>
                </div>
                <!-- End::main-header-dropdown -->
            </li>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <li class="header-element header-fullscreen">
                <!-- Start::header-link -->
                <a onclick="openFullscreen();" href="javascript:void(0);" class="header-link">
                    <svg xmlns="http://www.w3.org/2000/svg" class="full-screen-open header-link-icon" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><rect x="48" y="48" width="160" height="160" opacity="0.2"/><polyline points="168 48 208 48 208 88" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><polyline points="88 208 48 208 48 168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><polyline points="208 168 208 208 168 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><polyline points="48 88 48 48 88 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="full-screen-close header-link-icon d-none" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><rect x="32" y="32" width="192" height="192" rx="16" opacity="0.2"/><polyline points="160 48 208 48 208 96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="144" y1="112" x2="208" y2="48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><polyline points="96 208 48 208 48 160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="112" y1="144" x2="48" y2="208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>
                </a>
                <!-- End::header-link -->
            </li>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <li class="header-element dropdown">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    @if($user->profile_image)
                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="profile-img" class="rounded-circle header-link-icon">
                    @else
                    <div class="header-link-icon avatar bg-primary-transparent avatar-rounded">
                         {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    @endif
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <div class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end" aria-labelledby="mainHeaderProfile">
                    <div class="p-3 bg-primary text-fixed-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0 fs-16">Profile</p>
                            <a href="javascript:void(0);" class="text-fixed-white"><i class="ti ti-settings-cog"></i></a>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="p-3">
                        <div class="d-flex align-items-start gap-2">
                            <div class="lh-1">
                                 @if($user->profile_image)
                                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="profile-img" class="rounded-circle header-link-icon">
                                @else
                                    <span class="avatar avatar-sm bg-primary-transparent avatar-rounded">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                @endif
                                
                            </div>
                            <div>
                                <span class="d-block fw-semibold lh-1">{{ $user->name }}</span>
                                <span class="d-block text-muted fs-12">{{ $user->email }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <ul class="list-unstyled mb-0">
                        <li>
                            <ul class="list-unstyled mb-0 sub-list">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.index') }}"><i class="ti ti-user-circle me-2 fs-18"></i>View Profile</a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.edit') }}"><i class="ti ti-settings-cog me-2 fs-18"></i>Account Settings</a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.change-password') }}"><i class="ti ti-lock me-2 fs-18"></i>Change Password</a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.settings') }}"><i class="ti ti-settings me-2 fs-18"></i>Settings</a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.activity-log') }}"><i class="ti ti-activity me-2 fs-18"></i>Activity Log</a>
                                </li>
                            </ul>        
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center" 
                                        onclick="return confirm('Are you sure you want to logout?')">
                                    <i class="ti ti-logout me-2 fs-18"></i>
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </li>  
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <li class="header-element">
                <!-- Start::header-link|switcher-icon -->
                <!-- <a href="javascript:void(0);" class="header-link switcher-icon" data-bs-toggle="offcanvas" data-bs-target="#switcher-canvas">
                   <i class="ri-settings-3-line header-link-icon"></i>
                </a> -->
                <!-- End::header-link|switcher-icon -->
            </li>
            <!-- End::header-element -->

        </ul>
        <!-- End::header-content-right -->

    </div>
    <!-- End::main-header-container -->

</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function fetchNotifications() {
            fetch('{{ route("notifications.latest") }}')
                .then(response => response.json())
                .then(data => {
                    // Update Badge
                    const badgeContainer = document.querySelector('.notifications-dropdown .header-link');
                    let badge = badgeContainer.querySelector('.header-icon-pulse');
                    
                    if (data.count > 0) {
                        if (!badge) {
                            const span = document.createElement('span');
                            span.className = 'header-icon-pulse bg-secondary rounded pulse pulse-secondary';
                            badgeContainer.appendChild(span);
                        }
                    } else {
                        if (badge) badge.remove();
                    }

                    // Update Count in Dropdown Header
                    const dropdownMenu = document.querySelector('.notifications-dropdown .main-header-dropdown');
                    if (dropdownMenu) {
                        const headerText = dropdownMenu.querySelector('.p-3 p');
                        if (headerText) {
                            headerText.innerHTML = `Notifications ${data.count > 0 ? `<span class="badge bg-secondary-transparent text-fixed-white ms-2">${data.count}</span>` : ''}`;
                        }
                        
                        // Update List
                        const listContainer = document.getElementById('header-notification-scroll');
                        if (listContainer) {
                            listContainer.innerHTML = data.html;
                        }
                        
                        // Update Empty State
                        const emptyState = dropdownMenu.querySelector('.empty-item1');
                        if (emptyState) {
                            if (data.count > 0 || (listContainer && listContainer.children.length > 0)) {
                                emptyState.classList.add('d-none');
                            } else {
                                emptyState.classList.remove('d-none');
                            }
                        }
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }

        // Poll every 10 seconds
        setInterval(fetchNotifications, 10000);
    });
</script>
