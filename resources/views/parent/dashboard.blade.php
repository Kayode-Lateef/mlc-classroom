@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')

@endpush

@section('content')
        <div class="main">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-8 p-r-0 title-margin-right">
                        <div class="page-header">
                            <div class="page-title">
                                <h1>Hello, <span>Welcome Here Parent</span></h1>
                            </div>
                        </div>
                    </div>
                    <!-- /# column -->
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="#">Dashboard</a></li>
                                    <li class="active">Home</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <!-- /# column -->
                </div>
                <!-- /# row -->
                <div id="main-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="media">
                                    <div class="media-left meida media-middle">
                                        <span><i class="ti-bag f-s-22 color-primary border-primary round-widget"></i></span>
                                    </div>
                                    <div class="media-body media-text-right">
                                        <h4>128</h4>
                                        <h6>Total Students</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="media">
                                    <div class="media-left meida media-middle">
                                        <span><i class="ti-bar-chart f-s-22 color-warning border-warning round-widget"></i></span>
                                    </div>
                                    <div class="media-body media-text-right">
                                        <h4>10</h4>
                                        <h6>Total Teachers</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="media">
                                    <div class="media-left meida media-middle">
                                        <span><i class="ti-comment f-s-22 color-success border-success round-widget"></i></span>
                                    </div>
                                    <div class="media-body media-text-right">
                                        <h4>48</h4>
                                        <h6>Total Classes</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="media">
                                    <div class="media-left meida media-middle">
                                        <span><i class="ti-location-pin f-s-22 border-danger color-danger round-widget"></i></span>
                                    </div>
                                    <div class="media-body media-text-right">
                                        <h4>50 present / 100 total</h4>
                                        <h6>Attendance Rate</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
@endsection

@push('scripts')
   
@endpush