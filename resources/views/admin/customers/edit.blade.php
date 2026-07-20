@extends('layouts.app')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <div class="row">
                            <div class="col">
                                <form method="POST" action="{{ route('customers.update', $customer->cust_ID) }}">
                                    @csrf
                                    @method('PUT')
                                    <h5 class="mb-0 text-uppercase">Edit Customer</h5>
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
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>First Name</label>
                                                        <input type="text" class="form-control" name="f_name" 
                                                               value="{{ old('f_name', $customer->f_name) }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Last Name</label>
                                                        <input type="text" class="form-control" name="l_name" 
                                                               value="{{ old('l_name', $customer->l_name) }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Username</label>
                                                        <input type="text" class="form-control" name="cust_username" 
                                                               value="{{ old('cust_username', $customer->cust_username) }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="email" class="form-control" name="cust_email" 
                                                               value="{{ old('cust_email', $customer->cust_email) }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Company Name</label>
                                                        <input type="text" class="form-control" name="cust_comp_name" 
                                                               value="{{ old('cust_comp_name', $customer->cust_comp_name) }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Phone</label>
                                                        <input type="text" class="form-control" name="cust_phone" 
                                                               value="{{ old('cust_phone', $customer->cust_phone) }}">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Fax</label>
                                                        <input type="text" class="form-control" name="cust_fax" 
                                                               value="{{ old('cust_fax', $customer->cust_fax) }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Sales Representative</label>
                                                        <input type="text" class="form-control" name="cust_sales_rep" 
                                                               value="{{ old('cust_sales_rep', $customer->cust_sales_rep) }}">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-3">  
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Address</label>
                                                        <textarea class="form-control" name="cust_address" rows="3">{{ old('cust_address', $customer->cust_address) }}</textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Password</label>
                                                        <input type="text"  value="{{ old('password', $customer->cust_password) }}" class="form-control" name="password">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Confirm Password</label>
                                                        <input type="text" value="{{ old('password_confirmation', $customer->cust_password) }}" class="form-control" name="password_confirmation">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <button type="submit" class="btn btn-primary">Update Customer</button>
                                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

