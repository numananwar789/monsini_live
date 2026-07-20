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
                <form method="POST" action="{{ route('custom.password.email') }}">
                    @csrf
                    <div class="mb-4">
                        <i class="feather icon-mail auth-icon"></i>
                    </div>
                    <h3 class="mb-4">Reset Password</h3>
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                    <div>
                        <button type="submit" class="btn btn-primary mb-4 shadow-2">Send OTP</button>
                        <p class="mb-0 text-muted">Don't have an account? <a href="{{ route('register') }}">Signup</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection