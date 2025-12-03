@extends('layouts.master')

@section('styles')

        <!-- Jsvector Maps -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/jsvectormap/jsvectormap.min.css')}}">

@endsection

@section('content')
	
                    <!-- Start::page-header -->
                    <div class="page-header-breadcrumb mb-3">
                        <div class="d-flex align-center justify-content-between flex-wrap">
                            <h1 class="page-title fw-medium fs-18 mb-0">Widgets</h1>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Widgets</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Widgets</li>
                            </ol>
                        </div>
                    </div>
                    <!-- End::page-header -->
                    
                    <!-- Start:: row-1 -->
                    <div class="row row-cols-xxl-5">
                        <div class="col">
                            <x-widgets.stat-card-style1
                                title="Total Employees"
                                value="1,754"
                                icon="ri-group-3-fill"
                                color="primary"
                                percentage="+1.04%"
                                percentageColor="text-success"
                            />
                        </div>
                        <div class="col">
                            <x-widgets.stat-card-style1
                                title="Employees In Leave"
                                value="234"
                                icon="ri-user-minus-fill"
                                color="secondary"
                                percentage="+0.36%"
                                percentageColor="text-success"
                            />
                        </div>
                        <div class="col">
                            <x-widgets.stat-card-style1
                                title="Total Clients"
                                value="664"
                                icon="ri-briefcase-fill"
                                color="warning"
                                percentage="-1.28%"
                                percentageColor="text-danger"
                            />
                        </div>
                        <div class="col">
                            <x-widgets.stat-card-style1
                                title="New Leads"
                                value="855"
                                icon="ri-id-card-fill"
                                color="success"
                                percentage="+2.25%"
                                percentageColor="text-success"
                            />
                        </div>
                        <div class="col">
                            <x-widgets.stat-card-style1
                                title="Total Deals"
                                value="325"
                                icon="ri-id-card-fill"
                                color="info"
                                percentage="-5.96%"
                                percentageColor="text-danger"
                            />
                        </div>
                    </div>
                    <!-- End:: row-1 -->

                    <!-- Start:: row-2 -->
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-3">
                            <x-widgets.stat-card-style2
                                title="Total Sales"
                                value="12,432"
                                color="primary"
                                chartId="chart-2"
                            >
                                <x-slot:icon>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M232,208a8,8,0,0,1-8,8H32a8,8,0,0,1,0-16h8V136a8,8,0,0,1,8-8H72a8,8,0,0,1,8,8v64H96V88a8,8,0,0,1,8-8h32a8,8,0,0,1,8,8V200h16V40a8,8,0,0,1,8-8h40a8,8,0,0,1,8,8V200h8A8,8,0,0,1,232,208Z"></path></svg>
                                </x-slot:icon>
                            </x-widgets.stat-card-style2>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-3">
                            <x-widgets.stat-card-style2
                                title="Total Revenue"
                                value="$9,432"
                                color="secondary"
                                chartId="chart-3"
                            >
                                <x-slot:icon>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M152,112a8,8,0,0,1-8,8H112a8,8,0,0,1,0-16h32A8,8,0,0,1,152,112Zm80-40V200a16,16,0,0,1-16,16H40a16,16,0,0,1-16-16V72A16,16,0,0,1,40,56H80V48a24,24,0,0,1,24-24h48a24,24,0,0,1,24,24v8h40A16,16,0,0,1,232,72ZM96,56h64V48a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8Zm120,57.61V72H40v41.61A184,184,0,0,0,128,136,184,184,0,0,0,216,113.61Z"></path></svg>
                                </x-slot:icon>
                            </x-widgets.stat-card-style2>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-3">
                            <x-widgets.stat-card-style2
                                title="Total Customers"
                                value="3,132"
                                color="success"
                                chartId="chart-4"
                            >
                                <x-slot:icon>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M164.47,195.63a8,8,0,0,1-6.7,12.37H10.23a8,8,0,0,1-6.7-12.37,95.83,95.83,0,0,1,47.22-37.71,60,60,0,1,1,66.5,0A95.83,95.83,0,0,1,164.47,195.63Zm87.91-.15a95.87,95.87,0,0,0-47.13-37.56A60,60,0,0,0,144.7,54.59a4,4,0,0,0-1.33,6A75.83,75.83,0,0,1,147,150.53a4,4,0,0,0,1.07,5.53,112.32,112.32,0,0,1,29.85,30.83,23.92,23.92,0,0,1,3.65,16.47,4,4,0,0,0,3.95,4.64h60.3a8,8,0,0,0,7.73-5.93A8.22,8.22,0,0,0,252.38,195.48Z"></path></svg>
                                </x-slot:icon>
                            </x-widgets.stat-card-style2>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-3">
                            <x-widgets.stat-card-style2
                                title="Total Profit"
                                value="$532"
                                color="info"
                                chartId="chart-5"
                            >
                                <x-slot:icon>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="svg-info" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm12,152h-4v8a8,8,0,0,1-16,0v-8H104a8,8,0,0,1,0-16h36a12,12,0,0,0,0-24H116a28,28,0,0,1,0-56h4V72a8,8,0,0,1,16,0v8h16a8,8,0,0,1,0,16H116a12,12,0,0,0,0,24h24a28,28,0,0,1,0,56Z"></path></svg>
                                </x-slot:icon>
                            </x-widgets.stat-card-style2>
                        </div>
                    </div>
                    <!-- End:: row-2 -->

                    <!-- Start:: row-3 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-body">
                                    <div class="row gy-4">
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
                                            <x-widgets.stat-card-style3
                                                title="Total Sales"
                                                value="$54,320"
                                                color="primary"
                                                percentage="+ 0.54%"
                                                percentageColor="text-success"
                                            >
                                                <x-slot:icon>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm16,160h-8v8a8,8,0,0,1-16,0v-8h-8a32,32,0,0,1-32-32,8,8,0,0,1,16,0,16,16,0,0,0,16,16h32a16,16,0,0,0,0-32H116a32,32,0,0,1,0-64h4V64a8,8,0,0,1,16,0v8h4a32,32,0,0,1,32,32,8,8,0,0,1-16,0,16,16,0,0,0-16-16H116a16,16,0,0,0,0,32h28a32,32,0,0,1,0,64Z"/></svg>
                                                </x-slot:icon>
                                            </x-widgets.stat-card-style3>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
                                            <x-widgets.stat-card-style3
                                                title="Total Products"
                                                value="1,320"
                                                color="secondary"
                                                percentage="- 3.96%"
                                                percentageColor="text-danger"
                                            >
                                                <x-slot:icon>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M223.68,66.15,135.68,18a15.88,15.88,0,0,0-15.36,0l-88,48.17a16,16,0,0,0-8.32,14v95.64a16,16,0,0,0,8.32,14l88,48.17a15.88,15.88,0,0,0,15.36,0l88-48.17a16,16,0,0,0,8.32-14V80.18A16,16,0,0,0,223.68,66.15ZM128,120,47.65,76,128,32l80.35,44Zm8,99.64V133.83l80-43.78v85.76Z"/></svg>
                                                </x-slot:icon>
                                            </x-widgets.stat-card-style3>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
                                            <x-widgets.stat-card-style3
                                                title="Pending Orders"
                                                value="240"
                                                color="warning"
                                                percentage="+ 5.53%"
                                                percentageColor="text-success"
                                            >
                                                <x-slot:icon>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm56,112H128a8,8,0,0,1-8-8V72a8,8,0,0,1,16,0v48h48a8,8,0,0,1,0,16Z"/></svg>
                                                </x-slot:icon>
                                            </x-widgets.stat-card-style3>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
                                            <x-widgets.stat-card-style3
                                                title="Total Revenue"
                                                value="$76,432"
                                                color="info"
                                                percentage="+ 1.25%"
                                                percentageColor="text-success"
                                            >
                                                <x-slot:icon>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm12,152h-4v8a8,8,0,0,1-16,0v-8H104a8,8,0,0,1,0-16h36a12,12,0,0,0,0-24H116a28,28,0,0,1,0-56h4V72a8,8,0,0,1,16,0v8h16a8,8,0,0,1,0,16H116a12,12,0,0,0,0,24h24a28,28,0,0,1,0,56Z"/></svg>
                                                </x-slot:icon>
                                            </x-widgets.stat-card-style3>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
                                            <x-widgets.stat-card-style3
                                                title="Inprogress Orders"
                                                value="1,120"
                                                color="success"
                                                percentage="+ 3.18%"
                                                percentageColor="text-success"
                                            >
                                                <x-slot:icon>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M208,80H176V56a48,48,0,0,0-96,0V80H48A16,16,0,0,0,32,96V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V96A16,16,0,0,0,208,80Zm-80,0H128V56a32,32,0,0,1,32-32,32,32,0,0,1,32,32V80H128Z"/></svg>
                                                </x-slot:icon>
                                            </x-widgets.stat-card-style3>
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
                                            <x-widgets.stat-card-style3
                                                title="Conversion Rate"
                                                value="12.5%"
                                                color="danger"
                                                percentage="- 0.65%"
                                                percentageColor="text-danger"
                                            >
                                                <x-slot:icon>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><polyline points="232 56 136 152 96 112 24 184" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><polyline points="232 120 232 56 168 56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>
                                                </x-slot:icon>
                                            </x-widgets.stat-card-style3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-3 -->

@endsection

@section('scripts')

        <!-- Apex Charts JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- Widgets JS -->
        <script src="{{asset('build/assets/widgets.js')}}"></script>

@endsection
