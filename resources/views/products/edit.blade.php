@extends('layouts.app')

@section('page-css')
@endsection

@section('content')
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <div class="row">
                                <div class="col">
                                    <form method="POST" action="{{ route('products.update', $product->product_ID) }}">
                                        @csrf
                                        @method('PUT')
                                        <h5 class="mb-0 text-uppercase">Edit Product</h5>
                                        <hr />
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="style">Product Style</label>
                                                            <input type="text"
                                                                class="form-control @error('style') is-invalid @enderror"
                                                                id="style" name="style"
                                                                value="{{ old('style', $product->product_style) }}"
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
                                                                value="{{ old('factory_style', $product->factory_style) }}">
                                                            @error('factory_style')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="size">Product Size Range</label>
                                                            <input type="text"
                                                                class="form-control @error('size') is-invalid @enderror"
                                                                id="size" name="size"
                                                                value="{{ old('size', $product->product_size_range) }}"
                                                                required>
                                                            @error('size')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="vendor_id">Product Vendor</label>
                                                            <select
                                                                class="form-control @error('vendor_id') is-invalid @enderror"
                                                                id="vendor_id" name="vendor_id" required>
                                                                <option value="">Select Vendor</option>
                                                                @foreach ($vendors as $vendor)
                                                                    <option value="{{ $vendor->vendor_ID }}"
                                                                        {{ old('vendor_id', $product->product_vendor_ID) == $vendor->vendor_ID ? 'selected' : '' }}>
                                                                        {{ $vendor->vendor_comp_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('vendor_id')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="cost">Product Cost</label>
                                                            <input type="number" step="0.01"
                                                                class="form-control @error('cost') is-invalid @enderror"
                                                                id="cost" name="cost"
                                                                value="{{ old('cost', $product->product_cost) }}" required>
                                                            @error('cost')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="wholesale">Product Wholesale Price</label>
                                                            <input type="number" step="0.01"
                                                                class="form-control @error('wholesale') is-invalid @enderror"
                                                                id="wholesale" name="wholesale"
                                                                value="{{ old('wholesale', $product->product_wholesale_price) }}"
                                                                required>
                                                            @error('wholesale')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="link">Product Link</label>
                                                            <input type="text"
                                                                class="form-control @error('link') is-invalid @enderror"
                                                                id="link" name="link"
                                                                value="{{ old('link', $product->product_link) }}">
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
                                                                value="{{ old('image', $product->product_image) }}">
                                                            @error('image')
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
                                                                @php
                                                                    // $allSubProducts = [
                                                                    //     'Shawl',
                                                                    //     'Gloves',
                                                                    //     'Fabrics',
                                                                    //     'Buttons',
                                                                    //     'Embroidery',
                                                                    // ];
                                                                    $selectedSubProducts = old(
                                                                        'sub_products',
                                                                        $product->sub_products ?? [],
                                                                    );
                                                                @endphp

                                                                @foreach ($allSubProducts as $sub)
                                                                    <option value="{{ $sub }}"
                                                                        {{ in_array($sub, $selectedSubProducts ?? []) ? 'selected' : '' }}>
                                                                        {{ $sub }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('sub_products')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
<div class="col-md-6">
    <div class="form-group">
        <label for="version_year">Version Year</label>
        <input type="number"
            class="form-control @error('version_year') is-invalid @enderror"
            id="version_year"
            name="version_year"
            value="{{ old('version_year', $product->version_year) }}"
            required>

        @error('version_year')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-block">
                                            <button type="submit" class="btn btn-primary">Update Product</button>
                                            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Color Update Section -->
                            <div class="row mt-4">
                                <div class="col">
                                    <h5 class="mb-0 text-uppercase">Update Colors</h5>
                                    <hr />
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Color Name</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($colors as $color)
                                                    <tr>
                                                        <td>
                                                            <input type="text" class="form-control color-input"
                                                                value="{{ $color->product_color }}"
                                                                data-id="{{ $color->product_ID }}">
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-primary update-color"
                                                                data-id="{{ $color->product_ID }}">
                                                                Update
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Color Update Section End-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
        crossorigin="anonymous"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

        <script src="/assets/js/pcoded.min.js"></script> --}}

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" integrity="sha512-3pIirOrwegjM6erE5gPSwkUzO+3cTjpnV9lexlNZqvupR64iZBnOOTiiLPb9M36zpMScbmUNIcHUqKD47M719g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function() {
            $('.update-color').on('click', function() {
                const productId = $(this).data('id');
                const colorName = $(this).closest('tr').find('.color-input').val();
                const token = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    type: 'POST',
                    url: '{{ route('products.update.color') }}',
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    data: {
                        color_id: productId,
                        color_name: colorName
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Color updated successfully');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error: ' + xhr.responseJSON.message);
                    }
                });
            });


            $('#sub_products').select2({
                placeholder: "Select sub-products",
                allowClear: true
            });


        });
    </script>
@endsection
