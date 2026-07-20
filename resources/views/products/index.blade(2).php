@extends('layouts.app')

@section('page-css')
    <style>
        .btn-width {
            width: 5rem;
            margin-bottom: 6px !important;
        }

        .zoom {
            transition: transform .2s;
        }

        .zoom:hover {
            transform: scale(1.5);
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
                            <div class="row">
                                <div class="col">
                                    <h5 class="mb-0 text-uppercase">All Products</h5>

                                    @if (session('success'))
                                        <div class="alert alert-success mt-3">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    @if (session('error'))
                                        <div class="alert alert-danger mt-3">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    <hr />
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <div class="d-flex justify-content-start align-items-center">
                                                    <label for="check-all" class="mb-0" style="font-size:18px"><b>Check
                                                            all the entries</b></label>
                                                    <input type="checkbox" name="check-all" id="check-all" class="ml-2">
                                                </div>
                                               
                                                    <table id="example" class="table table-striped table-bordered"
                                                        style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th>Check</th>
                                                                <th>Image</th>
                                                                <th>Style</th>
                                                                <th>F. Style</th>
                                                                <th>Color</th>
                                                                <th>Sub Products</th>
                                                                <th>Size Range</th>
                                                                <th>Total Cost</th>
                                                                <th>Total Price</th>
                                                                <th>Vendor</th>
                                                                @if (auth()->user()->admin_role === 'superadmin' || auth()->user()->user_name == 'admin1')
                                                                    <th style="width: 175px!important;">Edit/Delete</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            @foreach ($products as $product)
                                                                @php
                                                                    $prodStatusNow = \App\Models\Product::where(
                                                                        'product_style',
                                                                        $product->product_style,
                                                                    )
                                                                        ->where('product_status', 1)
                                                                        ->count();
                                                                @endphp
                                                             
                                                                    <tr>
                                                                        <td style="text-align: center;vertical-align: middle;">
                                                                            <input class="form-check-input" type="checkbox"
                                                                                value="{{ $product->product_style }}"
                                                                                id="{{ $product->product_style }}"
                                                                                name="products[]">
                                                                            <label class="form-check-label"
                                                                                for="{{ $product->product_style }}"></label>
                                                                        </td>
                                                                        <td>
                                                                            <div class="col-12 col-md-4 mx-auto"
                                                                                style="padding:0px;">
                                                                                <img src="{{ $product->product_image }}"
                                                                                    alt="" class="w-100 img-fluid zoom">
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <a target="_blank"
                                                                                href="{{ $product->product_link }}">
                                                                                {{ strtoupper($product->product_style) }}</a>
                                                                        </td>

                                                                        <td>
                                                                            {{ strtoupper($product->factory_style) }}
                                                                        </td>

                                                                        <td style="min-width: 20em;">
                                                                            @php
                                                                                $colors = \App\Models\Product::where(
                                                                                    'product_style',
                                                                                    $product->product_style,
                                                                                )->get();
                                                                            @endphp
                                                                            <select class="js-select2 form-control select_color"
                                                                                name="select_color" multiple>
                                                                                @foreach ($colors as $color)
                                                                                    <option
                                                                                        {{ $color->product_status ? 'selected' : '' }}
                                                                                        value="{{ $color->product_ID }}"
                                                                                        data-style="{{ $product->product_style }}"
                                                                                        data-colorId="{{ $color->product_ID }}">
                                                                                        {{ strtoupper($color->product_color) }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>

                                                                        <td>{{ implode(', ', $product->sub_products ?? []) }}</td>
                                                                        <td>{{ $product->product_size_range }}</td>
                                                                        <td>{{ $product->product_cost }}</td>
                                                                        <td>{{ $product->product_wholesale_price }}</td>
                                                                        <td>{{ strtoupper($product->product_vendor_name) }}
                                                                        </td>
                                                                        @if (auth()->user()->admin_role == 'superadmin' || auth()->user()->user_name == 'admin1')
                                                                            <td class="text-center d-flex flex-wrap">
                                                                                <a target="_self"
                                                                                    id="edit_page_url{{ $product->product_style }}"
                                                                                    class="btn btn-success mb-0 btn-sm edit_product btn-width"
                                                                                    href="{{ route('products.edit', $product->product_ID) }}">
                                                                                    Edit
                                                                                </a>
                                                                                    <form id="productForm" method="POST"
                                                                                        action="{{ route('admin-products.action') }}">
                                                                                        @csrf
                                                                                        <input type="text" style="display:none"
                                                                                            name="prodID"
                                                                                            value="{{ $product->product_style }}">

                                                                                        <input type="submit" name="action"
                                                                                            class="btn btn-danger mb-0 btn-sm btn-width"
                                                                                            value="Delete" />

                                                                                        @if ($prodStatusNow >= 1)
                                                                                            <input type="submit" name="action"
                                                                                                class="btn btn-success mb-0 btn-sm btn-width"
                                                                                                value="Active" />
                                                                                        @elseif($prodStatusNow == 0)
                                                                                            <input type="submit" name="action"
                                                                                                class="btn btn-warning mb-0 btn-sm btn-width"
                                                                                                value="Inactive" />
                                                                                        @endif
                                                                                        
                                                                                        <!--<label>-->
                                                                                        <!--    <input type="checkbox" class="toggle-inventory-override"-->
                                                                                        <!--        data-style="{{ $product->product_style }}"-->
                                                                                        <!--        {{ $product->inventory_override ? 'checked' : '' }}>-->
                                                                                        <!--    Show from Inventory-->
                                                                                        <!--</label>-->
                                                                                        
                                                                                        <label>
                                                                                            <input type="checkbox"
                                                                                                class="toggle-inventory-override"
                                                                                                data-style="{{ $product->product_style }}"
                                                                                                {{ $product->inventory_override ? 'checked' : '' }}
                                                                                                {{ $prodStatusNow >= 1 ? 'disabled' : '' }}>
                                                                                            Show from Inventory
                                                                                        </label>

                                                                                        <input name="styleNumber"
                                                                                            value="{{ $product->product_style }}"
                                                                                            hidden />

                                                                                     </form>
                                                                            </td>
                                                                        @endif
                                                                    </tr>
                                                               
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                           
                                            </div>
                                        </div>
                                    </div>

                                    @if (auth()->user()->admin_role == 'superadmin' || auth()->user()->user_name == 'admin1' || true)
                                        <div class="card-block">
                                            <a class="btn btn-primary" id="add-products-order"
                                                href="{{ route('products.create') }}">Add New Product</a>
                                            <a class="btn btn-primary text-white" data-toggle="modal"
                                                data-target="#importModal">Import Products</a>
                                            <a href="{{ route('admin-products.download') }}"
                                                class="btn btn-success float-right">Download Product Data</a>

                                            <button type="button" onclick="xpandTablePrint()"
                                                class="btn btn-warning float-right" data-toggle="modal"
                                                data-target="#archiveModal">Archive Data</button>

                                                <a href="{{ route('sub-products.index') }}"
                                                class="btn btn-success float-right">Sub Products</a>
                                        </div>
                                    @endif

                                    <!-- Import Modal -->
                                    <div class="modal fade" id="importModal" tabindex="-1" role="dialog"
                                        aria-labelledby="importModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="importModalLabel">Upload Excel File</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="POST" action="{{ route('admin-products.import') }}"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="productFile">Upload File</label>
                                                            <input name="file" type="file"
                                                                class="form-control-file" id="productFile"
                                                                accept=".xls,.xlsx" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Submit</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal -->
                                    <div class="modal fade" id="archiveModal" tabindex="-1"
                                        aria-labelledby="archiveModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="archiveModalLabel">Archive Products</h5>
                                                    <button type="submit" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="archive-name" class="col-form-label">Archive
                                                            Name:</label>
                                                        <input type="text" class="form-control" id="archive-name"
                                                            name="archive-name">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button id='profileclick' onclick="xpandTablePrintNew()"
                                                        type="submit" class="btn btn-primary">Save</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Modal -->

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
    <!-- <script src="assets/js/vendor-all.min.js"></script> -->
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
        crossorigin="anonymous"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <script src="/assets/js/pcoded.min.js"></script> --}}
    <!-- amchart js -->
    <script src="/assets/plugins/amchart/js/amcharts.js"></script>
    <script src="/assets/plugins/amchart/js/gauge.js"></script>
    <script src="/assets/plugins/amchart/js/serial.js"></script>
    <script src="/assets/plugins/amchart/js/light.js"></script>
    <script src="/assets/plugins/amchart/js/pie.min.js"></script>
    <script src="/assets/plugins/amchart/js/ammap.min.js"></script>
    <script src="/assets/plugins/amchart/js/usaLow.js"></script>
    <script src="/assets/plugins/amchart/js/radar.js"></script>
    <script src="/assets/plugins/amchart/js/worldLow.js"></script>
    <!-- notification Js -->
    <script src="/assets/plugins/notification/js/bootstrap-growl.min.js"></script>

    <!-- dashboard-custom js -->
    <script src="/assets/js/pages/dashboard-custom.js"></script>
    <script src="/assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>

    <!-- datatable date range links -->
    <!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>

    <!-- toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>


    {{-- <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
<script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script> --}}

    <script>
        function xpandTable() {
            table.page.len(-1).draw();
        }

        function xpandTablePrint() {
            var elementsNew = document.getElementsByClassName("myClass");
            Array.from(elementsNew).forEach(function(elementInput) {
                elementInput.value = '';
            });
            $('.myClass').trigger('keyup');
            table.page.len(-1).draw();
        }

        function xpandTablePrintNew() {
            var elementsNew = document.getElementsByClassName("myClass");
            Array.from(elementsNew).forEach(function(elementInput) {
                elementInput.value = '';
            });
            $('.myClass').trigger('keyup');
            table.page.len(-1).draw();

            // Create an array to store the selected values
            var selectedItems = [];
            $('input[name="products[]"]:checked').each(function() {
                selectedItems.push($(this).val());
            });

            //get archive name
            var archiveName = $('#archive-name').val();
            var token = $('meta[name="csrf-token"]').attr('content');
            // Make an AJAX request to the PHP script
            $.ajax({
                url: '/products/archive',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: {
                    selectedItems: selectedItems,
                    archiveName: archiveName

                },
                success: function(response) {
                    alert("Archived Successfully!")
                    window.location.reload();
                    // console.log(response);
                    // Perform any additional actions or update the UI as needed
                },
                error: function(xhr, status, error) {
                    // Handle the error scenario if the AJAX request fails
                    // console.log(xhr.responseText);
                    alert(xhr.responseText);
                }
            });
        }


        $(document).ready(function() {
            // Initialize DataTable
            table = $('#example').DataTable({
                aLengthMenu: [
                    [10, 25, 50, 100, 200, -1],
                    [10, 25, 50, 100, 200, "All"]
                ],
                dom: 'lBfrtip',
                buttons: [{
                    extend: 'print',
                    autoPrint: true,
                    text: 'Print',
                    exportOptions: {
                        columns: function(idx, data, node) {
                            if (node.innerHTML == "Edit/Delete" || node.innerHTML == "Image")
                                return false;
                            return true;
                        }
                    },
                    customize: function(win) {
                        $(win.document.body).css('font-size', '11pt');
                        $(win.document.body).find('table').addClass('compact').css('font-size',
                            'inherit');

                        var css = '@page { size: landscape; }',
                            head = win.document.head || win.document.getElementsByTagName(
                                'head')[0],
                            style = win.document.createElement('style');

                        style.type = 'text/css';
                        style.media = 'print';

                        if (style.styleSheet) {
                            style.styleSheet.cssText = css;
                        } else {
                            style.appendChild(win.document.createTextNode(css));
                        }

                        head.appendChild(style);
                    }
                }]
            });

            // Initialize Select2
            function initSelect2() {
                $(".js-select2").select2({
                    closeOnSelect: false,
                    placeholder: "Colors",
                    allowHtml: true,
                    allowClear: true,
                    tags: false
                });


                            // Activate/deactivate products via AJAX
                $('.js-select2').on('select2:select', function(e) {
                    var prodID = e.params.data.id;
                    var colorProd = e.params.data.text;

                    $.ajax({
                        url: "{{ route('admin-products.action') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: prodID,
                            action: "Active"
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success('Color ' + colorProd + ' has been activated',
                                    'Activated', {
                                        progressBar: true,
                                        closeHtml: '<button type="button">&times;</button>',
                                        newestOnTop: true,
                                    });
                            }
                        }
                    });
                });

                $('.js-select2').on('select2:unselect', function(e) {
                    var prodID = e.params.data.id;
                    var colorProd = e.params.data.text;

                    $.ajax({
                        url: "{{ route('admin-products.action') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: prodID,
                            action: "Inactive"
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.error('Color ' + colorProd + ' has been de-activated',
                                    'Deactivated', {
                                        progressBar: true,
                                        closeHtml: '<button type="button">&times;</button>',
                                        newestOnTop: true,
                                    });
                            }
                        }
                    });
                });
            }

            // Run once on document ready
            initSelect2();

            table.on('draw.dt', function () {
                 console.log("On Sort");
                initSelect2();
            });

            
            // table.on('sort', function () {
            //     console.log("On Sort");
            //     initSelect2();
            // });

            // Check all functionality
            $('#check-all').click(function(event) {
                if (this.checked) {
                    $(':checkbox').each(function() {
                        this.checked = true;
                    });
                } else {
                    $(':checkbox').each(function() {
                        this.checked = false;
                    });
                }
            });
            
            // $('.toggle-inventory-override').change(function () {
            //     let style = $(this).data('style');
            //     let status = $(this).is(':checked') ? 1 : 0;
            
            //     $.ajax({
            //         url: "{{ route('admin-products.toggle-inventory') }}",
            //         method: "POST",
            //         data: {
            //             _token: "{{ csrf_token() }}",
            //             style: style,
            //             status: status
            //         },
            //         success: function (response) {
            //             if (status === 1) {
            //                 toastr.success('Show from Inventory enabled');
            //             } else {
            //                 toastr.warning('Show from Inventory disabled');
            //             }
            //         },
            //         error: function () {
            //             toastr.error('Something went wrong');
            //         }
            //     });
            // });
            
            $(document).on('change', '.toggle-inventory-override', function () {

    let style = $(this).data('style');
    let status = $(this).is(':checked') ? 1 : 0;

    $.ajax({
        url: "{{ route('admin-products.toggle-inventory') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            style: style,
            status: status
        },
        success: function (response) {

            if (status === 1) {
                toastr.success('Show from Inventory enabled');
            } else {
                toastr.warning('Show from Inventory disabled');
            }

        },
        error: function () {
            toastr.error('Something went wrong');
        }
    });

});
        });
    </script>
@endsection
