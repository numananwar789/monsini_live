<!DOCTYPE html>
<html lang="en">

<head>
    <title>Monsini - @yield('title')</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="CodedThemes" />

    <!-- Favicon icon -->
    <link rel="icon" href="/assets/images/monsiniprom-logo.png" type="image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome/css/fontawesome-all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/animation/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        @import url('https://fonts.cdnfonts.com/css/poppins');

        #navbar-font a.nav-link,
        li.nav-item {
            font-family: 'Poppins' !important;
            font-size: 16px;
            font-weight: 500;
            letter-spacing: 1.1px;
            border-top: 3px solid #f8f9fa;
            border-bottom: 3px solid #f8f9fa;
        }

        li.nav-item:hover {
            border-top: 3px solid #237197;
            border-bottom: 3px solid #237197;
        }

        li.nav-item:hover a.nav-link {
            color: #237197 !important;
        }

        a.nav-link {
            padding-left: 12px !important;
            padding-right: 12px !important;
            color: #237197 !important;
        }
    </style>

    <style>
        @font-face {
            font-family: 'Lobster Two';
            font-style: normal;
            font-weight: 400;
            src: local('Lobster Two'), url('https://fonts.cdnfonts.com/s/12184/lobstertworegular.woff') format('woff');
        }

        #navbar-font a.nav-link,
        li.nav-item {
            font-family: "Lobster Two" !important;
            font-size: 18px;
            font-weight: 500;
            letter-spacing: 1.1px;
        }

        li.nav-item:hover {
            background: #5E239D;
        }

        li.nav-item:hover a.nav-link {
            color: #fff !important;
        }

        a.nav-link.active {
            background: #5E239D;
            color: #fff !important;
        }

        a.nav-link {
            padding-left: 12px !important;
            padding-right: 12px !important;
            color: black !important;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light static-top py-3" id="navbar-font"
        style="box-shadow: 0px 10px 10px -11px rgb(101 100 219 / 63%);">
        <div class="container">
            <a class="" href="https://monsiniprom.com/">
                <img src="https://monsiniprom.com/wp-content/uploads/2022/06/monsini-transparent-logo.png"
                    alt="..." height="70">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="https://monsiniprom.com/">Home</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Prom Collections
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="https://monsinidress.com/prom/2025">2025 Collection</a>
                            </li>
                            <li><a class="dropdown-item" href="https://monsinidress.com/prom/2026">2026 Collection</a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Bridal Collections
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="https://monsiniprom.com/bridal/2025">2025 Collection</a>
                            </li>
                            <li><a class="dropdown-item" href="https://monsiniprom.com/bridal/2026">2026 Collection</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Enchanted Evening
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="https://monsiniprom.com/evening/2025">2025 Collection</a>
                            </li>
                            <li><a class="dropdown-item" href="https://monsiniprom.com/evening/2026">2026 Collection</a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="https://monsiniprom.com/about">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="https://monsiniprom.com/contact">Contact us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('register') }}">Retailer
                            Registration</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Retailer Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- Required Js -->
    <script src="{{ asset('assets/js/vendor-all.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.min.js') }}"></script>
</body>

</html>
