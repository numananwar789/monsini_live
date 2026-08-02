@extends('layouts.app')

@section('page-css')
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/datetime/1.1.2/css/dataTables.dateTime.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet" />
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

    <script>
        // Populated via AJAX from getFlaggedOrders() once on page load, instead
        // of being built from every pending order server-side in Blade — that
        // would defeat the point of paginating the table.
        let flaggedOrders = [];
    </script>

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
                                    <h5 class="mb-0 text-uppercase">All Orders</h5>


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
                                                <div class="d-flex justify-content-start align-items-center">
                                                    <label for="check-all" class="mb-0" style="font-size:18px"><b>Check
                                                            all the entries</b></label>
                                                    <input type="checkbox" name="check-all" id="check-all" class="ml-2">
                                                </div>

                                                <form id="orderForm" method="post" action="">
                                                    @csrf
                                                    <table id="example" class="table table-striped table-bordered"
                                                        style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th>Check</th>
                                                                <th>Order ID</th>
                                                                <th>Order GUID</th>
                                                                <th>Purchase ID</th>
                                                                <th>Place Date</th>
                                                                <th>Customer</th>
                                                                <th>Vendor</th>
                                                                <th>Style</th>
                                                                <th>Color</th>
                                                                <th>Sub Products</th>
                                                                <th>Size</th>
                                                                <th>Quantity</th>
                                                                <th>Wear Date</th>
                                                                <th>From Inventory</th>
                                                                <th>From Onway</th>
                                                                <th>Total Cost</th>
                                                                <th>Total Price</th>
                                                                <th>Status</th>
                                                                <th>User</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {{-- Rows loaded via AJAX by DataTables (serverSide),
                                                                 see getOrdersData(). Nothing rendered here. --}}
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
                                            style="color:#fff;">Download
                                            Order Data</a>
                                        @if (auth()->user()->admin_role == 'superadmin')
                                        @endif
                                        <a href="{{ route('orders.refresh') }}" class="btn btn-warning float-right"
                                            style="color:#fff;">Refresh
                                            Orders</a>
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
                                                            <input name="file" type="file" class="form-control-file"
                                                                id="orderFile" accept=".xls,.xlsx" required>
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


    <script>
        function confirmOrders() {
            let selected = Array.from(document.querySelectorAll('input[name="orders[]"]:checked'))
                .map(cb => cb.value);

            let conflicts = flaggedOrders.filter(o => selected.includes(o.id));

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
            form.action = "{{ route('orders.accept') }}";
            form.submit();
        }
    </script>

    <script>
        var table;
        $(document).ready(function() {

            // Fetch the (small) list of orders that would conflict with an
            // inventory allocation, once, independent of table pagination.
            $.getJSON("{{ route('orders.flagged') }}", function(data) {
                flaggedOrders = data;
            });

            table = $('#example').DataTable({
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
                        searchable: false
                    },
                    {
                        data: 'order_id',
                        name: 'order_ID'
                    },
                    {
                        data: 'order_guid',
                        name: 'order_GUID'
                    },
                    {
                        data: 'purchase_id',
                        name: 'purchase_id'
                    },
                    {
                        data: 'place_date',
                        name: 'created_at'
                    },
                    {
                        data: 'customer',
                        name: 'order_customer_name'
                    },
                    {
                        data: 'vendor',
                        name: 'order_vendor_name'
                    },
                    {
                        data: 'style',
                        name: 'order_product_style'
                    },
                    {
                        data: 'color',
                        name: 'order_product_color'
                    },
                    {
                        data: 'sub_products',
                        name: 'sub_products',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'size',
                        name: 'order_product_size'
                    },
                    {
                        data: 'quantity',
                        name: 'order_quantity'
                    },
                    {
                        data: 'wear_date',
                        name: 'order_wear_date'
                    },
                    {
                        data: 'from_inventory',
                        name: 'given_by_invntry'
                    },
                    {
                        data: 'from_onway',
                        name: 'given_by_onway'
                    },
                    {
                        data: 'total_cost',
                        name: 'order_cost'
                    },
                    {
                        data: 'total_price',
                        name: 'order_purchase_price'
                    },
                    {
                        data: 'status',
                        name: 'order_status'
                    },
                    {
                        data: 'user',
                        name: 'user_flag'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
                // Applies the same row background-color rules the old Blade
                // @php block computed per row, now sent as row_style in the JSON.
                createdRow: function(row, data) {
                    if (data.row_style) {
                        $(row).attr('style', data.row_style);
                    }
                },
                aLengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                dom: 'lBfrtip',
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
                            if (node.innerHTML == "Ship Date" || node.innerHTML == "Status" ||
                                node.innerHTML == "Order Allocation" || node.innerHTML ==
                                "Action" ||
                                node.innerHTML == "Check") {
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
                }]
            });

            $('#example_filter input').addClass('myClass');

            table.on('draw', function() {
                if ($('#check-all').is(":checked")) {
                    $('#check-all').prop('checked', false);
                }
            });
        });

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

        // Remove confirm form resubmission issue
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
@endsection
