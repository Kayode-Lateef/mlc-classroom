@extends('layouts.app')

@section('title', 'User Management')

@push('styles')

@endpush

@section('content')

    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-8 p-r-0 title-margin-right">
                        <div class="page-header">
                            <div class="page-title">
                                <h1>Dashboard</h1>
                            </div>
                        </div>
                    </div>
                    <!-- /# column -->
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="#">Dashboard</a></li>
                                    <li class="active">User Management</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <!-- /# column -->
                </div>
                <!-- /# row -->
                <div id="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-header pr">
                                    <h4>All Students </h4>
                                    <div class="search-action">
                                        <div class="search-type dib">
                                            <input class="form-control input-rounded" placeholder="#Roll type here..." type="text">
                                        </div>
                                        <div class="search-type dib">
                                            <input class="form-control input-rounded" placeholder="Type section..." type="text">
                                        </div>
                                        <div class="search-type dib">
                                            <input class="form-control input-rounded" placeholder="search" type="text">
                                        </div>
                                    </div>
                                    <div class="card-header-right-icon">
                                        <ul>
                                            <li class="card-close" data-dismiss="alert"><i class="ti-close"></i></li>
                                            <li class="card-option drop-menu"><i class="ti-settings" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" role="link"></i>
                                                <ul class="card-option-dropdown dropdown-menu">
                                                    <li><a href="#"><i class="ti-loop"></i> Update data</a></li>
                                                    <li><a href="#"><i class="ti-menu-alt"></i> Detail log</a></li>
                                                    <li><a href="#"><i class="ti-pulse"></i> Statistics</a></li>
                                                    <li><a href="#"><i class="ti-power-off"></i> Clear ist</a></li>
                                                </ul>
                                            </li>
                                            <li class="doc-link"><a href="#"><i class="ti-link"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table student-data-table m-t-20">
                                            <thead>
                                                <tr>
                                                    <th><label><input type="checkbox" value=""></label>Roll</th>
                                                    <th>Photo</th>
                                                    <th>Name</th>
                                                    <th>Gender</th>
                                                    <th>Parents Name</th>
                                                    <th>Class</th>
                                                    <th>Section</th>
                                                    <th>Address</th>
                                                    <th>Date Of Birth</th>
                                                    <th>Mobile No</th>
                                                    <th>E-mail</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2901</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/1.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Daniel Will
                                                    </td>
                                                    <td>
                                                        Male</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                        Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2902</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/2.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Hasan
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Leo
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Ta-110 Canada
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        899509
                                                    </td>
                                                    <td>
                                                        Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2903</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/3.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                        Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2904</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/1.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Shams Uddin
                                                    </td>
                                                    <td>
                                                        Male</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                        Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2905</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/2.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                       Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2906</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/3.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                       Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2907</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/1.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                       Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2908</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/2.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                       Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2909</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/3.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                       Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2910</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/user-1.png" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                     Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2911</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/1.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                        Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2912</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/user-1.png" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Leo Shown
                                                    </td>
                                                    <td>
                                                        Male</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                        Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2913</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/2.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                        Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2914</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/user-1.png" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                    Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2915</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/avatar/3.jpg" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Lew Shown
                                                    </td>
                                                    <td>
                                                        Male</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                     Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2916</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/user-1.png" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                        Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2917</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/user-1.png" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                      Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label><input type="checkbox" value=""></label>#2918</td>
                                                    <td>
                                                        <div class="round-img">
                                                            <a href=""><img src="assets/images/user-1.png" alt=""></a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        Richi Nazleo
                                                    </td>
                                                    <td>
                                                        Female</td>
                                                    <td>
                                                        David Smith
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>
                                                        A
                                                    </td>
                                                    <td>
                                                        Dhaka-1200
                                                    </td>
                                                    <td>
                                                        10/03/2013
                                                    </td>
                                                    <td>
                                                        +985049
                                                    </td>
                                                    <td>
                                                      Support@example.com
                                                    </td>
                                                    <td>
                                                        <span><a href=""><i class="ti-eye color-default"></i></a> </span>
                                                        <span><a href=""><i class="ti-pencil-alt color-success"></i></a></span>
                                                        <span><a href=""><i class="ti-trash color-danger"></i> </a></span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /# column -->

                    </div>
                    <!-- /# row -->
                    
                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Admin Dashboard | Last Updated: <span id="date-time"></span></p>
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