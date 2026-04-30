<!DOCTYPE html>
<html lang="en">

<head>
    <title>Logistics &mdash; Colorlib Website Template</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,700,900|Display+Playfair:200,300,400,700">
    <link rel="stylesheet" href="{{ asset('asset/fonts/icomoon/style.css') }}">

    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/owl.theme.default.min.css') }}">

    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap-datepicker.css') }}">

    <link rel="stylesheet" href="{{ asset('asset/fonts/flaticon/font/flaticon.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('asset/css/aos.css') }}">

    <link rel="stylesheet" href="{{ asset('asset/css/style.css') }}">

</head>

<body>

    <div class="site-wrap">

        @include('layouts.partials.header')

        @yield('content')

        @include('layouts.partials.footer')

    </div>

    <script src="{{ asset('asset/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('asset/js/jquery-migrate-3.0.1.min.js') }}"></script>
    <script src="{{ asset('asset/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('asset/js/popper.min.js') }}"></script>
    <script src="{{ asset('asset/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('asset/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('asset/js/jquery.stellar.min.js') }}"></script>
    <script src="{{ asset('asset/js/jquery.countdown.min.js') }}"></script>
    <script src="{{ asset('asset/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('asset/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('asset/js/aos.js') }}"></script>

    <script src="{{ asset('asset/js/main.js') }}"></script>

</body>

</html>