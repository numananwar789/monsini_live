@extends('layouts.app')

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
                                    <form method="POST" action="{{ route('orders.store') }}">
                                        @csrf
                                        <h5 class="mb-0 text-uppercase">Add Order</h5>
                                        <hr />


                                        @if (session('success'))
                                            <div class="alert alert-success">
                                                {{ session('success') }}
                                            </div>
                                        @endif

                                        @if (session('error'))
                                            <div class="alert alert-danger">
                                                {{ session('error') }}
                                            </div>
                                        @endif

                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="customers">Customer Name</label>
                                                        <select required name="customers" id="customers"
                                                            class="form-control">
                                                            <option value="" selected>Choose a Customer</option>
                                                            @foreach ($custList as $cust)
                                                                <option value="{{ $cust->cust_comp_name }}">
                                                                    {{ strtoupper($cust->cust_comp_name) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label for="vendorsName">Vendor Name</label>
                                                        <input readonly required type="text" class="form-control"
                                                            name="vendorsName" id="vendorsName" value="NA">
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="style">Product Style</label>
                                                        <select required name="style" id="style" class="form-control">
                                                            <option value="">Choose a Product</option>
                                                            @foreach ($prodList as $prod)
                                                                <option value="{{ $prod->product_style }}">
                                                                    {{ strtoupper($prod->product_style) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label for="color">Product Color</label>
                                                        <select required name="color" id="color" class="form-control">
                                                            <option value="">Choose a Color</option>
                                                            <option value="">Choose a Product First</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="size">Product Size</label>
                                                        <select required name="size" id="size" class="form-control">
                                                            <option value="">Choose A Size</option>
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label for="quantity">Order Quantity</label>
                                                        <input required type="number" class="form-control" name="quantity"
                                                            id="quantity" min="1">
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="price">Total Price</label>
                                                        <input required type="text" class="form-control" name="price"
                                                            id="price" readonly value="NA">
                                                    </div>
                                                    <div class="col-6">
                                                        <label for="purchase_id">Purchase ID</label>
                                                        <input required type="text" class="form-control"
                                                            name="purchase_id" id="purchase_id">
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="orderStatus">Order Status</label>
                                                        <select required name="status" id="orderStatus"
                                                            class="form-control">
                                                            <option selected value="pending">Pending</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label for="note">Note</label>
                                                        <input type="text" class="form-control" name="note"
                                                            id="note">
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col-6">
                                                        <label for="wearDate">Wear Date</label>
                                                        <input type="date" onkeydown="return false" required
                                                            name="wearDate" class="form-control"
                                                            min="{{ date('Y-m-d', strtotime('tomorrow')) }}">
                                                    </div>

                                                    <div class="col">
                                                        <label for="orderStatus">Sub Products</label>
                                                        <select name="sub_products[]" multiple id="sub_products" class="form-control">

                                                        </select>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-block">
                                            <button type="submit" class="btn btn-primary">Add Order</button>
                                            <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />



    <style>
        .in-td {
            width: 200px;
            margin: 0 auto;
        }

        /* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
    <script>
        $(document).ready(function() {

            $('#sub_products').select2({
                placeholder: "Select sub-products",
                allowClear: true
            });

            let costProd = '';

            // Get colors when style changes
            $('#style').change(function() {
                const style = $(this).val();
                if (style) {
                    $.get("/admin/orders/get-colors/" + style, function(data) {
                        $('#color').html(data);
                    });
                }
            });

            // Set vendor name when style changes
            $('#style').change(function() {
                const style = $(this).val();
                const vxp = @json($vendorProductList);

                const vendor = vxp.find(item => item.product_style === style);
                if (vendor) {
                    $('#vendorsName').val(vendor.product_vendor_name.toUpperCase());
                }
            });

            // Get sizes when style changes
            $('#style').change(function() {
                const style = $(this).val();
                if (style) {
                    $.get("/admin/orders/get-sizes/" + style, function(data) {
                        $('#size').html(data);
                    });
                }
            });

            // Get cost when style changes
            $('#style').change(function() {
                const style = $(this).val();
                if (style) {
                    $.get("/admin/orders/get-cost/" + style, function(data) {
                        costProd = data;
                    });
                }
            });

            // Calculate price when quantity or size changes
            $('#quantity, #size').on('keyup change', function() {
                const quantity = $('#quantity').val();
                const size = $('#size').val();

                // if (quantity && size && costProd) {
                //     let price = quantity * costProd;
                //     if (size >= 18) {
                //         price = quantity * (parseInt(costProd) + 30);
                //     }
                //     $('#price').val(price);
                // }
                
                if (quantity && size && costProd) {
    const style = $('#style').val();
    let basePrice = parseFloat(costProd);
    let addition = 0;

    // Apply size-based logic
    if (size >= 18) {
        // Check if style starts with "B" (Bridal)
        if (style && style.trim().charAt(0).toUpperCase() === 'B') {
            addition = 60;
        } else {
            addition = 30;
        }
    }

    const finalPrice = quantity * (basePrice + addition);
   $('#price').val(finalPrice % 1 === 0 ? finalPrice : finalPrice.toFixed(2));

}

            });


            $('#style').change(function() {
                const style = $(this).val();
                if (style) {
                    $.get("/inventory/get-products/" + style, function(data) {
                        $('#sub_products').html(data);
                    });
                }
            });

        });
    </script>
@endsection
