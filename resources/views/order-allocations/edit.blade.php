@extends('layouts.app')

@section('title', 'Edit Order Allocation')

@section('content')
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <div class="row">
                                <div class="col">
                                    <form method="POST" action="{{ route('order-allocations.update', $order->order_ID) }}">
                                        @csrf
                                        @method('PUT')

                                        <h5 class="mb-0 text-uppercase">Edit Order Allocation</h5>
                                        <hr />
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="c-name">Customer Name</label>
                                                        <select required name="customers" id="customers"
                                                            class="form-control">
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
                                                        <select required name="style" id="style" class="form-control">
                                                            @foreach ($products as $prod)
                                                                <option value="{{ $prod->product_style }}"
                                                                    {{ strtolower($prod->product_style) == strtolower($order->order_product_style) ? 'selected' : '' }}>
                                                                    {{ strtoupper($prod->product_style) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label for="color">Product Color</label>
                                                        <select required name="color" id="color" class="form-control">
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
                                                                $sizeExploded = explode('-', $sizeRange ?? '');
                                                                $sizeMinRaw = isset($sizeExploded[0]) ? trim($sizeExploded[0]) : '0';
                                                                $sizeMaxRaw = isset($sizeExploded[1]) ? trim($sizeExploded[1]) : '0';
                                                                $sizeMin = (int)$sizeMinRaw;
                                                                $sizeMax = (int)$sizeMaxRaw;
                                                                $currentSize = trim((string)$order->order_product_size);
                                                                $startsWithDoubleZero = ($sizeMinRaw === '00');
                                                            @endphp

                                                            @if($startsWithDoubleZero)
                                                                <option value="00"
                                                                    @if($currentSize === '00') selected @endif>
                                                                    00
                                                                </option>
                                                            @endif

                                                            @for ($iNow = $sizeMin; $iNow <= $sizeMax; $iNow += 2)
                                                                @php
                                                                    $loopSize = (string)$iNow;
                                                                @endphp
                                                                <option value="{{ $loopSize }}"
                                                                    @if($currentSize === $loopSize) selected @endif>
                                                                    {{ $loopSize }}
                                                                </option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label for="quantity">Order Quantity</label>
                                                        <input required type="number" class="form-control" name="quantity"
                                                            id="quantity" value="{{ $order->order_quantity }}"
                                                            min="1">
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
                                                        <label for="vpID">Vendor Purchase ID</label>
                                                        <input type="text" class="form-control" name="vpID"
                                                            id="vpID" value="{{ $order->vendor_purchase_ID }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <label for="note">Note</label>
                                                        <input type="text" class="form-control" name="note"
                                                            id="note" value="{{ $order->order_note }}">
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col-6">
                                                        <label for="wearDate">Wear Date</label>
                                                        <input required type="date" name="wearDate" class="form-control"
                                                            value="{{ $order->order_wear_date }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="sub_products">Sub Products</label>
                                                            <select
                                                                class="form-control @error('sub_products') is-invalid @enderror"
                                                                id="sub_products" name="sub_products[]" multiple>
                                                                @php
                                                                    // Handle sub_products properly
                                                                    $selectedSubProducts = old('sub_products', $order->sub_products ?? []);

                                                                    // If it's a string, try to decode or convert
                                                                    if (is_string($selectedSubProducts)) {
                                                                        $decoded = json_decode($selectedSubProducts, true);
                                                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                                            $selectedSubProducts = $decoded;
                                                                        } else {
                                                                            // Try comma-separated
                                                                            $selectedSubProducts = array_map('trim', explode(',', $selectedSubProducts));
                                                                        }
                                                                    }

                                                                    // Ensure it's an array
                                                                    if (!is_array($selectedSubProducts)) {
                                                                        $selectedSubProducts = [];
                                                                    }
                                                                @endphp

                                                                @foreach ($subProducts as $sub)
                                                                    <option value="{{ $sub }}"
                                                                        {{ in_array($sub, $selectedSubProducts) ? 'selected' : '' }}>
                                                                        {{ $sub }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('sub_products')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
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

    <script>
        $(document).ready(function() {
            let costProd = {{ $costProduct }};
            let currentColor = "{{ $order->order_product_color }}".toUpperCase();
            let currentStyle = "{{ $order->order_product_style }}";

            $('#sub_products').select2({
                placeholder: "Select sub-products",
                allowClear: true
            });

            function updateColors(style, selectedColor) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('get.color') }}',
                    data: {
                        style: style,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        $('#color').html(data);
                        if (selectedColor) {
                            let colorToSelect = selectedColor.toUpperCase();
                            $('#color option').each(function() {
                                if ($(this).val().toUpperCase() === colorToSelect) {
                                    $(this).prop('selected', true);
                                    return false;
                                }
                            });
                        }
                    }
                });
            }

            function updateVendor(style) {
                let vxp = @json($vxp ?? []);
                for (let i = 0; i < vxp.length; i++) {
                    let opt = vxp[i];
                    if (opt.product_style == style) {
                        $('#vendorsName').val(opt.product_vendor_name.toUpperCase());
                        break;
                    }
                }
            }

            function updateCost(style) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('get.product.price') }}',
                    data: {
                        style: style,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        costProd = data;
                        updatePrice();
                    }
                });
            }

            function updateSizes(style) {
                if (style) {
                    $.ajax({
                        url: "{{ route('get.size') }}",
                        type: "POST",
                        dataType: "JSON",
                        data: {
                            style_get: style,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            let min_val = response.min;
                            let max_val = response.max;
                            let currentSize = "{{ $order->order_product_size }}";

                            $('#size').empty();

                            let startsWithDoubleZero = (min_val === '00');

                            if (startsWithDoubleZero) {
                                $('#size').append('<option value="00" ' +
                                    (currentSize === '00' ? 'selected' : '') + '>00</option>');
                                min_val = 0;
                            }

                            for (let i = parseInt(min_val); i <= parseInt(max_val); i += 2) {
                                let sizeValue = i.toString();
                                let isSelected = (currentSize === sizeValue) ? 'selected' : '';
                                $('#size').append('<option value="' + sizeValue + '" ' + isSelected + '>' + sizeValue + '</option>');
                            }
                        }
                    });
                }
            }

            function updatePrice() {
                const sizeProd = $('#size').val() || 0;
                const quantity = $('#quantity').val() || 0;

                if (sizeProd >= 18) {
                    $('#price').val(quantity * (parseInt(costProd) + 30));
                } else {
                    $('#price').val(quantity * costProd);
                }
            }

            $('#style').change(function() {
                const style = $(this).val();
                if (style) {
                    updateColors(style, null);
                    updateVendor(style);
                    updateCost(style);
                    updateSizes(style);

                    $.get("/inventory/get-products/" + style, function(data) {
                        let selectedSubs = @json($order->sub_products ?? []);
                        // Ensure selectedSubs is an array
                        if (typeof selectedSubs === 'string') {
                            try {
                                selectedSubs = JSON.parse(selectedSubs);
                            } catch(e) {
                                selectedSubs = selectedSubs.split(',').map(s => s.trim());
                            }
                        }
                        if (!Array.isArray(selectedSubs)) {
                            selectedSubs = [];
                        }
                        $('#sub_products').html(data);
                        if (selectedSubs.length > 0) {
                            $('#sub_products').val(selectedSubs).trigger('change');
                        }
                    });
                }
            });

            $('#quantity').on('keyup change', updatePrice);
            $('#size').on('change', updatePrice);

            if (currentStyle) {
                updateColors(currentStyle, currentColor);
                updateVendor(currentStyle);
                updateCost(currentStyle);
                updateSizes(currentStyle);
            }
        });
    </script>
@endsection
