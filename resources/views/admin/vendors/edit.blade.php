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
                                <form method="POST" action="{{ route('vendors.update', $vendor->vendor_ID) }}">
                                    @csrf
                                    @method('PUT')
                                    <h5 class="mb-0 text-uppercase">Edit Vendor</h5>
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
                                                           value="{{ old('name', $vendor->vendor_name) }}">
                                                </div>
                                                <div class="col">
                                                    <label for="company_name">Vendor Company Name</label>
                                                    <input required type="text" class="form-control" name="company_name" 
                                                           value="{{ old('company_name', $vendor->vendor_comp_name) }}">
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col">
                                                    <label for="address">Vendor Address</label>
                                                    <input required type="text" class="form-control" name="address" 
                                                           value="{{ old('address', $vendor->vendor_address) }}">
                                                </div>
                                                <div class="col">
                                                    <label for="phone">Vendor Phone</label>
                                                    <input type="tel" class="form-control" name="phone" 
                                                           value="{{ old('phone', $vendor->vendor_phone) }}">
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col">
                                                    <label for="fax">Vendor Fax</label>
                                                    <input type="tel" class="form-control" name="fax" 
                                                           value="{{ old('fax', $vendor->vendor_fax) }}">
                                                </div>
                                                <div class="col">
                                                    <label for="agent">Vendor Agent</label>
                                                    <input type="text" class="form-control" name="agent" 
                                                           value="{{ old('agent', $vendor->vendor_agent) }}">
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-6">
                                                    <label for="email">Vendor Email</label>
                                                    <input type="email" class="form-control" name="email" 
                                                           value="{{ old('email', $vendor->vendor_email) }}">
                                                </div>
                                                <div class="col-6">
                                                    <label for="days">Vendor Days</label>
                                                    <input type="text" class="form-control" name="days" 
                                                           value="{{ old('days', $vendor->vendor_days) }}">
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-6">
                                                    <label for="days_stock">Vendor Days (In Stock or On Way)</label>
                                                    <input type="text" class="form-control" name="days_stock" 
                                                           value="{{ old('days_stock', $vendor->vendor_days_stock) }}">
                                                </div>
                                                <div class="col-6">
                                                    <label for="message">Vendor Note</label>
                                                    <textarea name="message" id="message" class="form-control" rows="3">{{ old('message', $vendor->message) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <button type="submit" class="btn btn-primary">Update Vendor</button>
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