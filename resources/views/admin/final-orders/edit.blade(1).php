@extends('layouts.app')

@section('title', 'Edit Final Order')

@section('content')
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <div class="row">
                                <div class="col">
                                    <form method="POST" action="{{ route('final-orders.update', $order->final_ID) }}">
                                        @csrf
                                        @method('PUT')

                                        <h5 class="mb-0 text-uppercase">Edit Final Order</h5>
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
                                                                    {{ strtolower($cust->cust_ID) == strtolower($order->order_customer_ID) ? 'selected' : '' }}>
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
                                                        <select required name="style" id="style" class="form-control"
                                                            aria-label="Default select example">
                                                            <option value="">Select Style </option>
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
                                                        <select required name="color" id="color" class="form-control"
                                                            aria-label="Default select example">
                                                            <option value="">Select Color </option>
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
                                                                $sizeMin = $sizeExploded[0];
                                                                $sizeMax = $sizeExploded[1];
                                                            @endphp
                                                            @for ($iNow = $sizeMin; $iNow <= $sizeMax; $iNow += 2)
                                                                <option value="{{ $iNow }}"
                                                                    {{ $iNow == $order->order_product_size ? 'selected' : '' }}>
                                                                    {{ $iNow }}
                                                                </option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label for="quantity">Order Quantity</label>
                                                        <input required type="number" class="form-control" name="quantity"
                                                            id="quantity" value="{{ $order->order_quantity }}">
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


                                                </div>

                                                <div class="col">
                                                    <label for="note">Note</label>
                                                    <input type="text" class="form-control" name="note" id="note"
                                                        value="{{ $order->order_note }}">
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

    $('#sub_products').select2({
        placeholder: "Select sub-products",
        allowClear: true
    });

    // parse cost as float
    let costProd = parseFloat({{ $costProduct }});

    // Get colors on style change
    $('#style').change(function() {
        $.ajax({
            type: 'POST',
            url: '{{ route('get.color') }}',
            data: {
                style: $(this).val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                $('#color').html(data);
            }
        });
    });

    // Get vendor name on style change
    $('#style').change(function() {
        let vxp = @json($vxp);
        for (let i = 0; i < vxp.length; i++) {
            let opt = vxp[i];
            if (opt.product_style == $(this).val()) {
                $('#vendorsName').val(opt.product_vendor_name.toUpperCase());
                break;
            }
        }
    });

    // Get cost on style change
    $('#style').change(function() {
        $.ajax({
            type: 'POST',
            url: '{{ route('get.product.price') }}',
            data: {
                style: $(this).val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                costProd = parseFloat(data);
                updatePrice();
            }
        });
    });

    // Get sizes on style change
    $('#style').change(function() {
        let style_get = $('#style').val();
        if (style_get != null) {
            $.ajax({
                url: "{{ route('get.size') }}",
                type: "POST",
                dataType: "JSON",
                data: {
                    style_get: style_get,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    let sizeRange = Array.isArray(response) ? response[0] : response;
                    let min_val = sizeRange.min;
                    let max_val = sizeRange.max;

                    $('#size').empty().append(`<option value="">Choose Size</option>`);
                    for (let i = parseInt(min_val); i <= parseInt(max_val); i += 2) {
                        $('#size').append(`<option value="${i}">${i}</option>`);
                    }
                }
            });
        }
    });

    // ===== Updated Price Calculation =====
    function updatePrice() {
        const style = ($('#style').val() || '').trim();
        const sizeProd = parseFloat($('#size').val() || 0);
        const quantity = parseFloat($('#quantity').val() || 0);
        const base = isNaN(costProd) ? 0 : parseFloat(costProd);

        if (!quantity || !base) {
            $('#price').val(0);
            return;
        }

        let addition = 0;
        if (sizeProd >= 18) {
            // Bridal styles start with B → +60; others → +30
            addition = (style.charAt(0).toUpperCase() === 'B') ? 60 : 30;
        }

        const total = quantity * (base + addition);
        const formatted = (total % 1 === 0) ? total : total.toFixed(2);
        $('#price').val(formatted);
    }

    $('#quantity').on('keyup change', updatePrice);
    $('#size').on('change', updatePrice);
    $('#style').on('change', updatePrice);

    // Load sub-products when style changes
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


<!--@section('page-js')-->

<!--    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>-->
<!--    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />-->

<!--    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}-->
<!--    <script>-->
<!--        $(document).ready(function() {-->

<!--            $('#sub_products').select2({-->
<!--                placeholder: "Select sub-products",-->
<!--                allowClear: true-->
<!--            });-->

<!--            let costProd = {{ $costProduct }};-->

            // Get colors on style change
<!--            $('#style').change(function() {-->
<!--                $.ajax({-->
<!--                    type: 'POST',-->
<!--                    url: '{{ route('get.color') }}',-->
<!--                    data: {-->
<!--                        style: $(this).val(),-->
<!--                        _token: '{{ csrf_token() }}'-->
<!--                    },-->
<!--                    success: function(data) {-->
<!--                        $('#color').html(data);-->
<!--                    }-->
<!--                });-->
<!--            });-->

            // Get vendor name on style change
<!--            $('#style').change(function() {-->
<!--                let vxp = @json($vxp);-->
<!--                for (let i = 0; i < vxp.length; i++) {-->
<!--                    let opt = vxp[i];-->
<!--                    if (opt.product_style == $(this).val()) {-->
<!--                        $('#vendorsName').val(opt.product_vendor_name.toUpperCase());-->
<!--                        break;-->
<!--                    }-->
<!--                }-->
<!--            });-->

            // Get cost on style change        
<!--            $('#style').change(function() {-->
<!--                $.ajax({-->
<!--                    type: 'POST',-->
<!--                    url: '{{ route('get.product.price') }}',-->
<!--                    data: {-->
<!--                        style: $(this).val(),-->
<!--                        _token: '{{ csrf_token() }}'-->
<!--                    },-->
<!--                    success: function(data) {-->
<!--                        costProd = data;-->
<!--                    }-->
<!--                });-->
<!--            });-->

            // Get sizes on style change
<!--            $('#style').change(function() {-->
<!--                let style_get = $('#style').val();-->
<!--                if (style_get != null) {-->
<!--                    $.ajax({-->
<!--                        url: "{{ route('get.size') }}",-->
<!--                        type: "POST",-->
<!--                        dataType: "JSON",-->
<!--                        data: {-->
<!--                            style_get: style_get,-->
<!--                            _token: '{{ csrf_token() }}'-->
<!--                        },-->
<!--                        success: function(response) {-->
<!--                            let sizeRange = Array.isArray(response) ? response[0] : response;-->
<!--                            let min_val = sizeRange.min;-->
<!--                            let max_val = sizeRange.max;-->

<!--                            $('#size').empty().append(`<option value="">Choose Size</option>`);-->
<!--                            for (let i = parseInt(min_val); i <= parseInt(max_val); i += 2) {-->
<!--                                $('#size').append(`<option value="${i}">${i}</option>`);-->
<!--                            }-->
<!--                        }-->
<!--                    });-->
<!--                }-->
<!--            });-->

            // Change price on quantity or size change
<!--            function updatePrice() {-->
<!--                const sizeProd = $('#size').val();-->
<!--                const quantity = $('#quantity').val() || 0;-->

<!--                if (sizeProd >= 18) {-->
<!--                    $('#price').val(quantity * (parseInt(costProd) + 30));-->
<!--                } else if (sizeProd < 18) {-->
<!--                    $('#price').val(quantity * costProd);-->
<!--                }-->
<!--            }-->

<!--            $('#quantity').keyup(updatePrice);-->
<!--            $('#size').change(updatePrice);-->
            // $('#style').trigger('change');

<!--            $('#style').change(function() {-->
<!--                const style = $(this).val();-->
<!--                if (style) {-->
<!--                    $.get("/inventory/get-products/" + style, function(data) {-->
<!--                        $('#sub_products').html(data);-->
<!--                    });-->
<!--                }-->
<!--            });-->
<!--        });-->
<!--    </script>-->
<!--@endsection-->
