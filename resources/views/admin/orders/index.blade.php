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
                                                            @foreach ($orderList as $order)
                                                                @php
                                                                    $row_clr = '';
                                                                    if (
                                                                        strtoupper($order->order_customer_name) ==
                                                                        strtoupper($ownerComp)
                                                                    ) {
                                                                        $row_clr =
                                                                            'background-color: #3f4d67; color:white;';
                                                                    }
                                                                    if ($order->given_by_invntry > 0) {
                                                                        $row_clr =
                                                                            'background-color: rgb(0 100 12); color:white;';
                                                                    }
                                                                    if ($order->given_by_onway > 0) {
                                                                        $row_clr =
                                                                            'background-color: rgb(209 198 0); color:black;';
                                                                    }
                                                                @endphp
                                                                <tr style="{{ $row_clr }}">
                                                                    <td style="text-align: center;vertical-align: middle;">
                                                                        <input form="orderForm" class="form-check-input"
                                                                            type="checkbox" value="{{ $order->order_ID }}"
                                                                            id="{{ $order->order_ID }}" name="orders[]">
                                                                        <label class="form-check-label"
                                                                            for="{{ $order->order_ID }}"></label>
                                                                    </td>
                                                                    <td>{{ $order->order_ID }}</td>
                                                                    <td>{{ $order->order_GUID }}</td>
                                                                    <td>{{ $order->purchase_id }}</td>
                                                                    <td>{{ explode(' ', $order->created_at)[0] }}</td>
                                                                    <td>{{ strtoupper($order->order_customer_name) }}</td>
                                                                    <td>{{ strtoupper($order->order_vendor_name) }}</td>
                                                                    <td>{{ strtoupper($order->order_product_style) }}</td>
                                                                    <td>{{ strtoupper($order->order_product_color) }}</td>
                                                                    <td>{{ implode(', ', $order->sub_products ?? []) }}</td>
                                                                    <td>{{ $order->order_product_size }}</td>
                                                                    <td>{{ $order->order_quantity }}</td>
                                                                    <td>{{ $order->order_wear_date }}</td>
                                                                    <td>{{ $order->given_by_invntry }}</td>
                                                                    <td>{{ $order->given_by_onway }}</td>
                                                                    <td>{{ $order->order_cost }}</td>
                                                                    <td>{{ $order->order_purchase_price }}</td>
                                                                    <td>{{ $order->order_status }}</td>
                                                                    <td>{{ $order->user_flag }}</td>
                                                                    <td class="text-center">
                                                                        <a target="_self"
                                                                            class="btn btn-success mb-0 btn-sm"
                                                                            href="{{ route('orders.edit', $order->order_ID) }}">Edit</a>
                                                                        @if (auth()->user()->admin_role == 'superadmin' || auth()->user()->user_name == 'admin1')
                                                                            <a target="_self"
                                                                                class="btn btn-danger mb-0 mr-0 btn-sm"
                                                                                href="{{ route('orders.delete-id', $order->order_ID) }}">Delete</a>
                                                                        @endif
                                                                        <input type="hidden" name="orderID"
                                                                            value="{{ $order->order_ID }}">
                                                                    </td>
                                                                </tr>

                                                                @if ($order->given_by_invntry > 0 && !empty($order->sub_products))
                                                                    <script>
                                                                        flaggedOrders.push({
                                                                            id: "{{ $order->order_ID }}",
                                                                            guid: "{{ $order->order_GUID }}",
                                                                            style: "{{ strtoupper($order->order_product_style) }}",
                                                                            color: "{{ strtoupper($order->order_product_color) }}",
                                                                            subs: "{{ implode(', ', $order->sub_products ?? []) }}",
                                                                            qty: "{{ $order->order_quantity }}"
                                                                        });
                                                                    </script>
                                                                @endif
                                                            @endforeach
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
                                        {{-- <button type="submit" form="orderForm" formaction="{{ route('orders.accept') }}"
                                            class="btn btn-primary" onclick="xpandTablePrint()">Accept Customer
                                            Orders</button> --}}

                                        <button type="button"
                                                class="btn btn-primary"
                                                onclick="confirmOrders()">
                                            Accept Customer Orders
                                        </button>

                                        <a href="{{ route('orders.export.pending') }}" class="btn btn-success float-right" style="color:#fff;">Download
                                            Order Data</a>
                                        @if (auth()->user()->admin_role == 'superadmin')
                                            {{-- <a href="{{ route('orders.clear-all') }}" class="btn btn-danger float-right"
                                                onclick="return confirm('Are you sure you want to delete all orders');">Clear
                                                All Orders</a> --}}
                                        @endif
                                        <a href="{{ route('orders.refresh') }}" class="btn btn-warning float-right" style="color:#fff;">Refresh
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
                However, the <strong>sub-products (add-ons)</strong> are not in stock and must be purchased separately from the vendor.</p>
                
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

@if(session('success') == 'Orders refreshed successfully')
<script>
    window.location.reload();
</script>
@endif
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
        crossorigin="anonymous"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="/assets/js/pcoded.min.js"></script> --}}
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

                    // Show modal
                    let modal = new bootstrap.Modal(document.getElementById('conflictModal'));
                    modal.show();
            }else {
                // No conflicts → submit directly
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
            table = $('#example').DataTable({
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
