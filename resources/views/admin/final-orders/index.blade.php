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
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <div class="row">
                                <div class="col">
                                    <h5 class="mb-0 text-uppercase">Final Orders</h5>
                                    <hr />

                                    <div class="card">
                                        <div class="card-header" style="display: flex; align-items:end; justify-content:end">
                                            <div>
                                                <p class="mb-0"><b>Total Quantities:</b> {{ $totOrderQuant }}</p>
                                                <p class="mb-0"><b>Customer Orders (ENTRIES):</b> {{ $totOrderQuant_Others }}</p>
                                                <p class="mb-0"><b>Company Orders (ENTRIES):</b> {{ $totOrderQuant_Comp }}</p>
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <div class="d-flex justify-content-start align-items-center">
                                                    <label for="check-all" class="mb-0" style="font-size:18px">
                                                        <b>Check all the entries</b>
                                                    </label>
                                                    <input type="checkbox" name="check-all" id="check-all" class="ml-2">
                                                </div>

                                                <form id="orderForm" method="post" action="{{ route('final-orders.bypass') }}">
                                                    @csrf

                                                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th>Check</th>
                                                                <th>Order ID</th>
                                                                <th>Order GUID</th>
                                                                <th>Vendor</th>
                                                                <th>Customer</th>
                                                                <th>Style</th>
                                                                <th>Color</th>
                                                                <th>Sub Products</th>
                                                                <th>Size</th>
                                                                <th>Quantity</th>
                                                                <th>From Inventory</th>
                                                                <th>From Onway</th>
                                                                <th>Total Cost</th>
                                                                <th>Total Price</th>
                                                                <th>Place Date</th>
                                                                <th>Status</th>
                                                                <th>Purchase ID</th>
                                                                <th>Wear Date</th>
                                                                <th>User</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>

                                                        <tbody>
                                                            <input type="text" hidden name="orderIDNew" id="orderIDNew">

                                                            @foreach ($orderList as $order)
                                                                @php
                                                                    $row_clr = '';

                                                                    if (str_contains(strtoupper($order->order_customer_name), strtoupper($ownerComp))) {
                                                                        $row_clr = 'background-color: #3f4d67; color:white;';
                                                                    }

                                                                    if ($order->given_by_invntry > 0) {
                                                                        $row_clr = 'background-color: rgb(0 100 12); color:white;';
                                                                    }

                                                                    if ($order->given_by_onway > 0) {
                                                                        $row_clr = 'background-color: rgb(209 198 0); color:black;';
                                                                    }

                                                                    if ($order->order_status == 'Confirmed to Customer') {
                                                                        $row_clr = 'background-color: #90EE90; color:black;';
                                                                    }
                                                                @endphp

                                                                <tr style="{{ $row_clr }}">
                                                                    <td style="text-align: center;vertical-align: middle;">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            value="{{ $order->order_ID }}"
                                                                            id="{{ $order->order_ID }}"
                                                                            name="orders[]">
                                                                        <label class="form-check-label" for="{{ $order->order_ID }}"></label>
                                                                    </td>

                                                                    <td>{{ $order->order_ID }}</td>
                                                                    <td>{{ $order->order_GUID }}</td>
                                                                    <td>{{ strtoupper($order->order_vendor_name) }}</td>
                                                                    <td>{{ strtoupper($order->order_customer_name) }}</td>
                                                                    <td>{{ strtoupper($order->order_product_style) }}</td>
                                                                    <td>{{ strtoupper($order->order_product_color) }}</td>
                                                                    <td>{{ implode(', ', $order->sub_products ?? []) }}</td>
                                                                    <td>{{ $order->order_product_size }}</td>
                                                                    <td>{{ $order->order_quantity }}</td>
                                                                    <td>{{ $order->given_by_invntry }}</td>
                                                                    <td>{{ $order->given_by_onway }}</td>
                                                                    <td>{{ $order->order_cost }}</td>
                                                                    <td>{{ $order->order_purchase_price }}</td>
                                                                    <td>{{ explode(' ', $order->created_at)[0] }}</td>
                                                                    <td>{{ $order->order_status == 'Pending' ? 'Accepted' : $order->order_status }}</td>
                                                                    <td>{{ $order->purchase_id }}</td>
                                                                    <td>{{ $order->order_wear_date }}</td>
                                                                    <td>{{ $order->user_flag }}</td>

                                                                    <td class="text-center">
                                                                        <a target="_self"
                                                                            class="btn btn-success mb-0 btn-sm"
                                                                            href="{{ route('final-orders.edit', $order->final_ID) }}">
                                                                            Edit
                                                                        </a>

                                                                        @if (auth()->user()->admin_role == 'superadmin' || auth()->user()->user_name == 'admin1')
                                                                            <a target="_self"
                                                                                class="btn btn-danger mb-0 btn-sm"
                                                                                href="{{ route('order-finals.delete-id', $order->order_ID) }}">
                                                                                Delete
                                                                            </a>
                                                                        @endif

                                                                        <input name="bypass" type="submit"
                                                                            class="btn btn-warning mb-0 btn-sm"
                                                                            value="Bypass"
                                                                            onclick="javascript:document.getElementById('orderIDNew').value={{ $order->order_ID }};">

                                                                        <input type="text" hidden name="orderID" value="{{ $order->final_ID }}">
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-sm-flex align-item-center justify-content-between">
                                        <div class="d-flex align-items-end">
                                            <div class="d-flex flex-column">
                                                <button type="submit" form="orderForm"
                                                    formaction="{{ route('final-orders.confirm-customer') }}"
                                                    class="btn btn-primary mb-2"
                                                    onclick="xpandTablePrint()">
                                                    Confirm Orders to Customers
                                                </button>

                                                <button type="submit" form="orderForm"
                                                    formaction="{{ route('final-orders.confirm-vendor') }}"
                                                    class="btn btn-primary"
                                                    onclick="return xpandTablePrint2();">
                                                    Send Orders to Vendors
                                                </button>
                                            </div>

                                            <div class="d-flex align-item-end">
                                                <input type="date" form="orderForm" name="dateNow" id="dateNow"
                                                    class="btn btn-primary"
                                                    style="background:#fff;color: #5e239d;" />
                                            </div>
                                        </div>

                                        <div>
                                            <a href="{{ route('order-finals.download') }}"
                                                class="btn btn-success float-right"
                                                style="height: 43px; color:#fff;">
                                                Download Final Order Data
                                            </a>

                                            <a href="/refresh-final-orders"
                                                class="btn btn-warning float-right mr-2"
                                                style="height: 43px; color:#fff;">
                                                Refresh Final Orders
                                            </a>

                                            <button id="cancel-orders"
                                                class="btn btn-warning float-right mr-2"
                                                style="color:#fff; background:#f8631d">
                                                Cancel Orders
                                            </button>
                                        </div>
                                    </div>

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
    <script src="/assets/plugins/notification/js/bootstrap-growl.min.js"></script>

    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

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
                                selected.push($(this).prop("checked") ? $(this).closest('tr').index() : null);
                            }));

                            if (selected.length === 0 || $.inArray(idx, selected) !== -1) {
                                return true;
                            }

                            return false;
                        },
                        columns: function(idx, data, node) {
                            if (
                                node.innerHTML == "Status" ||
                                node.innerHTML == "Check" ||
                                node.innerHTML == "Action"
                            ) {
                                return false;
                            }

                            return true;
                        }
                    },
                    customize: function(win) {
                        $(win.document.body).css('font-size', '11pt');
                        $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');

                        var css = '@page { size: landscape; }',
                            head = win.document.head || win.document.getElementsByTagName('head')[0],
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

        $('#check-all').click(function() {
            $('input[name="orders[]"]').prop('checked', this.checked);
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

        function xpandTablePrint2() {
            var dateElement = document.getElementById("dateNow").value;

            if (dateElement != "") {
                var elementsNew = document.getElementsByClassName("myClass");

                Array.from(elementsNew).forEach(function(elementInput) {
                    elementInput.value = '';
                });

                $('.myClass').trigger('keyup');
                table.page.len(-1).draw();

                return true;
            } else {
                alert("You are missing due date");
                return false;
            }
        }

        document.getElementById("cancel-orders").addEventListener("click", function() {
            if (confirm("Are you sure you want to cancel the selected orders?")) {
                cancelOrders();
            }
        });

        function getCheckedOrderIDs() {
            const orderIDs = [];
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name="orders[]"]:checked');

            checkboxes.forEach((checkbox) => {
                orderIDs.push(checkbox.value);
            });

            return orderIDs;
        }

        function cancelOrders() {
            const orderIDs = getCheckedOrderIDs();

            if (orderIDs.length === 0) {
                alert("Please select at least one order to cancel.");
                return;
            }

            const token = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                type: "POST",
                url: "{{ route('order-finals.cancel') }}",
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: {
                    orderIDs: orderIDs
                },
                dataType: "json",
                success: function(response) {
                    alert(response.message);
                    location.reload();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Failed to cancel orders');
                }
            });
        }

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @endif

        @if (session('error'))
            toastr.error('{{ session('error') }}');
        @endif
    </script>
@endsection