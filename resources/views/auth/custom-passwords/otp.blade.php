@extends('layouts.custom-auth')

@section('content')
<div class="auth-wrapper">
    <div class="auth-content">
        <div class="auth-bg">
            <span class="r"></span>
            <span class="r s"></span>
            <span class="r s"></span>
            <span class="r"></span>
        </div>

        <div class="card">
            <div class="card-body text-center">
                <form method="POST" action="{{ route('custom.password.verify') }}">
                    @csrf
                    <div class="mb-4">
                        <i class="feather icon-mail auth-icon"></i>
                    </div>
                    <h3 class="mb-4">Enter Verification Code</h3>
                    <p>We've sent a 5-digit code to your email</p>
                    <div class="input-group mb-3">
                        <input type="text" name="otp" class="form-control @error('otp') is-invalid @enderror" placeholder="OTP Code" required autofocus>
                    </div>
                    @error('otp')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                    <div>
                        <button type="submit" class="btn btn-primary mb-4 shadow-2">Verify Code</button>
                        <p class="mb-0 text-muted">Didn't receive code? <a href="{{ route('custom.password.request') }}">Resend</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
