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

        .dropdown-scroll {
            max-height: 300px;
            overflow-y: auto;
        }

        .dropdown-scroll>div {
            padding: 12px;
        }

        .dropdown-scroll .d-flex {
            padding: 8px 4px;
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
                                                        {{-- Rows are now loaded via AJAX by DataTables (serverSide),
                                                             see the getProductsData() endpoint. Nothing rendered here. --}}
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


                                    <hr>

                                    <h5 class="mt-4 mb-3">Year Publishing Control</h5>

                                    <div class="dropdown">

                                        <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                            id="yearDropdown" data-toggle="dropdown" aria-expanded="false">

                                            Manage Year Publishing

                                        </button>

                                        <div class="dropdown-menu p-3" aria-labelledby="yearDropdown"
                                            style="min-width: 350px;">


                                            <div class="dropdown-scroll">
                                                @foreach ($years as $year)
                                                    <div class="d-flex justify-content-between align-items-center mb-3">

                                                        <div>
                                                            <strong>Azure {{ $year->year }}</strong>
                                                            <br>

                                                            <small class="text-muted">
                                                                {{ $year->count }} Products
                                                            </small>
                                                        </div>

                                                        <div class="text-right">

                                                            @if ($year->is_published)
                                                                <span class="badge badge-success mb-1">
                                                                    Published
                                                                </span>
                                                            @else
                                                                <span class="badge badge-secondary mb-1">
                                                                    Hidden
                                                                </span>
                                                            @endif

                                                            <br>

                                                            <label class="mb-0">

                                                                <input type="checkbox" class="toggle-year-checkbox"
                                                                    data-year="{{ $year->year }}"
                                                                    {{ $year->is_published ? 'checked' : '' }}>

                                                                Publish

                                                            </label>

                                                        </div>

                                                    </div>

                                                    @if (!$loop->last)
                                                        <div class="dropdown-divider"></div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

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
                                                            <input name="file" type="file" class="form-control-file"
                                                                id="productFile" accept=".xls,.xlsx" required>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

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

    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>

    <!-- toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

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

            var selectedItems = [];
            $('input[name="products[]"]:checked').each(function() {
                selectedItems.push($(this).val());
            });

            var archiveName = $('#archive-name').val();
            var token = $('meta[name="csrf-token"]').attr('content');

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
                },
                error: function(xhr, status, error) {
                    alert(xhr.responseText);
                }
            });
        }

        // Re-attach select2 activate/deactivate handlers. Delegated on
        // document so it works for rows injected by DataTables' ajax draw.
        function bindSelect2Handlers() {
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

        $(document).ready(function() {

            var canEdit =
                {{ auth()->user()->admin_role === 'superadmin' || auth()->user()->user_name == 'admin1' ? 'true' : 'false' }};

            var columns = [{
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'image',
                    name: 'image',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'style',
                    name: 'product_style'
                },
                {
                    data: 'factory_style',
                    name: 'factory_style'
                },
                {
                    data: 'color',
                    name: 'product_color',
                    orderable: false
                },
                {
                    data: 'sub_products',
                    name: 'sub_products',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'size_range',
                    name: 'product_size_range'
                },
                {
                    data: 'cost',
                    name: 'product_cost'
                },
                {
                    data: 'price',
                    name: 'product_wholesale_price'
                },
                {
                    data: 'vendor',
                    name: 'product_vendor_name'
                },
            ];

            if (canEdit) {
                columns.push({
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                });
            }

            // Initialize DataTable with server-side processing.
            // Rows, paging, search, and sorting are now all handled
            // by the getProductsData() endpoint instead of being
            // rendered up-front for every product on page load.
            table = $('#example').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin-products.datatable') }}",
                    type: 'GET'
                },
                columns: columns,
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
                }],
                drawCallback: function() {
                    $(".js-select2").select2({
                        closeOnSelect: false,
                        placeholder: "Colors",
                        allowHtml: true,
                        allowClear: true,
                        tags: false
                    });
                    bindSelect2Handlers();
                }
            });

            // Check all functionality — only affects checkboxes currently
            // rendered on the page (the DataTables-standard behaviour).
            $('#check-all').click(function(event) {
                var checked = this.checked;
                $('input[name="products[]"]').each(function() {
                    this.checked = checked;
                });
            });

            $(document).on('change', '.toggle-inventory-override', function() {

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
                    success: function(response) {

                        if (status === 1) {
                            toastr.success('Show from Inventory enabled');
                        } else {
                            toastr.warning('Show from Inventory disabled');
                        }

                    },
                    error: function() {
                        toastr.error('Something went wrong');
                    }
                });

            });
        });

        $(document).on('change', '.toggle-year-checkbox', function() {

            let checkbox = $(this);

            let year = checkbox.data('year');

            let status = checkbox.is(':checked') ? 1 : 0;

            $.ajax({
                url: "{{ route('admin-products.toggle-year') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    year: year,
                    status: status
                },

                success: function(res) {

                    toastr.success(res.message);

                    let badge = checkbox.closest('.text-right').find('.badge');

                    if (status === 1) {

                        badge
                            .removeClass('badge-secondary')
                            .addClass('badge-success')
                            .text('Published');

                    } else {

                        badge
                            .removeClass('badge-success')
                            .addClass('badge-secondary')
                            .text('Hidden');
                    }
                },

                error: function() {

                    toastr.error('Failed to update year status');

                    checkbox.prop('checked', !status);
                }
            });

        });
    </script>
@endsection
