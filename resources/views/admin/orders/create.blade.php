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
                                                        <select required name="style" id="style"
                                                            class="form-control select2-style">
                                                            <!-- Options will be loaded by Select2 -->
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
                                                        <select name="sub_products[]" multiple id="sub_products"
                                                            class="form-control">
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

        /* Fix Select2 to match Bootstrap form-control */
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
            padding: 0.375rem 0.75rem !important;
            font-size: 1rem !important;
            font-weight: 400 !important;
            line-height: 1.5 !important;
            color: #495057 !important;
            background-color: #fff !important;
            background-clip: padding-box !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #495057 !important;
            line-height: 1.5 !important;
            padding: 0 !important;
            flex: 1 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 0.75rem !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #495057 transparent transparent transparent !important;
        }

        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #80bdff !important;
            outline: 0 !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }

        .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            padding: 0.375rem 0.75rem !important;
            font-size: 1rem !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #80bdff !important;
            outline: 0 !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff !important;
            color: #fff !important;
        }

        .select2-container {
            width: 100% !important;
            display: block !important;
        }

        /* For multiple select (sub_products) */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            padding: 0.375rem 0.75rem !important;
            min-height: calc(2.25rem + 2px) !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff !important;
            border-color: #006fe6 !important;
            color: #fff !important;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
            border-radius: 0.25rem !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff !important;
            margin-right: 0.25rem !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ff0000 !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #80bdff !important;
            outline: 0 !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }
    </style>

    <script>
        $(document).ready(function() {

            // Initialize Select2 for sub_products
            $('#sub_products').select2({
                placeholder: "Select sub-products",
                allowClear: true
            });

            // Initialize Select2 for product style with pagination and search
            $('.select2-style').select2({
                placeholder: "Search for a product style...",
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('get.products.paginated') }}',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1,
                            _token: '{{ csrf_token() }}'
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results || [],
                            pagination: {
                                more: data.pagination ? data.pagination.more : false
                            }
                        };
                    },
                    cache: false
                },
                templateResult: formatStyleOption,
                templateSelection: formatStyleSelection
            });

            // Custom formatting for Select2 options
            function formatStyleOption(option) {
                if (!option.id) {
                    return option.text;
                }
                return $('<span><strong>' + option.text + '</strong></span>');
            }

            function formatStyleSelection(option) {
                return option.text || option.id;
            }

            let costProd = 0;

            // Handle style change
            $('#style').on('change', function() {
                const style = $(this).val();

                if (style) {
                    // Get vendor name from vxp data
                    const vxp = @json($vendorProductList);
                    const vendor = vxp.find(item => item.product_style === style);
                    if (vendor) {
                        $('#vendorsName').val(vendor.product_vendor_name.toUpperCase());
                    }

                    // Get colors
                    $.get("/admin/orders/get-colors/" + style, function(data) {
                        $('#color').html(data);
                    });

                    // Get sizes
                    $.get("/admin/orders/get-sizes/" + style, function(data) {
                        $('#size').html(data);
                    });

                    // Get cost
                    $.get("/admin/orders/get-cost/" + style, function(data) {
                        costProd = parseFloat(data) || 0;
                    });

                    // Get sub-products
                    $.get("/inventory/get-products/" + style, function(data) {
                        $('#sub_products').html(data);
                        $('#sub_products').select2({
                            placeholder: "Select sub-products",
                            allowClear: true
                        });
                    });
                }
            });

            // Calculate price when quantity or size changes
            $('#quantity, #size').on('keyup change', function() {
                const quantity = parseInt($('#quantity').val()) || 0;
                const size = $('#size').val();
                const style = $('#style').val();

                if (quantity && size && costProd) {
                    let basePrice = parseFloat(costProd);
                    let addition = 0;

                    // Apply size-based logic
                    if (parseInt(size) >= 18) {
                        // Check if style starts with "B" (Bridal)
                        if (style && style.trim().charAt(0).toUpperCase() === 'B') {
                            addition = 60;
                        } else {
                            addition = 30;
                        }
                    }

                    const finalPrice = quantity * (basePrice + addition);
                    $('#price').val(finalPrice % 1 === 0 ? finalPrice : finalPrice.toFixed(2));
                } else {
                    $('#price').val('NA');
                }
            });

        });
    </script>
@endsection
