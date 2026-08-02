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
                                                        <select required name="style" id="style" class="form-control"
                                                            aria-label="Default select example">
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
        $sizeMin = (int)$sizeMinRaw;
        $sizeMax = (int)$sizeMaxRaw;

        // CURRENT ORDER SIZE
        $currentSize = trim((string)$order->order_product_size);

        // CHECK IF RANGE STARTS WITH 00
        $startsWithDoubleZero = ($sizeMinRaw === '00');
    @endphp

    {{-- ========================================= --}}
    {{-- ADD 00 OPTION --}}
    {{-- ========================================= --}}
    @if($startsWithDoubleZero)

        <option value="00"
            @if($currentSize === '00') selected @endif>
            00
        </option>

    @endif

    {{-- ========================================= --}}
    {{-- NORMAL LOOP --}}
    {{-- ========================================= --}}
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

                                                    <div class="col-md-6">
                                                        <label for="note">Note</label>
                                                        <input type="text" class="form-control" name="note" id="note"
                                                            value="{{ $order->order_note }}">
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

        $('#sub_products').select2({
            placeholder: "Select sub-products",
            allowClear: true
        });

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

                        if (String(min_val).trim() === '00') {
                            $('#size').append(`<option value="00">00</option>`);
                            min_val = 0;
                        }

                        for (let i = parseInt(min_val); i <= parseInt(max_val); i += 2) {
                            $('#size').append(`<option value="${i}">${i}</option>`);
                        }

                        updatePrice();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            }

            $.ajax({
                type: 'POST',
                url: '{{ route('get.color') }}',
                data: {
                    style: $(this).val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $('#color').html(data);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    $('#color').html('<option value="">Error loading colors</option>');
                }
            });

            $.ajax({
                type: 'POST',
                url: '{{ route('get.cost') }}',
                data: {
                    style: $(this).val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    costProd = parseFloat(data) || 0;
                    updatePrice();
                }
            });

            const style = $(this).val();
            if (style) {
                $.get("/inventory/get-products/" + style, function(data) {
                    $('#sub_products').html(data);
                });
            }
        });

        $('#quantity').on('keyup change', updatePrice);
        $('#size').on('change', updatePrice);

        updatePrice();

    });
</script>
@endsection
