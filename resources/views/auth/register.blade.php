@extends('layouts.app2')
@section('title', 'Sign up')

@section('content')
    <div class="auth-wrapper">
        <div class="auth-content w-md-100 w-sm-100 w-75 ">
            <div class="auth-bg">
                <span class="r"></span>
                <span class="r s"></span>
                <span class="r s"></span>
                <span class="r"></span>
            </div>
            <form action="{{ route('register.customer.post') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="feather icon-user-plus auth-icon"></i>
                        </div>
                        <h3 class="mb-4">Sign up</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control @error('f_name') is-invalid @enderror"
                                        placeholder="First name" name="f_name" value="{{ old('f_name') }}" required>
                                    @error('f_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control @error('l_name') is-invalid @enderror"
                                        placeholder="Last name" name="l_name" value="{{ old('l_name') }}" required>
                                    @error('l_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control @error('store') is-invalid @enderror"
                                        placeholder="Store name" name="store" value="{{ old('store') }}" required>
                                    @error('store')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control @error('sales_rep') is-invalid @enderror"
                                        placeholder="Sales Representer" name="sales_rep" value="{{ old('sales_rep') }}"
                                        required>
                                    @error('sales_rep')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control @error('user_name') is-invalid @enderror"
                                        placeholder="User Name" name="user_name" value="{{ old('user_name') }}" required>
                                    @error('user_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        placeholder="Email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                        placeholder="Phone" name="phone" value="{{ old('phone') }}" required>
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <input type="tel" class="form-control @error('fax') is-invalid @enderror"
                                        placeholder="Fax" name="fax" value="{{ old('fax') }}">
                                    @error('fax')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                                        placeholder="Street, City, Province / State" name="address"
                                        value="{{ old('address') }}" required>
                                    @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control @error('zip') is-invalid @enderror"
                                        placeholder="Zip" name="zip" value="{{ old('zip') }}" required>
                                    @error('zip')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="input-group mb-3">
                                    <select name="country" id="countryId"
                                        class="form-control @error('country') is-invalid @enderror" required>
                                        <option value="">Select Country</option>
                                        <option value="Afghanistan" @if (old('country') == 'Afghanistan') selected @endif>
                                            Afghanistan</option>
                                        <option value="Åland Islands" @if (old('country') == 'Åland Islands') selected @endif>
                                            Åland Islands</option>
                                        <!-- Rest of the country options with old() support -->
                                        @include('partials.country-options')
                                    </select>
                                    @error('country')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-4">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        placeholder="password" name="password" required>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-4">
                                    <input type="password" class="form-control" placeholder="confirm password"
                                        name="password_confirmation" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-left">
                            <div class="checkbox checkbox-fill d-inline">
                                <input type="checkbox" name="newsletter" id="newsletter"
                                    @if (old('newsletter')) checked @endif>
                                <label for="newsletter" class="cr">Send me the <a href="#!"> Newsletter</a>
                                    weekly.</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary shadow-2 mb-4">Sign up</button>
                        @if ($errors->any())
                            <div class="bg-danger w-25 mb-3 text-center mx-auto text-white"
                                style="height: 30px; display:flex; align-items:center; justify-content:center; font-size:18px; border-radius: 5px; ">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <p class="mb-0 text-muted">Already have an account? <a href="{{ route('login') }}"> Log in</a>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
