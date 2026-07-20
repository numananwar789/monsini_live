@extends('layouts.app')

@section('page-css')
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/animation/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/notification/css/notification.min.css') }}">
@endsection

@section('content')
<!-- [ Main Content ] start -->
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <!-- [ Main Content ] start -->
                        <div class="row">
                            <div class="col">
                                <form method="POST" action="{{ route('users.update', $user->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <h5 class="mb-0 text-uppercase">Edit Admin</h5>
                                    <hr />
                                    <div class="card">
                                        <div class="card-body">
                                            @if($errors->any())
                                                <div class="alert alert-danger">
                                                    <ul>
                                                        @foreach($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                            
                                            <div class="row">
                                                <div class="col">
                                                    <label for="name">Admin Name</label>
                                                    <input type="text" class="form-control" name="name" 
                                                           value="{{ old('name', $user->name) }}" placeholder="Enter admin name">
                                                </div>
                                                <div class="col">
                                                    <label for="user_name">Admin Username</label>
                                                    <input type="text" class="form-control" name="user_name" 
                                                           value="{{ old('user_name', $user->user_name) }}" placeholder="Enter username">
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col">
                                                    <label for="password">Password (Leave blank to keep current)</label>
                                                    <input type="password" class="form-control" name="password" 
                                                           id="password" placeholder="Enter new password" onchange="validatePassword()">
                                                </div>
                                                <div class="col">
                                                    <label for="password_confirmation">Confirm Password</label>
                                                    <input type="password" class="form-control" name="password_confirmation" 
                                                           id="password_confirmation" placeholder="Confirm password" onchange="validatePassword()">
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-6">
                                                    <label for="admin_role">Admin Role</label>
                                                    <select name="admin_role" id="admin_role" class="form-control">
                                                        <option value="admin" {{ old('role', $user->admin_role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                                        <option value="superadmin" {{ old('role', $user->admin_role) == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label for="email">Email Address</label>
                                                    <input type="email" class="form-control" name="email" 
                                                           value="{{ old('email', $user->email) }}" placeholder="Enter email address">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <button type="submit" class="btn btn-primary">Update Admin</button>
                                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- [ Main Content ] end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->
@endsection

@section('page-js')
<!-- Required Js -->
{{-- <script src="{{ asset('assets/js/vendor-all.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/pcoded.min.js') }}"></script> --}}

<!-- Notification Js -->
<script src="{{ asset('assets/plugins/notification/js/bootstrap-growl.min.js') }}"></script>

<script>
    function validatePassword() {
        const password = document.getElementById('password');
        const confirm = document.getElementById('password_confirmation');
        
        if (password.value && confirm.value !== password.value) {
            confirm.setCustomValidity('Passwords do not match');
        } else {
            confirm.setCustomValidity('');
        }
    }

    // Form validation feedback
    @if(session('success'))
        $(document).ready(function() {
            $.notify({
                title: "Success!",
                message: "{{ session('success') }}",
                icon: 'fa fa-check' 
            },{
                type: "success"
            });
        });
    @endif

    @if($errors->any())
        $(document).ready(function() {
            $.notify({
                title: "Error!",
                message: "Please check the form for errors",
                icon: 'fa fa-times' 
            },{
                type: "danger"
            });
        });
    @endif
</script>
@endsection