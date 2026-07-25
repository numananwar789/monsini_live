<!DOCTYPE html>
<html lang="en">

<head>
    <title>Password Reset - Monsini</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Monsini Password Reset" />
    <meta name="keywords" content="monsini, password, reset" />
    <meta name="author" content="Monsini" />

    <!-- Favicon icon -->
    <link rel="icon" href="/assets/images/monsiniprom-logo.png" type="image/x-icon">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/plugins/animation/css/animate.min.css">
    <!-- vendor css -->
    <link rel="stylesheet" href="/assets/css/style.css">

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
                    alt="Monsini Logo" height="70">
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
                            <li><a class="dropdown-item" href="https://monsiniprom.com/product-category/sp-2022">Spring
                                    2022</a></li>
                            <li><a class="dropdown-item" href="https://monsiniprom.com/product-category/sp-2023">Spring
                                    2023</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page"
                            href="https://monsiniprom.com/product-category/br-2023">Bridal 2023</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="https://monsiniprom.com/about-us">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="https://monsiniprom.com/contact-us">Contact us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('register.customer.get') }}">Retailer
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

    <!-- Bootstrap JS -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    <script src="/assets/js/vendor-all.min.js"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="/assets/js/pcoded.min.js"></script>
</body>

</html>
