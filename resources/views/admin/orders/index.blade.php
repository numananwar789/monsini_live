@extends('layouts.app')

@section('page-css')
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/datetime/1.1.2/css/dataTables.dateTime.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet" />
    <!-- Add Scroller CSS -->
    <link rel="stylesheet" href="/assets/plugins/datatable/css/scroller.dataTables.min.css">

    <style>
        .dt-buttons {
            margin-left: 20px !important;
        }

        .dataTables_wrapper {
            margin-top: 5px;
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
                                    <h5 class="mb-0 text-uppercase">All Orders</h5>

                                    @if (session('success'))
                                        <div class="alert alert-success mt-3">
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
                                                <div class="d-flex justify-content-start align-items-center mb-3">
                                                    <label for="check-all" class="mb-0" style="font-size:18px">
                                                        <b>Check all the entries</b>
                                                    </label>
                                                    <input type="checkbox" name="check-all" id="check-all" class="ml-2">
                                                    <span id="selection-count" class="ml-3 text-muted"></span>
                                                </div>

                                                <form id="orderForm" method="post" action="">
                                                    @csrf
                                                    <table id="example" class="table table-striped table-bordered"
                                                        style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 40px;">Check</th>
                                                                <th style="width: 80px;">Order ID</th>
                                                                <th style="width: 120px;">Order GUID</th>
                                                                <th style="width: 100px;">Purchase ID</th>
                                                                <th style="width: 100px;">Place Date</th>
                                                                <th style="width: 120px;">Customer</th>
                                                                <th style="width: 100px;">Vendor</th>
                                                                <th style="width: 100px;">Style</th>
                                                                <th style="width: 80px;">Color</th>
                                                                <th style="width: 120px;">Sub Products</th>
                                                                <th style="width: 60px;">Size</th>
                                                                <th style="width: 70px;">Quantity</th>
                                                                <th style="width: 100px;">Wear Date</th>
                                                                <th style="width: 80px;">From Inventory</th>
                                                                <th style="width: 80px;">From Onway</th>
                                                                <th style="width: 90px;">Total Cost</th>
                                                                <th style="width: 90px;">Total Price</th>
                                                                <th style="width: 100px;">Status</th>
                                                                <th style="width: 80px;">User</th>
                                                                <th style="width: 120px;">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {{-- Rows loaded via AJAX by DataTables (serverSide) --}}
                                                        </tbody>
                                                    </table>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-block">
                                        <a class="btn btn-primary" id="add-order" href="{{ route('orders.create') }}">Add
                                            New Order</a>
                                        <a class="btn btn-primary text-white" data-toggle="modal"
                                            data-target="#importModal">Import Orders</a>

                                        <button type="button" class="btn btn-primary" onclick="confirmOrders()">
                                            Accept Customer Orders
                                        </button>

                                        <a href="{{ route('orders.export.pending') }}" class="btn btn-success float-right"
                                            style="color:#fff;">Download Order Data</a>
                                        <a href="{{ route('orders.refresh') }}" class="btn btn-warning float-right"
                                            style="color:#fff;">Refresh Orders</a>
                                        <button type="submit" form="orderForm" formaction="{{ route('orders.cancel') }}"
                                            class="btn btn-warning float-right"
                                            style="color:#fff; background:#f8631d">Cancel Orders</button>
                                    </div>

                                    <!-- Import Modal -->
                                    <div class="modal fade" id="importModal" tabindex="-1" role="dialog"
                                        aria-labelledby="importModalTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="importModalTitle">Upload Excel File</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="POST" action="{{ route('orders.import') }}"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="orderFile">Upload File</label>
                                                            <input name="file" type="file"
                                                                class="form-control-file" id="orderFile"
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
                                </div>
                            </div>
                            <!-- [ Main Content ] end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="conflictModal" tabindex="-1" aria-labelledby="conflictModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="conflictModalLabel">⚠️ Inventory Allocation Warning</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p>If you proceed, the selected orders will be allocated from <strong>inventory</strong>.
                        However, the <strong>sub-products (add-ons)</strong> are not in stock and must be purchased
                        separately from the vendor.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>GUID</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Sub Products</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="conflictOrdersTable"></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitOrderForm()">Proceed Anyway</button>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
@endsection

@section('page-js')
    @if (session('success') == 'Orders refreshed successfully')
        <script>
            window.location.reload();
        </script>
    @endif
    <script src="/assets/plugins/notification/js/bootstrap-growl.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>

    <!-- Scroller (virtual scrolling) -->
    <link rel="stylesheet" href="/assets/plugins/datatable/css/scroller.dataTables.min.css">
    <script src="/assets/plugins/datatable/js/dataTables.scroller.min.js"></script>

    <!-- toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
        // Populated via AJAX from getFlaggedOrders() once on page load
        let flaggedOrders = [];

        // Track selected orders
        var selectedOrders = new Set();
        var excludedOrders = new Set();
        var selectAllMatching = false;
        var table;
        var scrollerMode = false;
        var lastSearchTerm = '';

        function updateSelectionCount() {
            var count;
            var info = table ? table.page.info() : null;
            var totalRecords = info ? info.recordsDisplay : 0;

            if (selectAllMatching) {
                count = totalRecords - excludedOrders.size;
            } else {
                count = selectedOrders.size;
            }

            $('#selection-count').text(count > 0 ? count.toLocaleString() + ' selected' : '');
        }

        function confirmOrders() {
            let selected = selectAllMatching ?
                Array.from({
                    length: table.page.info().recordsDisplay
                }, (_, i) => i + 1) :
                Array.from(selectedOrders);

            let conflicts = flaggedOrders.filter(o => selected.includes(parseInt(o.id)));

            if (conflicts.length > 0) {
                let tbody = document.getElementById("conflictOrdersTable");
                tbody.innerHTML = "";
                conflicts.forEach(o => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${o.id}</td>
                            <td>${o.guid}</td>
                            <td>${o.style}</td>
                            <td>${o.color}</td>
                            <td>${o.subs}</td>
                            <td>${o.qty}</td>
                        </tr>`;
                });

                let modal = new bootstrap.Modal(document.getElementById('conflictModal'));
                modal.show();
            } else {
                submitOrderForm();
            }
        }

        function submitOrderForm() {
            let form = document.getElementById('orderForm');

            if (selectAllMatching) {
                // Send filter mode instead of individual IDs
                let search = table.search() || '';
                let excluded = Array.from(excludedOrders);

                // Add hidden inputs for select-all mode
                let selectAllInput = document.createElement('input');
                selectAllInput.type = 'hidden';
                selectAllInput.name = 'select_all';
                selectAllInput.value = '1';
                form.appendChild(selectAllInput);

                let searchInput = document.createElement('input');
                searchInput.type = 'hidden';
                searchInput.name = 'search';
                searchInput.value = search;
                form.appendChild(searchInput);

                let excludedInput = document.createElement('input');
                excludedInput.type = 'hidden';
                excludedInput.name = 'excluded';
                excludedInput.value = JSON.stringify(excluded);
                form.appendChild(excludedInput);
            }

            form.action = "{{ route('orders.accept') }}";
            form.submit();
        }

        $(document).ready(function() {

            // Fetch flagged orders
            $.getJSON("{{ route('orders.flagged') }}", function(data) {
                flaggedOrders = data;
            });

            function tableConfig(useScroller, pageLength) {
                var config = {
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('orders.datatable') }}",
                        type: 'GET'
                    },
                    columns: [{
                            data: 'checkbox',
                            name: 'checkbox',
                            orderable: false,
                            searchable: false,
                            width: '40px'
                        },
                        {
                            data: 'order_id',
                            name: 'order_ID',
                            width: '80px'
                        },
                        {
                            data: 'order_guid',
                            name: 'order_GUID',
                            width: '120px'
                        },
                        {
                            data: 'purchase_id',
                            name: 'purchase_id',
                            width: '100px'
                        },
                        {
                            data: 'place_date',
                            name: 'created_at',
                            width: '100px'
                        },
                        {
                            data: 'customer',
                            name: 'order_customer_name',
                            width: '120px'
                        },
                        {
                            data: 'vendor',
                            name: 'order_vendor_name',
                            width: '100px'
                        },
                        {
                            data: 'style',
                            name: 'order_product_style',
                            width: '100px'
                        },
                        {
                            data: 'color',
                            name: 'order_product_color',
                            width: '80px'
                        },
                        {
                            data: 'sub_products',
                            name: 'sub_products',
                            orderable: false,
                            searchable: false,
                            width: '120px'
                        },
                        {
                            data: 'size',
                            name: 'order_product_size',
                            width: '60px'
                        },
                        {
                            data: 'quantity',
                            name: 'order_quantity',
                            width: '70px'
                        },
                        {
                            data: 'wear_date',
                            name: 'order_wear_date',
                            width: '100px'
                        },
                        {
                            data: 'from_inventory',
                            name: 'given_by_invntry',
                            width: '80px'
                        },
                        {
                            data: 'from_onway',
                            name: 'given_by_onway',
                            width: '80px'
                        },
                        {
                            data: 'total_cost',
                            name: 'order_cost',
                            width: '90px'
                        },
                        {
                            data: 'total_price',
                            name: 'order_purchase_price',
                            width: '90px'
                        },
                        {
                            data: 'status',
                            name: 'order_status',
                            width: '100px'
                        },
                        {
                            data: 'user',
                            name: 'user_flag',
                            width: '80px'
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false,
                            width: '120px'
                        }
                    ],
                    aLengthMenu: [
                        [10, 25, 50, 100, 200, -1],
                        [10, 25, 50, 100, 200, "All"]
                    ],
                    buttons: [{
                        extend: 'print',
                        autoPrint: true,
                        text: 'Print',
                        exportOptions: {
                            rows: function(idx, data, node) {
                                var dt = new $.fn.dataTable.Api('#example');
                                xpandTablePrint();
                                var selected = [];
                                $(dt.$('input[type="checkbox"]').map(function() {
                                    selected.push($(this).prop("checked") ? $(this)
                                        .closest('tr').index() : null);
                                }));

                                if (selected.length === 0 || $.inArray(idx, selected) !== -1)
                                    return true;

                                return false;
                            },
                            columns: function(idx, data, node) {
                                if (node.innerHTML == "Check" || node.innerHTML == "Action") {
                                    return false;
                                }
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
                    drawCallback: onDraw,
                    createdRow: function(row, data) {
                        if (data.row_style) {
                            $(row).attr('style', data.row_style);
                        }
                    }
                };

                if (useScroller) {
                    config.deferRender = true;
                    config.scrollY = '60vh';
                    config.scrollX = true;
                    config.scrollCollapse = true;
                    config.scroller = {
                        loadingIndicator: true,
                        displayBuffer: 2
                    };
                    config.dom = 'lBfrti';
                } else {
                    config.dom = 'lBfrtip';
                    config.pageLength = pageLength || 10;
                }

                return config;
            }

            function onDraw() {
                // Restore checkbox states from tracked selection
                $('#example').find('input[name="orders[]"]').each(function() {
                    this.checked = selectAllMatching ?
                        !excludedOrders.has(this.value) :
                        selectedOrders.has(this.value);
                });

                if (scrollerMode) {
                    $('#example_length select').val('-1');
                }

                // Update selection count
                updateSelectionCount();
            }

            function initTable(useScroller, pageLength) {
                if ($.fn.dataTable.isDataTable('#example')) {
                    $('#example').DataTable().destroy();
                    $('#example').find('tbody').empty();
                }

                scrollerMode = useScroller;
                table = $('#example').DataTable(tableConfig(useScroller, pageLength));

                table.on('search.dt', function() {
                    var term = table.search();
                    if (term === lastSearchTerm) return;
                    lastSearchTerm = term;

                    if (selectAllMatching) {
                        selectAllMatching = false;
                        excludedOrders.clear();
                        $('#check-all').prop('checked', false);
                    }
                    updateSelectionCount();
                });
            }

            // Initialize table
            initTable(false, 25);

            // Handle length change
            $(document).on('change', '#example_length select', function() {
                var length = parseInt(this.value, 10);
                var wantScroller = length === -1;

                if (wantScroller !== scrollerMode) {
                    setTimeout(function() {
                        initTable(wantScroller, wantScroller ? null : length);
                    }, 0);
                }
            });

            // "Check all" handler
            $('#check-all').on('change', function() {
                selectAllMatching = this.checked;
                selectedOrders.clear();
                excludedOrders.clear();

                $('#example').find('input[name="orders[]"]').each(function() {
                    this.checked = selectAllMatching;
                });

                updateSelectionCount();
            });

            // Individual checkbox handler
            $('#example').on('change', 'input[name="orders[]"]', function() {
                if (selectAllMatching) {
                    if (this.checked) {
                        excludedOrders.delete(this.value);
                    } else {
                        excludedOrders.add(this.value);
                    }
                } else if (this.checked) {
                    selectedOrders.add(this.value);
                } else {
                    selectedOrders.delete(this.value);
                }
                updateSelectionCount();
            });

            // Print function
            $('#example_filter input').addClass('myClass');

            function xpandTablePrint() {
                var elementsNew = document.getElementsByClassName("myClass");
                Array.from(elementsNew).forEach(function(elementInput) {
                    elementInput.value = '';
                });
                $('.myClass').trigger('keyup');
                table.page.len(-1).draw();
            }

            // Remove confirm form resubmission issue
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        });
    </script>
@endsection
