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
                <form method="POST" action="{{ route('custom.password.update') }}">
                    @csrf
                    <div class="mb-4">
                        <i class="feather icon-mail auth-icon"></i>
                    </div>
                    <h3 class="mb-4">Create New Password</h3>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="New Password" required>
                    </div>
                    <div class="input-group mb-4">
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
                    </div>
                    @error('password')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                    <div>
                        <button type="submit" class="btn btn-primary mb-4 shadow-2">Reset Password</button>
                        <p class="mb-0 text-muted">Remembered password? <a href="{{ route('login') }}">Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection