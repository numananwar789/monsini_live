<!DOCTYPE html>
<html lang="en">

<head>
    <title>Monsini - Signin</title>
    <!-- HTML5 Shim and Respond.js IE10 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 10]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="CodedThemes" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon icon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <!-- fontawesome icon -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome/css/fontawesome-all.min.css') }}">
    <!-- animation css -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/animation/css/animate.min.css') }}">
    <!-- vendor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

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
    <nav class="navbar navbar-expand-lg navbar-light bg-light static-top py-3" id="navbar-font" style="box-shadow: 0px 10px 10px -11px rgb(101 100 219 / 63%);">
        <div class="container">
            <a class="" href="https://monsiniprom.com/">
                <img src="https://monsiniprom.com/wp-content/uploads/2022/06/monsini-transparent-logo.png" alt="..." height="70">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="https://monsiniprom.com/">Home</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Prom Collections
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="https://monsinidress.com/prom/2025">2025 Collection</a></li>
                            <li><a class="dropdown-item" href="https://monsinidress.com/prom/2026">2026 Collection</a></li>
                        </ul>
                    </li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Bridal Collections
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="https://monsiniprom.com/bridal/2025">2025 Collection</a></li>
                            <li><a class="dropdown-item" href="https://monsiniprom.com/bridal/2026">2026 Collection</a></li>
                        </ul>
                    </li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Enchanted Evening
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="https://monsiniprom.com/evening/2025">2025 Collection</a></li>
                            <li><a class="dropdown-item" href="https://monsiniprom.com/evening/2026">2026 Collection</a></li>
                        </ul>
                    </li>                    

                    <!--<li class="nav-item">-->
                    <!--    <a class="nav-link" aria-current="page" href="https://monsinidress.com/prom/2025">Bridal Collections</a>-->
                    <!--</li>-->
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="https://monsiniprom.com/about">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="https://monsiniprom.com/contact">Contact us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('register.customer.get') }}">Retailer Registration</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Retailer Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="auth-wrapper">
        <div class="auth-content">
            <div class="auth-bg">
                <span class="r"></span>
                <span class="r s"></span>
                <span class="r s"></span>
                <span class="r"></span>
            </div>
            
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />
            
            <form method="POST" id="login-form" action="{{ route('login') }}">
                @csrf
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="feather icon-unlock auth-icon"></i>
                        </div>
                        <h3 class="mb-4">Login</h3>
                        
                        <!-- Username/Email -->
                        <div class="input-group mb-3">
                            <input type="text" id="email" name="email" class="form-control" placeholder="Username or Email" value="{{ old('email') }}" required autofocus>
                        </div>
                        @error('email')
                            <div class="alert alert-danger text-center py-1 my-2">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                        
                        <!-- Password -->
                        <div class="input-group mb-4">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        @error('password')
                            <div class="alert alert-danger text-center py-1 my-2">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                        
                        <!-- Remember Me -->
                        <div class="form-group text-left">
                            <div class="checkbox checkbox-fill d-inline">
                                <input type="checkbox" name="remember" id="remember_me" {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember_me" class="cr"> Save credentials</label>
                            </div>
                        </div>
                        
                        <button type="submit" onclick="handleLoginClick(event)" class="btn btn-primary shadow-2 mb-4">Login</button>
                        
                        @if (Route::has('custom.password.request'))
                            <p class="mb-2 text-muted">Forgot password? <a href="{{ route('custom.password.request') }}">Reset</a></p>
                        @endif
                        
                        <p class="mb-0 text-muted">Don't have an account? <a href="{{ route('register.customer.get') }}">Signup</a></p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Required Js -->
    <script src="{{ asset('assets/js/vendor-all.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.min.js') }}"></script>



    <script>
        let submitted = false;

        function handleLoginClick(e) {
            if (submitted) {
                e.preventDefault();
                return;
            }
            submitted = true;
            document.getElementById('login-form').submit();
        }

        // Optional: disable auto-submit via autocomplete
        document.getElementById('login-form').addEventListener('submit', function (e) {
            if (submitted) return;
            submitted = true;
        });
    </script>

</body>
</html>