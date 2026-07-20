<!DOCTYPE html>
<html lang="en">

<head>
  <title>Monsini- Reset password</title>
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

  <!-- Favicon icon -->
  <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
  <!-- fontawesome icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- animation css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <!-- vendor css -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


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
        <img src="https://monsiniprom.com/wp-content/uploads/2022/06/monsini-transparent-logo.png" alt="Monsini Logo" height="70">
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
              <li><a class="dropdown-item" href="https://monsiniprom.com/product-category/sp-2022">Spring 2022</a></li>
              <li><a class="dropdown-item" href="https://monsiniprom.com/product-category/sp-2023">Spring 2023</a></li>
            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="https://monsiniprom.com/product-category/br-2023">Bridal 2023</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="https://monsiniprom.com/about-us">About Us</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="https://monsiniprom.com/contact-us">Contact us</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="https://market.monsiniprom.com/sign-up.php">Retailer Registration</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="https://market.monsiniprom.com/">Retailer Login</a>
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

      @if($step === 1)
        <div class="card animate__animated animate__fadeIn">
          <div class="card-body text-center">
            <form method="POST" action="{{ route('password.email') }}">
              @csrf
              <div class="mb-4">
                <i class="fas fa-envelope auth-icon"></i>
              </div>
              <h3 class="mb-4">Reset Password</h3>
              <div class="input-group mb-3">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email', $email) }}" required>
              </div>
              @error('email')
                <p class="text-danger">{{ $message }}</p>
              @enderror
              <div>
                <button type="submit" class="btn btn-primary mb-4 shadow-2 w-100">Send Reset Code</button>
                <p class="mb-0 text-muted">Don't have an account? <a href="{{ route('register') }}">Signup</a></p>
              </div>
            </form>
          </div>
        </div>
      @endif

      @if($step === 2)
        <div class="card animate__animated animate__fadeIn">
          <div class="card-body text-center">
            <form method="POST" action="{{ route('password.otp') }}">
              @csrf
              <div class="mb-4">
                <i class="fas fa-key auth-icon"></i>
              </div>
              <h3 class="mb-4">Enter Verification Code</h3>
              <p>We sent a 5-digit code to <strong>{{ $email }}</strong></p>
              <div class="input-group mb-3">
                <input type="text" name="otp" class="form-control @error('otp') is-invalid @enderror" placeholder="OTP Code" required>
              </div>
              @error('otp')
                <p class="text-danger">{{ $message }}</p>
              @enderror
              <div>
                <button type="submit" class="btn btn-primary mb-4 shadow-2 w-100">Verify Code</button>
                <p class="mb-0 text-muted">Didn't receive the code? <a href="{{ route('password.request') }}">Resend</a></p>
              </div>
            </form>
          </div>
        </div>
      @endif

      @if($step === 3)
        <div class="card animate__animated animate__fadeIn">
          <div class="card-body text-center">
            <form method="POST" action="{{ route('password.update') }}">
              @csrf
              <div class="mb-4">
                <i class="fas fa-lock auth-icon"></i>
              </div>
              <h3 class="mb-4">Set New Password</h3>
              <div class="input-group mb-3">
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="New Password" required>
              </div>
              @error('password')
                <p class="text-danger">{{ $message }}</p>
              @enderror
              <div>
                <button type="submit" class="btn btn-primary mb-4 shadow-2 w-100">Reset Password</button>
                <p class="mb-0 text-muted">Remembered your password? <a href="{{ route('login') }}">Login</a></p>
              </div>
            </form>
          </div>
        </div>
      @endif
    </div>
  </div>

  <!-- Required Js -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/assets/js/vendor-all.min.js"></script>
  <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
  <script src="/assets/js/pcoded.min.js"></script>

  <script>
    // Add animation classes on form changes
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.card');
      cards.forEach(card => {
        card.classList.add('animate__animated', 'animate__fadeIn');
      });
    });
  </script>
</body>
</html>