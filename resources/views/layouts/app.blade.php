<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MLC Classroom') }} - @yield('title', 'Dashboard')</title>

    <!-- ================= Favicon ================== -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/apple-touch-icon.png') }}">

    <!-- Styles -->
    <link href="{{ asset('assets/css/lib/calendar2/semantic.ui.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/lib/calendar2/pignose.calendar.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/lib/chartist/chartist.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/lib/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/lib/themify-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/lib/owl.carousel.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/lib/owl.theme.default.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/lib/weather-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/lib/menubar/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/lib/toastr/toastr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/lib/sweetalert/sweetalert.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/lib/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/lib/unix.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    
    <!-- Page-specific styles -->
    @stack('styles')
</head>

<body>
    <!-- Sidebar -->
    @include('layouts.sidebar')
    
    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Page Content -->
    <div class="content-wrap">
        @yield('content')
    </div>

    <!-- Search Modal -->
    <div id="search">
        <button type="button" class="close">×</button>
        <form>
            <input type="search" value="" placeholder="type keyword(s) here" />
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <!-- Base Scripts (Load jQuery FIRST) -->
    <script src="{{ asset('assets/js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/jquery.nanoscroller.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/menubar/sidebar.js') }}"></script>
    <script src="{{ asset('assets/js/lib/preloader/pace.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/bootstrap.min.js') }}"></script>
    
    <!-- Calendar Scripts -->
    <script src="{{ asset('assets/js/lib/calendar-2/moment.latest.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/calendar-2/semantic.ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/calendar-2/prism.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/calendar-2/pignose.calendar.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/calendar-2/pignose.init.js') }}"></script>
    
    <!-- Widget Scripts -->
    <script src="{{ asset('assets/js/lib/weather/jquery.simpleWeather.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/weather/weather-init.js') }}"></script>
    <script src="{{ asset('assets/js/lib/circle-progress/circle-progress.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/circle-progress/circle-progress-init.js') }}"></script>
    
    <!-- Chart Libraries (Load libraries BEFORE init scripts) -->
    <script src="{{ asset('assets/js/lib/chartist/chartist.min.js') }}"></script>
    
    <script src="{{ asset('assets/js/lib/sparklinechart/jquery.sparkline.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/sparklinechart/sparkline.init.js') }}"></script>
    <script src="{{ asset('assets/js/lib/owl-carousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/owl-carousel/owl.carousel-init.js') }}"></script>

    <script src="{{ asset('assets/js/lib/toastr/toastr.min.js') }}"></script>

    <script>
        // Configure Toastr defaults
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut",
            "tapToDismiss": false
        };

        // Handle Laravel session flash messages
        $(document).ready(function() {
            @if(session('success'))
                toastr.success("{{ session('success') }}");
            @endif

            @if(session('error'))
                toastr.error("{{ session('error') }}");
            @endif

            @if(session('warning'))
                toastr.warning("{{ session('warning') }}");
            @endif

            @if(session('info'))
                toastr.info("{{ session('info') }}");
            @endif

            @if($errors->any())
                @foreach($errors->all() as $error)
                    toastr.error("{{ $error }}");
                @endforeach
            @endif
        });
    </script>
    <!-- scripit init-->
    {{-- <script src="{{ asset('assets/js/lib/toastr/toastr.init.js') }}"></script> --}}
    </script>

    <script src="{{ asset('assets/js/lib/sweetalert/sweetalert.min.js') }}"></script>
    <!-- scripit init-->
    {{-- <script src="{{ asset('assets/js/lib/sweetalert/sweetalert.init.js') }}"></script> --}}
    
    <!-- Custom scripts (before page-specific scripts) -->
    <script src="{{ asset('assets/js/scripts.js') }}"></script>

    <!-- Page-specific scripts (loads Chart.js and init scripts) -->
    @stack('scripts')
    
    <!-- Initialize charts AFTER everything is loaded -->
    <script>
        $(document).ready(function() {
            // Wait for DOM to be fully loaded before initializing charts
            console.log('DOM Ready - Charts can now initialize');
        });
    </script>
</body>
</html>