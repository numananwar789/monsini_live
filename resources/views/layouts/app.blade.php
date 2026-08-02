<!DOCTYPE html>
<html lang="en">

<head>
    <title>Monsiniprom</title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />


    <meta name="author" content="Monsini" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon icon -->
    <link rel="icon" href="/assets/images/monsiniprom-logo.png" type="image/x-icon">
    <!-- fontawesome icon -->
    <link rel="stylesheet" href="/assets/fonts/fontawesome/css/fontawesome-all.min.css">
    <!-- animation css -->
    <link rel="stylesheet" href="/assets/plugins/animation/css/animate.min.css">
    <!-- notification css -->
    <link rel="stylesheet" href="/assets/plugins/notification/css/notification.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />
    <!-- vendor css -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/datetime/1.1.2/css/dataTables.dateTime.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />

    @yield('page-css')
    @stack('styles')
</head>


<body>

    {{-- Header / top bar --}}
    @include('layouts.partials.header')


    @include('layouts.partials.sidebar')


    @yield('content')


    {{-- Footer --}}

    @include('layouts.partials.footer')


    {{-- Global JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.min.js') }}"></script>

    {{-- Page-specific JS --}}
    @yield('page-js')
</body>

</html>
