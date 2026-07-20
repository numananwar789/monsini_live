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
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Add New Product</h5>
                                        </div>
                                        <div class="card-body">
                                            @if (session('error'))
                                                <div class="alert alert-danger">
                                                    {{ session('error') }}
                                                </div>
                                            @endif

                                            <form method="POST" action="{{ route('products.store') }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="style">Product Style</label>
                                                            <input type="text"
                                                                class="form-control @error('style') is-invalid @enderror"
                                                                id="style" name="style" value="{{ old('style') }}"
                                                                required>
                                                            @error('style')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="style">Factory Style</label>
                                                            <input type="text"
                                                                class="form-control @error('factory_style') is-invalid @enderror"
                                                                id="factory_style" name="factory_style"
                                                                value="{{ old('factory_style') }}">
                                                            @error('factory_style')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="color">Product Color</label>
                                                            <input type="text"
                                                                class="form-control @error('color') is-invalid @enderror"
                                                                id="color" name="color" value="{{ old('color') }}"
                                                                required>
                                                            @error('color')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="size">Product Size Range</label>
                                                            <input type="text"
                                                                class="form-control @error('size') is-invalid @enderror"
                                                                id="size" name="size" value="{{ old('size') }}"
                                                                required>
                                                            @error('size')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="cost">Product Cost</label>
                                                            <input type="number" step="0.01"
                                                                class="form-control @error('cost') is-invalid @enderror"
                                                                id="cost" name="cost" value="{{ old('cost') }}"
                                                                required>
                                                            @error('cost')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    {{-- </div>

                                            <div class="row mt-3"> --}}
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="wholesale">Product Wholesale Price</label>
                                                            <input type="number" step="0.01"
                                                                class="form-control @error('wholesale') is-invalid @enderror"
                                                                id="wholesale" name="wholesale"
                                                                value="{{ old('wholesale') }}" required>
                                                            @error('wholesale')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="vendor_id">Product Vendor</label>
                                                            <select
                                                                class="form-control select2 @error('vendor_id') is-invalid @enderror"
                                                                id="vendor_id" name="vendor_id" required>
                                                                <option value="">Select Vendor</option>
                                                                @foreach ($vendors as $vendor)
                                                                    <option value="{{ $vendor->vendor_ID }}"
                                                                        {{ old('vendor_id') == $vendor->vendor_ID ? 'selected' : '' }}>
                                                                        {{ $vendor->vendor_comp_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('vendor_id')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="sub_products">Sub Products</label>
                                                            <select
                                                                class="form-control @error('sub_products') is-invalid @enderror"
                                                                id="sub_products" name="sub_products[]" multiple>
                                                                @foreach ($allSubProducts as $item)
                                                                     <option value="{{ $item }}">{{ $item }}</option>
                                                                @endforeach
                                                                {{-- <option value="Shawl">Shawl</option>
                                                                <option value="Gloves">Gloves</option>
                                                                <option value="Fabrics">Fabrics</option>
                                                                <option value="Buttons">Buttons</option>
                                                                <option value="Embroidery">Embroidery</option> --}}

                                                            </select>
                                                            @error('sub_products')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    {{-- </div>

                                            <div class="row mt-3"> --}}
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="link">Product Link</label>
                                                            <input type="text"
                                                                class="form-control @error('link') is-invalid @enderror"
                                                                id="link" name="link" value="{{ old('link') }}">
                                                            @error('link')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="image">Product Image URL</label>
                                                            <input type="text"
                                                                class="form-control @error('image') is-invalid @enderror"
                                                                id="image" name="image"
                                                                value="{{ old('image') }}">
                                                            @error('image')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group mt-4">
                                                    <button type="submit" class="btn btn-primary">Add Product</button>
                                                    <a href="{{ route('products.index') }}"
                                                        class="btn btn-secondary">Cancel</a>
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
        </div>
    </div>
@endsection

@section('page-js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script>
        $(document).ready(function() {

            $('.select2').select2();

            $('#sub_products').select2({
                placeholder: "Select sub-products",
                allowClear: true
            });

        });
    </script>
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
        crossorigin="anonymous"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

        <script src="/assets/js/pcoded.min.js"></script> --}}
@endsection
