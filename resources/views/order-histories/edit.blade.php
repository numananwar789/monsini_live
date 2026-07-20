@extends('layouts.app')

@section('page-css')
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/datetime/1.1.2/css/dataTables.dateTime.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet" />
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
@endsection



@section('content')


    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <!-- [ Main Content ] start -->
                            <div class="row">
                                <div class="col">
                                    <h5 class="mb-0 text-uppercase">History</h5>

                                    @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    @if (session('errors'))
                                        <div class="alert alert-danger">
                                            <strong>These orders failed:</strong>
                                            <ul>
                                                @foreach (session('errors') as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <hr />
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="table-responsive">




                                                <div class="row">
                                                    <div class="col">
                                                        <form method="post"
                                                            action="{{ route('order-histories.update', $order->order_ID) }}">
                                                            @csrf
                                                            @method('PUT')

                                                            <h5 class="mb-0 text-uppercase">Edit Order History</h5>
                                                            <hr />
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row mt-4">
                                                                        <div class="col">
                                                                            <label for="c-name">Customer Name</label>
                                                                            <select required name="customers" id="customers"
                                                                                class="form-control"
                                                                                aria-label="Default select example">
                                                                                @foreach ($customers as $cust)
                                                                                    <option
                                                                                        value="{{ $cust->cust_comp_name }}"
                                                                                        {{ strtolower($cust->cust_comp_name) == strtolower($order->order_customer_name) ? 'selected' : '' }}>
                                                                                        {{ strtoupper($cust->cust_comp_name) }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col">
                                                                            <label for="vendorname">Vendor Name</label>
                                                                            <input readonly required type="text"
                                                                                class="form-control" name="vendorsName"
                                                                                id="vendorsName"
                                                                                value="{{ strtoupper($order->order_vendor_name) }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="row mt-4">
                                                                        <div class="col">
                                                                            <label for="styles">Product Style</label>
                                                                            <select required name="style" id="style"
                                                                                class="form-control"
                                                                                aria-label="Default select example">
                                                                                @foreach ($products as $prod)
                                                                                    <option
                                                                                        value="{{ $prod->product_style }}"
                                                                                        {{ strtolower($prod->product_style) == strtolower($order->order_product_style) ? 'selected' : '' }}>
                                                                                        {{ strtoupper($prod->product_style) }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col">
                                                                            <label for="color">Product Color</label>
                                                                            <select required name="color" id="color"
                                                                                class="form-control"
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
    aria-label="Default select example" disabled="">

@php
    $sizeExploded = explode('-', $sizeRange ?? '');

    $sizeMinRaw = isset($sizeExploded[0]) ? trim($sizeExploded[0]) : '0';
    $sizeMaxRaw = isset($sizeExploded[1]) ? trim($sizeExploded[1]) : '0';

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
                                                                            <input required type="number"
                                                                                class="form-control" name="quantity"
                                                                                id="quantity"
                                                                                value="{{ $order->order_quantity }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="row mt-4">
                                                                        <div class="col">
                                                                            <label for="price">Total Price</label>
                                                                            <input required type="text"
                                                                                class="form-control" name="price"
                                                                                id="price" readonly
                                                                                value="{{ $order->order_purchase_price }}">
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <label for="note">Purchase ID</label>
                                                                            <input required type="text"
                                                                                class="form-control" name="purchase_id"
                                                                                id="purchase_id"
                                                                                value="{{ $order->purchase_id }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="row mt-4">
                                                                        <div class="col">
                                                                            <label for="vpID">Vendor Purchase ID</label>
                                                                            <input type="text" class="form-control"
                                                                                name="vpID" id="vpID"
                                                                                value="{{ $order->vendor_purchase_ID }}">
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="sub_products">Sub
                                                                                    Products</label>
                                                                                <select
                                                                                    class="form-control @error('sub_products') is-invalid @enderror"
                                                                                    id="sub_products" name="sub_products[]"
                                                                                    multiple>
                                                                                    @php

                                                                                        $selectedSubProducts = old(
                                                                                            'sub_products',
                                                                                            $order->sub_products ?? [],
                                                                                        );
                                                                                    @endphp

                                                                                    @foreach ($subProducts as $sub)
                                                                                        <option
                                                                                            value="{{ $sub }}"
                                                                                            {{ in_array($sub, $selectedSubProducts ?? []) ? 'selected' : '' }}>
                                                                                            {{ $sub }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                                @error('sub_products')
                                                                                    <div class="invalid-feedback">
                                                                                        {{ $message }}</div>
                                                                                @enderror
                                                                            </div>
                                                                        </div>


                                                                    </div>

                                                                    <div class="col-6">
                                                                        <label for="note">Note</label>
                                                                        <input type="text" class="form-control"
                                                                            name="note" id="note"
                                                                            value="{{ $order->order_note }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-block">
                                                                <input class="btn btn-primary" type="submit"
                                                                    id="add-products-order" value="Edit Order" />
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>



                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- [ Main Content ] end -->
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
            var costProd = {{ $costProduct ?? 0 }};


            $('#sub_products').select2({
                placeholder: "Select sub-products",
                allowClear: true
            });

            // Get colors on style change
            $('#style').change(function() {
                $.ajax({
                    url: "{{ route('get.color') }}",
                    type: "POST",
                    data: {
                        style: $(this).val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        $('#color').html(data);
                    }
                });
            });

            // Get vendor name on style change
            $('#style').change(function() {
                const vxp = @json($vendorProducts);
                for (let i = 0; i < vxp.length; i++) {
                    const opt = vxp[i];
                    if (opt["product_style"] == $(this).val()) {
                        $('#vendorsName').val(opt["product_vendor_name"].toUpperCase());
                        break;
                    }
                }
            });

            // Get cost on style change        
            $('#style').change(function() {
                $.ajax({
                    url: "{{ route('get.cost') }}",
                    type: "POST",
                    data: {
                        style: $(this).val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        costProd = data;
                    }
                });
            });

            // Get sizes on style change
            $('#style').change(function() {
                const style_get = $('#style').val();
                if (style_get != null) {
                    $.ajax({
                        url: "{{ route('get.size') }}",
                        type: "POST",
                        dataType: "JSON",
                        data: {
                            style_get: style_get,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            const min_val = response.min;
                            const max_val = response.max;
                            $('#size').empty().append(`<option value="">Choose Size</option>`);
                            for (let i = parseInt(min_val); i <= parseInt(max_val); i += 2) {
                                $('#size').append(`<option value="${i}">${i}</option>`);
                            }
                        }
                    });
                }
            });

            // Change wholesale price on quantity change
            $('#quantity').keyup(function() {
                const sizeProd = $('#size').val();
                if (sizeProd >= 18) {
                    $('#price').val($('#quantity').val() * (parseInt(costProd) + 30));
                } else if (sizeProd < 18) {
                    $('#price').val($('#quantity').val() * costProd);
                }
            });

            // Change whole sale on size change if above 18
            $('#size').change(function() {
                const sizeProd = $('#size').val();
                if (sizeProd >= 18) {
                    $('#price').val($('#quantity').val() * (parseInt(costProd) + 30));
                } else if (sizeProd < 18) {
                    $('#price').val($('#quantity').val() * costProd);
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
