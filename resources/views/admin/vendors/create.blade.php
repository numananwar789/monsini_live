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
                                <form method="POST" action="{{ route('vendors.store') }}">
                                    @csrf
                                    <h5 class="mb-0 text-uppercase">Add Vendor</h5>
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
                                                    <label for="name">Vendor Name</label>
                                                    <input required type="text" class="form-control" name="name" 
                                                           value="{{ old('name') }}" placeholder="Enter vendor name">
                                                </div>
                                                <div class="col">
                                                    <label for="company_name">Vendor Company Name</label>
                                                    <input required type="text" class="form-control" name="company_name" 
                                                           value="{{ old('company_name') }}" placeholder="Enter company name">
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col">
                                                    <label for="address">Vendor Address</label>
                                                    <input required type="text" class="form-control" name="address" 
                                                           value="{{ old('address') }}" placeholder="Enter address">
                                                </div>
                                                <div class="col">
                                                    <label for="phone">Vendor Phone</label>
                                                    <input type="tel" class="form-control" name="phone" 
                                                           value="{{ old('phone') }}" placeholder="Enter phone number">
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col">
                                                    <label for="email">Vendor Email</label>
                                                    <input required type="email" class="form-control" name="email" 
                                                           value="{{ old('email') }}" placeholder="Enter email">
                                                </div>
                                                <div class="col">
                                                    <label for="fax">Vendor Fax</label>
                                                    <input type="tel" class="form-control" name="fax" 
                                                           value="{{ old('fax') }}" placeholder="Enter fax number">
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-6">
                                                    <label for="agent">Vendor Agent</label>
                                                    <input type="text" class="form-control" name="agent" 
                                                           value="{{ old('agent') }}" placeholder="Enter agent name">
                                                </div>
                                                <div class="col-6">
                                                    <label for="message">Message for Customer</label>
                                                    <textarea name="message" id="message" class="form-control" rows="3" 
                                                              placeholder="Enter message for customer">{{ old('message', 'Message for customer') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <button type="submit" class="btn btn-primary">Add Vendor</button>
                                        <a href="{{ route('vendors.index') }}" class="btn btn-secondary">Cancel</a>
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