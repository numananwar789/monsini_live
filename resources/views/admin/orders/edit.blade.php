@extends('layouts.app')

@section('title', 'Edit Order')

@section('content')
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <div class="row">
                                <div class="col">
                                    <form method="POST" action="{{ route('orders.update', $order->order_ID) }}">
                                        @csrf
                                        @method('PUT')

                                        <h5 class="mb-0 text-uppercase">Edit Order</h5>
                                        <hr />

                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="c-name">Customer Name</label>
                                                        <select required name="customers" id="customers"
                                                            class="form-control" aria-label="Default select example">
                                                            @foreach ($customers as $cust)
                                                                <option value="{{ $cust->cust_comp_name }}"
                                                                    {{ strtolower($cust->cust_comp_name) == strtolower($order->order_customer_name) ? 'selected' : '' }}>
                                                                    {{ strtoupper($cust->cust_comp_name) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label for="vendorname">Vendor Name</label>
                                                        <input readonly required type="text" class="form-control"
                                                            name="vendorsName" id="vendorsName"
                                                            value="{{ strtoupper($order->order_vendor_name) }}">
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="styles">Product Style</label>
                                                        <select required name="style" id="style"
                                                            class="form-control select2-style"
                                                            aria-label="Default select example">
                                                            <!-- Initial option will be loaded by Select2 -->
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label for="color">Product Color</label>
                                                        <select required name="color" id="color" class="form-control"
                                                            aria-label="Default select example">
                                                            @foreach ($colors as $color)
                                                                <option value="{{ trim($color) }}"
                                                                    {{ strtolower(trim($color)) == strtolower(trim($order->order_product_color)) ? 'selected' : '' }}>
                                                                    {{ strtoupper(trim($color)) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="size">Product Size</label>
                                                        <select required name="size" id="size" class="form-control"
                                                            aria-label="Default select example">

                                                            @php
                                                                $sizeExploded = explode('-', $sizeRange);

                                                                // ORIGINAL RAW VALUES
                                                                $sizeMinRaw = trim($sizeExploded[0]);
                                                                $sizeMaxRaw = trim($sizeExploded[1]);

                                                                // INTEGER VALUES
                                                                $sizeMin = (int) $sizeMinRaw;
                                                                $sizeMax = (int) $sizeMaxRaw;

                                                                // CURRENT ORDER SIZE
                                                                $currentSize = trim(
                                                                    (string) $order->order_product_size,
                                                                );

                                                                // CHECK IF RANGE STARTS WITH 00
                                                                $startsWithDoubleZero = $sizeMinRaw === '00';
                                                            @endphp

                                                            {{-- ========================================= --}}
                                                            {{-- ADD 00 OPTION --}}
                                                            {{-- ========================================= --}}
                                                            @if ($startsWithDoubleZero)
                                                                <option value="00"
                                                                    @if ($currentSize === '00') selected @endif>
                                                                    00
                                                                </option>
                                                            @endif

                                                            {{-- ========================================= --}}
                                                            {{-- NORMAL LOOP --}}
                                                            {{-- ========================================= --}}
                                                            @for ($iNow = $sizeMin; $iNow <= $sizeMax; $iNow += 2)
                                                                @php
                                                                    $loopSize = (string) $iNow;
                                                                @endphp

                                                                <option value="{{ $loopSize }}"
                                                                    @if ($currentSize === $loopSize) selected @endif>

                                                                    {{ $loopSize }}

                                                                </option>
                                                            @endfor

                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label for="quantity">Order Quantity</label>
                                                        <input required type="number" class="form-control" name="quantity"
                                                            id="quantity" value="{{ $order->order_quantity }}" min="0">
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="price">Total Price</label>
                                                        <input required type="text" class="form-control" name="price"
                                                            id="price" readonly
                                                            value="{{ $order->order_purchase_price }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <label for="note">Purchase ID</label>
                                                        <input required type="text" class="form-control"
                                                            name="purchase_id" id="purchase_id"
                                                            value="{{ $order->purchase_id }}">
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="wearDate">Wear Date</label>
                                                        <input required type="date" name="wearDate"
                                                            value="{{ $order->order_wear_date }}" class="form-control" />
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="sub_products">Sub Products</label>
                                                            <select
                                                                class="form-control @error('sub_products') is-invalid @enderror"
                                                                id="sub_products" name="sub_products[]" multiple>
                                                                @php

                                                                    $selectedSubProducts = old(
                                                                        'sub_products',
                                                                        $order->sub_products ?? [],
                                                                    );
                                                                @endphp

                                                                @foreach ($subProducts as $sub)
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
                                                        <label for="note">Note</label>
                                                        <input type="text" class="form-control" name="note"
                                                            id="note" value="{{ $order->order_note }}">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="card-block">
                                            <button type="submit" class="btn btn-primary">Update Order</button>
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

@section('page-js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
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

        /* Focus state to match Bootstrap */
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #80bdff !important;
            outline: 0 !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }

        /* Disabled/readonly state */
        .select2-container--default .select2-selection--single[aria-disabled="true"] {
            background-color: #e9ecef !important;
            opacity: 1 !important;
        }

        /* Dropdown styling */
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

        .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: #e9ecef !important;
            color: #495057 !important;
        }

        /* Fix for Select2 inside form-control */
        .select2-container {
            width: 100% !important;
            display: block !important;
        }

        /* Ensure the select wrapper matches form-control height */
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #6c757d !important;
        }

        /* Remove extra padding from select2 container */
        .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 0.5rem !important;
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

        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
            margin-top: 0 !important;
            padding: 0 !important;
        }

        /* Focus state for multiple select */
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #80bdff !important;
            outline: 0 !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }

        /* Error state for Select2 */
        .is-invalid+.select2-container--default .select2-selection--single,
        .is-invalid+.select2-container--default .select2-selection--multiple {
            border-color: #dc3545 !important;
        }

        .is-invalid+.select2-container--default.select2-container--focus .select2-selection--single,
        .is-invalid+.select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
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
                minimumInputLength: 0, // Start showing results without typing
                ajax: {
                    url: '{{ route('get.products.paginated') }}',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250, // Wait 250ms before sending request
                    data: function(params) {
                        return {
                            q: params.term || '', // Search term
                            page: params.page || 1, // Page number
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

            // Load initial selected value
            var initialStyle = '{{ $order->order_product_style }}';
            if (initialStyle) {
                var initialOption = new Option(
                    '{{ strtoupper($order->order_product_style) }}',
                    initialStyle,
                    true,
                    true
                );
                $('#style').append(initialOption).trigger('change');
            }

            let costProd = parseFloat(@json($costProduct)) || 0;

            function updatePrice() {
                const sizeProd = parseInt($('#size').val()) || 0;
                const quantity = parseInt($('#quantity').val()) || 0;
                const styleProd = $('#style').val() || '';

                let addition = 0;

                if (sizeProd >= 18) {
                    addition = styleProd.trim().toUpperCase().startsWith('B') ? 60 : 30;
                }

                $('#price').val(quantity * (costProd + addition));
            }

            // Handle style change
            $('#style').on('change', function() {
                let style_get = $(this).val();

                if (style_get) {
                    // Get vendor name from selected option data
                    let selectedOption = $(this).find('option:selected');
                    let vendorName = selectedOption.data('vendor') || '';

                    // If vendor data is not available, fetch it
                    if (!vendorName) {
                        $.ajax({
                            url: '{{ route('get.vendor.by.style') }}',
                            type: 'POST',
                            data: {
                                style: style_get,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                $('#vendorsName').val(response.vendor_name ? response
                                    .vendor_name.toUpperCase() : '');
                            }
                        });
                    } else {
                        $('#vendorsName').val(vendorName.toUpperCase());
                    }

                    // Get size range
                    $.ajax({
                        url: "{{ route('get.size') }}",
                        type: "POST",
                        dataType: "JSON",
                        data: {
                            style_get: style_get,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            let sizeSelect = $('#size');
                            sizeSelect.empty();

                            let min_val = response.min || '0';
                            let max_val = response.max || '0';

                            // Check if min is "00"
                            let hasDoubleZero = (min_val === '00');

                            // Convert to integer for loop (00 becomes 0)
                            let minNum = hasDoubleZero ? 0 : parseInt(min_val);
                            let maxNum = parseInt(max_val);

                            // Add 00 option first if it exists
                            if (hasDoubleZero) {
                                sizeSelect.append(`<option value="00">00</option>`);
                            }

                            // Add numeric sizes
                            // Start from 2 if min is 0 (since 00 is already added)
                            let startFrom = (hasDoubleZero && minNum === 0) ? 2 : minNum;

                            if (startFrom <= maxNum && maxNum > 0) {
                                // Make sure startFrom is even
                                if (startFrom % 2 !== 0) {
                                    startFrom += 1;
                                }

                                for (let i = startFrom; i <= maxNum; i += 2) {
                                    sizeSelect.append(`<option value="${i}">${i}</option>`);
                                }
                            }

                            // If no options were added, show a message
                            if (sizeSelect.find('option').length === 0) {
                                sizeSelect.append(
                                    `<option value="">No sizes available</option>`);
                            }

                            // Set selected value
                            let currentSize =
                            '{{ trim((string) $order->order_product_size) }}';
                            if (currentSize) {
                                sizeSelect.val(currentSize);
                            }

                            updatePrice();
                        },
                        error: function(xhr) {
                            console.error('Size error:', xhr.responseText);
                            $('#size').empty().append(
                                `<option value="">Error loading sizes</option>`);
                        }
                    });

                    // Get colors
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('get.color') }}',
                        data: {
                            style: style_get,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            $('#color').html(data);
                        },
                        error: function(xhr) {
                            console.error('Color error:', xhr.responseText);
                            $('#color').html('<option value="">Error loading colors</option>');
                        }
                    });

                    // Get cost
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('get.cost') }}',
                        data: {
                            style: style_get,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            costProd = parseFloat(data) || 0;
                            updatePrice();
                        }
                    });

                    // Get sub-products
                    $.get("/inventory/get-products/" + style_get, function(data) {
                        $('#sub_products').html(data);
                        $('#sub_products').select2({
                            placeholder: "Select sub-products",
                            allowClear: true
                        });
                    });
                }
            });

            // Quantity and size change handlers
            $('#quantity').on('keyup change', updatePrice);
            $('#size').on('change', updatePrice);

            // Initial price update
            updatePrice();

        });
    </script>
@endsection
