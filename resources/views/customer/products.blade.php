@extends('layouts.customer')

@section('title', 'Products')

@section('styles')
    <style>
        .in-td {
            width: 200px;
            margin: 0 auto;
        }
    </style>

@endsection
@section('content')
    <div class="row">
        <div class="col">
            <h5 class="mb-0 text-uppercase">All Products</h5>
            <hr />
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <script type="text/javascript">
                            const custsizefunc = (object) => {
                                // // alert($(object).closest("tr").find("td:eq(4)").text());

                                // //get previous price
                                // var prevPrice = $(object).closest("td").next().text();

                                // //change pruice in td text
                                // object.value >= 18 ? $(object).closest("td").next().text(parseInt(prevPrice) + 30) : $(object).closest("td").next().text(prevPrice);

                                // //change pruice in td input
                                // // console.log($(object).closest("tr").next());
                            }
                        </script>
                        <form id="orderForm" method="post" action="{{ route('customer.products.store') }}">
                            @csrf
                            <table id="example" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Style</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Sub Products</th>
                                        <th>Price</th>
                                        <th>Availability</th>
                                        <th>Purchase</th>
                                        <th>Purchase ID</th>
                                        <th>Wear Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $previousValue = 'NA';
                                    @endphp
                                    @foreach ($products as $product)
                                        @php
                                            $onwayCountLoop = DB::table('dt_order_allocation')
                                                ->where('order_customer_name', $ownerComp)
                                                ->where('order_product_style', $product->product_style)
                                                ->sum('order_quantity');
                                        @endphp
                                        <tr>
                                            <td>
                                                <input type="text" class="d-none" name="prod_style[]"
                                                    value="{{ $product->product_style }}">
                                                <div class="col-12 col-md-4 mx-auto" style="padding:0px;">
                                                    <img src="{{ $product->product_image }}" alt=""
                                                        class="w-100 img-fluid zoom">
                                                </div>
                                            </td>

                                            <td>
                                                <a target="_blank" href="{{ $product->product_link }}">
                                                    {{ strtoupper($product->product_style) }}</a>
                                            </td>

                                            <td style="min-width:7em;">
                                                @php
                                                    $colors = [];

                                                    if ($product->use_inventory) {
                                                        $colors = !empty($product->in_stock_colors)
                                                            ? array_values(
                                                                array_filter(
                                                                    array_map(
                                                                        'trim',
                                                                        explode(',', $product->in_stock_colors),
                                                                    ),
                                                                ),
                                                            )
                                                            : [];
                                                    } else {
                                                        $colors = !empty($product->all_colors)
                                                            ? array_values(
                                                                array_filter(
                                                                    array_map(
                                                                        'trim',
                                                                        explode(',', $product->all_colors),
                                                                    ),
                                                                ),
                                                            )
                                                            : [];
                                                    }
                                                @endphp

                                                <select class="form-control color_select" name="color[]">
                                                    <option value="">Color</option>

                                                    @foreach ($colors as $color)
                                                        <option value="{{ $color }}">
                                                            {{ strtoupper($color) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td style="min-width:7em;">

                                                @php

                                                    $colorSizeMap = [];

                                                    // ==========================================
                                                    // INVENTORY MODE
                                                    // ==========================================
                                                    if ($product->use_inventory) {
                                                        $colors = !empty($product->in_stock_colors)
                                                            ? array_values(
                                                                array_filter(
                                                                    array_map(
                                                                        'trim',
                                                                        explode(',', $product->in_stock_colors),
                                                                    ),
                                                                ),
                                                            )
                                                            : [];

                                                        $sizes = !empty($product->in_stock_sizes)
                                                            ? array_values(
                                                                array_filter(
                                                                    array_map(
                                                                        'trim',
                                                                        explode(',', $product->in_stock_sizes),
                                                                    ),
                                                                ),
                                                            )
                                                            : [];

                                                        foreach ($colors as $index => $color) {
                                                            if (isset($sizes[$index])) {
                                                                $colorSizeMap[$color] = $sizes[$index];
                                                            }
                                                        }
                                                    }

                                                    // ==========================================
                                                    // NORMAL PRODUCT MODE
                                                    // ==========================================
                                                    else {
                                                        $rangeSizes = [];

                                                        if (!empty($product->all_sizes)) {
                                                            $range = explode('-', $product->all_sizes);

                                                            $min = isset($range[0]) ? (int) $range[0] : 0;
                                                            $max = isset($range[1]) ? (int) $range[1] : 0;

                                                            $originalMin = isset($range[0]) ? trim($range[0]) : '0';

                                                            // ==========================================
                                                            // CASE: 00-26
                                                            // Output: 00,0,2,4...
                                                            // ==========================================
                                                            if ($originalMin === '00') {
                                                                $rangeSizes[] = '00';

                                                                for ($i = 0; $i <= $max; $i += 2) {
                                                                    // skip duplicate 0 because already added as 00
                                                                    if ($i == 0) {
                                                                        $rangeSizes[] = '0';
                                                                    } else {
                                                                        $rangeSizes[] = (string) $i;
                                                                    }
                                                                }
                                                            }

                                                            // ==========================================
                                                            // CASE: 0-26
                                                            // Output: 0,2,4...
                                                            // ==========================================
                                                            else {
                                                                for ($i = $min; $i <= $max; $i += 2) {
                                                                    $rangeSizes[] = (string) $i;
                                                                }
                                                            }
                                                        }

                                                        foreach ($colors as $color) {
                                                            $colorSizeMap[$color] = $rangeSizes;
                                                        }
                                                    }

                                                @endphp

                                                <input type="hidden" class="color-size-map"
                                                    value='@json($colorSizeMap)'>

                                                <select class="form-control size_select" name="size[]">
                                                    <option value="">Size</option>
                                                </select>

                                            </td>


                                            <td>
                                                @if (!empty($product->sub_products))
                                                    @foreach ($product->sub_products as $subProduct)
                                                        <div class="form-check">
                                                            <input type="checkbox"
                                                                name="sub_products[{{ $product->product_style }}][]"
                                                                value="{{ $subProduct }}"
                                                                id="subProduct_{{ $product->product_style }}_{{ Str::slug($subProduct) }}"
                                                                class="form-check-input">
                                                            <label
                                                                for="subProduct_{{ $product->product_style }}_{{ Str::slug($subProduct) }}"
                                                                class="form-check-label">
                                                                {{ $subProduct }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <span>-</span>
                                                @endif
                                            </td>



                                            <td>
                                                {{ $product->product_wholesale_price }}
                                            </td>

                                            @php
                                                $style = '';

                                                // USE REAL INVENTORY COUNT FOR BOTH CASES
                                                $total_products =
                                                    (int) $product->inventory_count + (int) $product->onway_count;

                                                // AVAILABLE / OUT OF STOCK
                                                if ($total_products > 0) {
                                                    $order_type = 'Available';

                                                    $style = 'background:#65a765; color:white;';
                                                } else {
                                                    $order_type = 'Out of Stock';
                                                }
                                            @endphp

                                            <td style="{{ $style }}">
                                                {{ $total_products }}: {{ $order_type }}

                                                @if (!$product->use_inventory || $total_products > 0)
                                                    <button type="button" class="btn btn-success btn-block my-2"
                                                        @if ($total_products <= 0) disabled
                                                        @else
                                                            onclick="load_cat($(this))" @endif
                                                        data-id="{{ $product->product_style }}">
                                                        View
                                                    </button>
                                                @endif
                                            </td>

                                            <td>
                                                <div class="input-group in-td" style="width:10rem;">
                                                    <span class="input-group-prepend">
                                                        <button type="button" class="btn btn-outline-secondary btn-number"
                                                            disabled="disabled" data-type="minus"
                                                            data-field="{{ $product->product_ID }}">
                                                            <span class="fa fa-minus"></span>
                                                        </button>
                                                    </span>

                                                    <input type="text" readonly id="quantOrig"
                                                        name="{{ $product->product_ID }}" class="form-control input-number"
                                                        value="0" min="0" max="50000">

                                                    <input type="text" hidden name="quantsOrder[]"
                                                        id="orderQuants{{ $product->product_ID }}"
                                                        class="form-control order_class" value="0">

                                                    <span class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary btn-number"
                                                            data-type="plus" data-field="{{ $product->product_ID }}">
                                                            <span class="fa fa-plus"></span>
                                                        </button>
                                                    </span>
                                                </div>
                                            </td>

                                            <td>
                                                <input type="text" name="purchase_id[]" class="form-control">
                                            </td>
                                            <td>
                                                <input type="date" onkeydown="return false" name="wearDate[]"
                                                    class="form-control" min="{{ date('Y-m-d', strtotime('tomorrow')) }}">
                                            </td>
                                            <td>
                                                <input class="btn btn-primary" type="submit" value="Place"
                                                    form="orderForm" onclick="xpandTable()">
                                            </td>
                                        </tr>
                                        @php $previousValue = $product->product_style; @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Image</th>
                                        <th>Style</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Sub Products</th>
                                        <th>Price</th>
                                        <th>Availability</th>
                                        <th>Purchase</th>
                                        <th>Purchase ID</th>
                                        <th>Wear Date</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-block">
                <input class="btn btn-primary" type="submit" value="Place Orders" form="orderForm" onclick="xpandTable()">
            </div>
        </div>
    </div>

    <!-- The Modal -->
    <div class="modal" id="myModal">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" id="myTitle">Modal Heading</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body table-responsive">

                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-scripts')
    <script type="text/javascript">
        function addtocart() {
            // tableMine.page.len(-1).draw();
        }

        function xpandTable() {
            tableMine.page.len(-1).draw();
        }

        var tableMine;

        $(document).ready(function() {
            tableMine = $('#example').DataTable({
                aLengthMenu: [
                    [10, 25, 50, 100, 200, -1],
                    [10, 25, 50, 100, 200, "All"]
                ],
            });

            @if (session('error'))
                alert("{{ session('error') }}");
            @endif
        });

        $(document).ready(function() {
            var get_cookie_str = "{{ Cookie::get('key_val') }}";
            get_cookie_arrx = get_cookie_str.split("-");
            var input_number = $('.input-number');

            $.each(input_number, function(index, element) {
                if (get_cookie_arrx[index] > 0) {
                    $(this).val(get_cookie_arrx[index]);
                    $(this).closest("tr").find('.order_class').val(get_cookie_arrx[index]);
                }

                if (input_number.length - 1 == index) {
                    document.cookie = "key_val=null; path=/";
                }
            });
        });

        // function load_cat(obj) {
        //     var id = obj.attr('data-id');
        //     $.ajax({
        //         url: "{{ route('customer.products.popup') }}",
        //         type: "POST",
        //         data: {
        //             id: id,
        //             _token: "{{ csrf_token() }}"
        //         },
        //         dataType: "JSON",
        //         success: function(response) {
        //             if (response.products.length > 0) {
        //                 $('.modal-body').empty();

        //                 $.each(response.products, function(index, element) {
        //                     var element_arrx = element[3].split("-");
        //                     var min_val = element_arrx[0];
        //                     var max_val = element_arrx[1];
        //                     var table_load = "<tr>";
        //                     var table_load1 = "<tr>";
        //                     var table_load2 = "<tr>";

        //                     table_load += `<th></th>`;
        //                     table_load1 += `<td> in stock </td>`;
        //                     table_load2 += `<td> on way </td>`;

        //                     for (var i = parseInt(min_val); i <= parseInt(max_val); i += 2) {
        //                         let match1 = false;
        //                         let match2 = false;
        //                         table_load += `<th>${i}</th>`;

        //                         $.each(response.stock, function(indx, elements) {
        //                             if (elements["product_size"].trim() == i && elements["product_color"].trim() == element[2].trim()) {
        //                                 match1 = true;
        //                                 if (elements["final_quantity"] > 0) {
        //                                     table_load1 += `<td><p>${elements["final_quantity"]}</p></td>`;
        //                                 } else {
        //                                     table_load1 += `<td></td>`;
        //                                 }
        //                             }
        //                         });

        //                         $.each(response.on_way, function(on_way_indx, on_way_elements) {
        //                             if (on_way_elements['order_product_size'].trim() == i && on_way_elements['order_product_color'].trim() == element[2].trim()) {
        //                                 match2 = true;
        //                                 if (on_way_elements['order_quantity'] > 0) {
        //                                     table_load2 += `<td><p>${on_way_elements['order_quantity']}</p></td>`;
        //                                 } else {
        //                                     table_load2 += `<td></td>`;
        //                                 }
        //                             }
        //                         });

        //                         if (!match1) {
        //                             table_load1 += `<td></td>`;
        //                         }
        //                         if (!match2) {
        //                             table_load2 += `<td></td>`;
        //                         }
        //                     }
        //                     table_load += "</tr>";
        //                     table_load1 += "</tr>";
        //                     table_load2 += "</tr>";

        //                     $('.modal-body').append(`
    //                         <div class="row">
    //                             <div class="col-md-12">
    //                                 <h5>${element[2]}</h5>
    //                             </div>
    //                             <div class="col-md-12">
    //                             <table class='table'>
    //                                 ${table_load}
    //                                 ${table_load1}
    //                                 ${table_load2}
    //                             </table>
    //                             </div>
    //                         </div>
    //                         `);
        //                 });
        //                 $('#myTitle').html("Style#: " + response.products[0][1]);
        //                 $('#myModal').modal('show');
        //             }
        //         }
        //     });
        // }

        // 14-05-26
        // function load_cat(obj) {
        //     var id = obj.attr('data-id');

        //     $.ajax({
        //         url: "{{ route('customer.products.popup') }}",
        //         type: "POST",
        //         data: {
        //             id: id,
        //             _token: "{{ csrf_token() }}"
        //         },
        //         dataType: "JSON",
        //         success: function(response) {

        //             console.log('=== POPUP DEBUG ===');
        //             console.log('Mode:', response.mode);
        //             console.log('Products:', response.products);
        //             console.log('Stock:', response.stock);
        //             console.log('On Way:', response.on_way);
        //             console.log('Sizes:', response.sizes);
        //             console.log('Colors:', response.colors);

        //             if (response.stock.length === 0 && response.products.length === 0) {
        //                 console.warn('Both stock and products are empty — modal will not open.');
        //                 return;
        //             }

        //             $('.modal-body').empty();

        //             // ============================================
        //             // CASE: INVENTORY MODE (Inactive + Override)
        //             // ============================================
        //             if (response.mode === 'inventory') {

        //                 // FIXED: SORT SIZES LOW -> HIGH
        //                 response.sizes.sort(function(a, b) {
        //                     return parseInt(a) - parseInt(b);
        //                 });

        //                 $.each(response.colors, function(index, color) {

        //                     let table_head  = "<tr><th></th>";
        //                     let table_stock = "<tr><td>In Stock</td>";
        //                     let table_onway = "<tr><td>On Way</td>";

        //                     $.each(response.sizes, function(i, size) {

        //                         let stockQty = '';
        //                         let onWayQty = '';

        //                         $.each(response.stock, function(_, item) {
        //                             if (item.product_size == size && item.product_color == color) {
        //                                 stockQty = item.final_quantity;
        //                                 console.log(`[INVENTORY] Matched stock — color: ${color}, size: ${size}, qty: ${stockQty}`);
        //                             }
        //                         });

        //                         $.each(response.on_way, function(_, item) {
        //                             if (item.order_product_size == size && item.order_product_color == color) {
        //                                 onWayQty = item.order_quantity;
        //                                 console.log(`[INVENTORY] Matched on_way — color: ${color}, size: ${size}, qty: ${onWayQty}`);
        //                             }
        //                         });

        //                         table_head  += `<th>${size}</th>`;
        //                         table_stock += `<td>${stockQty}</td>`;
        //                         table_onway += `<td>${onWayQty}</td>`;
        //                     });

        //                     table_head  += "</tr>";
        //                     table_stock += "</tr>";
        //                     table_onway += "</tr>";

        //                     $('.modal-body').append(`
    //                         <div class="row">
    //                             <div class="col-md-12"><h5>${color}</h5></div>
    //                             <div class="col-md-12">
    //                                 <table class="table">
    //                                     ${table_head}
    //                                     ${table_stock}
    //                                     ${table_onway}
    //                                 </table>
    //                             </div>
    //                         </div>
    //                     `);
        //                 });

        //                 $('#myTitle').html("Style#: " + id);
        //                 $('#myModal').modal('show');
        //             }

        //             // ============================================
        //             // CASE: NORMAL MODE (Active product)
        //             // ============================================
        //             else {

        //                 $.each(response.products, function(index, element) {

        //                     console.log(`[NORMAL] Product:`, element);

        //                     var element_arrx = element[3].split("-");
        //                     var min_val = parseInt(element_arrx[0]);
        //                     var max_val = parseInt(element_arrx[1]);

        //                     console.log(`[NORMAL] Size range: ${min_val} - ${max_val}`);

        //                     var table_head  = "<tr><th></th>";
        //                     var table_stock = "<tr><td>In Stock</td>";
        //                     var table_onway = "<tr><td>On Way</td>";

        //                     for (var i = min_val; i <= max_val; i += 2) {

        //                         let stockQty = '';
        //                         let onWayQty = '';

        //                         $.each(response.stock, function(_, item) {
        //                             if (item.product_size == i && item.product_color.trim() == element[2].trim()) {
        //                                 stockQty = item.final_quantity;
        //                                 console.log(`[NORMAL] Matched stock — color: ${element[2]}, size: ${i}, qty: ${stockQty}`);
        //                             }
        //                         });

        //                         $.each(response.on_way, function(_, item) {
        //                             if (item.order_product_size == i && item.order_product_color.trim() == element[2].trim()) {
        //                                 onWayQty = item.order_quantity;
        //                                 console.log(`[NORMAL] Matched on_way — color: ${element[2]}, size: ${i}, qty: ${onWayQty}`);
        //                             }
        //                         });

        //                         table_head  += `<th>${i}</th>`;
        //                         table_stock += `<td>${stockQty}</td>`;
        //                         table_onway += `<td>${onWayQty}</td>`;
        //                     }

        //                     table_head  += "</tr>";
        //                     table_stock += "</tr>";
        //                     table_onway += "</tr>";

        //                     $('.modal-body').append(`
    //                         <div class="row">
    //                             <div class="col-md-12"><h5>${element[2]}</h5></div>
    //                             <div class="col-md-12">
    //                                 <table class="table">
    //                                     ${table_head}
    //                                     ${table_stock}
    //                                     ${table_onway}
    //                                 </table>
    //                             </div>
    //                         </div>
    //                     `);
        //                 });

        //                 $('#myTitle').html("Style#: " + response.products[0][1]);
        //                 $('#myModal').modal('show');
        //             }
        //         }
        //     });
        // }

        function load_cat(obj) {

            var id = obj.attr('data-id');

            $.ajax({

                url: "{{ route('customer.products.popup') }}",

                type: "POST",

                data: {
                    id: id,
                    _token: "{{ csrf_token() }}"
                },

                dataType: "JSON",

                success: function(response) {

                    console.log('=== POPUP DEBUG ===');
                    console.log('Mode:', response.mode);
                    console.log('Products:', response.products);
                    console.log('Stock:', response.stock);
                    console.log('On Way:', response.on_way);
                    console.log('Sizes:', response.sizes);
                    console.log('Colors:', response.colors);

                    if (response.stock.length === 0 && response.products.length === 0) {

                        console.warn('Both stock and products are empty — modal will not open.');

                        return;
                    }

                    $('.modal-body').empty();

                    // =========================================================
                    // INVENTORY MODE
                    // =========================================================
                    if (response.mode === 'inventory') {

                        // SORT SIZES LOW -> HIGH
                        response.sizes.sort(function(a, b) {

                            // KEEP 00 FIRST
                            if (a === '00') return -1;
                            if (b === '00') return 1;

                            return parseInt(a) - parseInt(b);
                        });

                        $.each(response.colors, function(index, color) {

                            let table_head = "<tr><th></th>";
                            let table_stock = "<tr><td>In Stock</td>";
                            let table_onway = "<tr><td>On Way</td>";

                            $.each(response.sizes, function(i, size) {

                                let stockQty = '';
                                let onWayQty = '';

                                $.each(response.stock, function(_, item) {

                                    if (
                                        String(item.product_size) === String(size) &&
                                        item.product_color.trim() == color.trim()
                                    ) {

                                        stockQty = item.final_quantity;

                                        console.log(
                                            `[INVENTORY] Matched stock — color: ${color}, size: ${size}, qty: ${stockQty}`
                                        );
                                    }
                                });

                                $.each(response.on_way, function(_, item) {

                                    if (
                                        String(item.order_product_size) === String(
                                            size) &&
                                        item.order_product_color.trim() == color.trim()
                                    ) {

                                        onWayQty = item.order_quantity;

                                        console.log(
                                            `[INVENTORY] Matched on_way — color: ${color}, size: ${size}, qty: ${onWayQty}`
                                        );
                                    }
                                });

                                table_head += `<th>${size}</th>`;
                                table_stock += `<td>${stockQty}</td>`;
                                table_onway += `<td>${onWayQty}</td>`;
                            });

                            table_head += "</tr>";
                            table_stock += "</tr>";
                            table_onway += "</tr>";

                            $('.modal-body').append(`

                        <div class="row">

                            <div class="col-md-12">
                                <h5>${color}</h5>
                            </div>

                            <div class="col-md-12">

                                <table class="table">

                                    ${table_head}
                                    ${table_stock}
                                    ${table_onway}

                                </table>

                            </div>

                        </div>

                    `);
                        });

                        $('#myTitle').html("Style#: " + id);

                        $('#myModal').modal('show');
                    }

                    // =========================================================
                    // NORMAL MODE
                    // =========================================================
                    else {

                        $.each(response.products, function(index, element) {

                            console.log(`[NORMAL] Product:`, element);

                            var element_arrx = element[3].split("-");

                            // RAW VALUES
                            var min_raw = element_arrx[0].trim();
                            var max_raw = element_arrx[1].trim();

                            // INTEGER VALUES
                            var min_val = parseInt(min_raw);
                            var max_val = parseInt(max_raw);

                            // CHECK IF RANGE STARTS WITH 00
                            var startsWithDoubleZero = (min_raw === '00');

                            console.log(
                                `[NORMAL] Size range: ${min_raw} - ${max_raw}`
                            );

                            var table_head = "<tr><th></th>";
                            var table_stock = "<tr><td>In Stock</td>";
                            var table_onway = "<tr><td>On Way</td>";

                            // =====================================================
                            // HANDLE 00 FIRST
                            // =====================================================
                            if (startsWithDoubleZero) {

                                let stockQty = '';
                                let onWayQty = '';

                                $.each(response.stock, function(_, item) {

                                    if (
                                        String(item.product_size) === '00' &&
                                        item.product_color.trim() == element[2].trim()
                                    ) {

                                        stockQty = item.final_quantity;

                                        console.log(
                                            `[NORMAL] Matched stock — color: ${element[2]}, size: 00, qty: ${stockQty}`
                                        );
                                    }
                                });

                                $.each(response.on_way, function(_, item) {

                                    if (
                                        String(item.order_product_size) === '00' &&
                                        item.order_product_color.trim() == element[2].trim()
                                    ) {

                                        onWayQty = item.order_quantity;

                                        console.log(
                                            `[NORMAL] Matched on_way — color: ${element[2]}, size: 00, qty: ${onWayQty}`
                                        );
                                    }
                                });

                                table_head += `<th>00</th>`;
                                table_stock += `<td>${stockQty}</td>`;
                                table_onway += `<td>${onWayQty}</td>`;
                            }

                            // =====================================================
                            // NORMAL LOOP
                            // =====================================================
                            for (var i = min_val; i <= max_val; i += 2) {

                                let stockQty = '';
                                let onWayQty = '';

                                $.each(response.stock, function(_, item) {

                                    if (
                                        String(item.product_size) === String(i) &&
                                        item.product_color.trim() == element[2].trim()
                                    ) {

                                        stockQty = item.final_quantity;

                                        console.log(
                                            `[NORMAL] Matched stock — color: ${element[2]}, size: ${i}, qty: ${stockQty}`
                                        );
                                    }
                                });

                                $.each(response.on_way, function(_, item) {

                                    if (
                                        String(item.order_product_size) === String(i) &&
                                        item.order_product_color.trim() == element[2].trim()
                                    ) {

                                        onWayQty = item.order_quantity;

                                        console.log(
                                            `[NORMAL] Matched on_way — color: ${element[2]}, size: ${i}, qty: ${onWayQty}`
                                        );
                                    }
                                });

                                table_head += `<th>${i}</th>`;
                                table_stock += `<td>${stockQty}</td>`;
                                table_onway += `<td>${onWayQty}</td>`;
                            }

                            table_head += "</tr>";
                            table_stock += "</tr>";
                            table_onway += "</tr>";

                            $('.modal-body').append(`

                        <div class="row">

                            <div class="col-md-12">
                                <h5>${element[2]}</h5>
                            </div>

                            <div class="col-md-12">

                                <table class="table">

                                    ${table_head}
                                    ${table_stock}
                                    ${table_onway}

                                </table>

                            </div>

                        </div>

                    `);
                        });

                        $('#myTitle').html("Style#: " + response.products[0][1]);

                        $('#myModal').modal('show');
                    }
                }
            });
        }

        var btn_nmbr = document.querySelectorAll('.btn-number');

        btn_nmbr.forEach(e => {
            e.addEventListener('click', function(event) {
                fieldName = $(this).attr('data-field');
                type = $(this).attr('data-type');
                var input = $("input[name='" + fieldName + "']");
                var currentVal = parseInt(input.val());

                console.log(currentVal);
                if (!isNaN(currentVal)) {
                    if (type == 'minus') {
                        if (currentVal > input.attr('min')) {
                            input.val(currentVal - 1).change();
                            $('#orderQuants' + fieldName).val(currentVal - 1).change();
                        }
                        if (parseInt(input.val()) == input.attr('min')) {
                            $(this).attr('disabled', true);
                        }
                    } else if (type == 'plus') {
                        if (currentVal < input.attr('max')) {
                            input.val(currentVal + 1).change();
                            $('#orderQuants' + fieldName).val(currentVal + 1).change();
                        }
                        if (parseInt(input.val()) == input.attr('max')) {
                            $(this).attr('disabled', true);
                        }
                    }
                } else {
                    input.val(0);
                }
            })
        });

        $('.input-number').focusin(function() {
            $(this).data('oldValue', $(this).val());
        });

        $('.input-number').change(function() {
            minValue = parseInt($(this).attr('min'));
            maxValue = parseInt($(this).attr('max'));
            valueCurrent = parseInt($(this).val());

            name = $(this).attr('name');
            if (valueCurrent >= minValue) {
                $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
            } else {
                alert('Sorry, the minimum value was reached');
                $(this).val($(this).data('oldValue'));
            }
            if (valueCurrent <= maxValue) {
                $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
            } else {
                alert('Sorry, the maximum value was reached');
                $(this).val($(this).data('oldValue'));
            }
        });

        $(".input-number").keydown(function(e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

        var custorder = new Array();
        $(document).ready(function() {
            var quantOrig = document.querySelectorAll('#quantOrig');
            $('#quantOrig').bind("change", function() {
                custorder.push($(this).attr('name'), $(this).val());
            });
        });

        function showNow() {
            console.log(custorder);
        }


        // ==========================================
        // COLOR -> SIZE DYNAMIC DROPDOWN
        // ==========================================

        $(document).on('change', '.color_select', function() {

            let row = $(this).closest('tr');

            let selectedColor = $(this).val();

            let sizeDropdown = row.find('.size_select');

            let colorSizeMap = JSON.parse(
                row.find('.color-size-map').val()
            );

            sizeDropdown.html('<option value="">Size</option>');

            if (
                selectedColor &&
                colorSizeMap[selectedColor]
            ) {

                let sizes = colorSizeMap[selectedColor];

                // ======================================
                // SINGLE SIZE
                // ======================================
                if (!Array.isArray(sizes)) {

                    sizeDropdown.append(
                        `<option value="${sizes}">${sizes}</option>`
                    );

                }

                // ======================================
                // MULTIPLE SIZES
                // ======================================
                else {

                    $.each(sizes, function(index, size) {

                        sizeDropdown.append(
                            `<option value="${size}">${size}</option>`
                        );

                    });

                }
            }
        });
    </script>

@endsection
