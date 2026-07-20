@extends('layouts.app')

@section('page-css')



@endsection


@push('styles')
    <style>
        .bg-monsini {
            background-color: #3f4d6794 !important; 
            color: white !important;;
        }
        .bg-inventory {
            background-color: rgb(0 100 12) !important;
            color: white;
        }
        .bg-onway {
            background-color: rgb(209 198 0) !important; 
            color: white !important;
        }
        .bg-bypass {
            background-color: rgb(255, 157, 92) !important; 
            color: white !important; 
        }

        /* .table td, .table th {
            color:black
            } */

    </style>
@endpush

@section('content')


    
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <div class="row">
                            <div class="col">
                                <h5 class="mb-0 text-uppercase">Orders Allocation</h5>
                                <hr />
                                <div class="card">
                                    <div class="card-header" style="display: flex; align-items:end; justify-content:end">
                                        <div>
                                            <p class="mb-0"><b>Total Quantities:</b> {{ $totOrderQuant }}</p>
                                            <p class="mb-0"><b>On Way Quantities:</b> {{ $totOrderQuant_OnWay_Monsini }}</p>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <div class="d-flex justify-content-start align-items-center">
                                                <label for="check-all" class="mb-0" style="font-size:18px"><b>Check all the entries</b></label>
                                                <input type="checkbox" name="check-all" id="check-all" class="ml-2">
                                            </div>

                                              <style>
                                                    .bg-monsini {
                                                        background-color: #3f4d6794 !important; 
                                                        color: white !important;;
                                                    }
                                                    .bg-inventory {
                                                        background-color: rgb(0, 100, 12) !important;
                                                        color: white;
                                                    }
                                                    .bg-onway {
                                                        background-color: rgb(209, 198, 0) !important; 
                                                        color: white !important;
                                                    }
                                                    .bg-bypass {
                                                        background-color: rgb(255, 157, 92) !important; 
                                                        color: white !important; 
                                                    }

                                                    /* .table td, .table th {
                                                        color:black
                                                        } */

                                                </style>

                                            <form id="orderForm" method="post" action="{{ route('order-allocations.confirm-to-customer') }}">
                                                @csrf
                                                <table id="example" class="table table-striped table-bordered" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Check</th>
                                                            <th>Order ID</th>
                                                            <th>Order GUID</th>
                                                            <th>Vendor</th>
                                                            <th>Vendor Purchase ID</th>
                                                            <th>Customer</th>
                                                            <th>Style</th>
                                                            <th>Color</th>
                                                            <th>Size</th>
                                                            <th>sub Products</th>
                                                            <th>Quantity</th>
                                                            <th>Inventory</th>
                                                            <th>OnWay</th>
                                                            <th>Total Cost</th>
                                                            <th>Total Price</th>
                                                            <th>Place Date</th>
                                                            <th>Status</th>
                                                            <th>Customer Purchase ID</th>
                                                            <th>Wear Date</th>
                                                            <th>User</th>
                                                            <th>Staging Date</th>
                                                            <th>Action</th>
                                                            <th>Allocate</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($orderList as $order)
                                                            @php
                                                                $rowClass = 'ss';
                                                                $button = 'Allocate';
                                                                $row_clr = '';
                                                                if (str_contains(strtoupper($order->order_customer_name), strtoupper($ownerComp))) {
                                                                    $rowClass = "bg-monsini";
                                                                     $row_clr = "background-color: #3f4d67; color:white;";
                                                                }
                                                                if ($order->given_by_invntry > 0) {
                                                                    $rowClass = "bg-inventory";
                                                                    $row_clr = "background-color: rgb(0 100 12); color:white;";
                                                                    $button = 'Allocate';
                                                                }
                                                                if ($order->given_by_onway > 0) {
                                                                    $rowClass = "bg-onway";
                                                                    $row_clr = "background-color: rgb(209 198 0); color:white;";
                                                                    $button = 'Allocate';
                                                                }
                                                                if ($order->bypass == 1) {
                                                                    $rowClass = "bg-bypass";
                                                                    $row_clr = "background-color: rgb(255, 157, 92); color:white;";
                                                                    $button = 'Allocate';
                                                                }

                                                     

                                                            @endphp
                                                            <tr class="{{ $rowClass }}" style="{{ $row_clr }}">
                                                                <td style="text-align: center;vertical-align: middle;">
                                                                    <input form="orderForm" class="form-check-input" type="checkbox" value="{{ $order->order_ID }}" id="{{ $order->order_ID }}" name="orders[]">
                                                                    <label class="form-check-label" for="{{ $order->order_ID }}"></label>
                                                                </td>
                                                                <td>{{ $order->order_ID }}</td>
                                                                <td>{{ $order->order_GUID }}</td>
                                                                <td>{{ strtoupper($order->order_vendor_name) }}</td>
                                                                <td>{{ strtoupper($order->vendor_purchase_ID) }}</td>
                                                                <td>{{ strtoupper($order->order_customer_name) }}</td>
                                                                <td>{{ strtoupper($order->order_product_style) }}</td>
                                                                <td>{{ strtoupper($order->order_product_color) }}</td>
                                                                <td>{{ $order->order_product_size }}</td>
                                                                
                                                                <td>
    {{
        is_array($order->sub_products)
            ? implode(', ', $order->sub_products)
            : $order->sub_products
    }}
</td>
                                                                <td>{{ $order->order_quantity }}</td>
                                                                <td>{{ $order->given_by_invntry }}</td>
                                                                <td>{{ $order->given_by_onway }}</td>
                                                                <td>{{ $order->order_cost }}</td>
                                                                <td>{{ $order->order_purchase_price }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') }}</td>
                                                                <td>{{ $order->order_status == "Pending" ? "Confirmed" : $order->order_status }}</td>
                                                                <td>{{ $order->purchase_id }}</td>
                                                                <td>{{ $order->order_wear_date }}</td>
                                                                <td>{{ $order->user_flag }}</td>
                                                                <td>{{ $order->staging_date }}</td>
                                                                <td class="text-center">
                                                                    <a target="_self" class="btn btn-success mb-0 btn-sm" href="{{ route('order-allocations.edit', $order->order_ID) }}">Edit</a>
                                                                    @if(auth()->user()->admin_role == "superadmin" || auth()->user()->user_name == "admin1")
                                                                        @if($order->given_by_invntry > 0 || $order->given_by_onway > 0)
                                                                            <a target="_self" class="btn btn-danger mb-0 btn-sm" href="{{ route('order-allocation.delete', $order->order_ID) }}" onclick="return confirm('Are you sure you want to delete this order?')">Delete</a>
                                                                        @else
                                                                            <a target="_self" class="btn btn-danger mb-0 btn-sm" href="{{ route('order-allocation.delete-full', $order->order_ID) }}" onclick="return confirm('Are you sure you want to delete this order and its related records?')">Delete</a>
                                                                        @endif
                                                                    @endif
                                                                    <a target="_self" class="{{ $order->staging_flag == 'Yes' ? 'btn btn-warning mb-0 btn-sm' : 'btn btn-success mb-0 btn-sm' }}" href="{{ route('order-allocation.toggle-staging', $order->order_ID) }}">{{ $order->staging_flag == 'Yes' ? 'Incoming' : 'Send to Staging' }}</a>
                                                                    <input type="text" style="display:none" name="orderID" value="{{ $order->order_ID }}">
                                                                </td>
                                                                <td>
                                                                      <input type="button" class="btn btn-info mb-0 btn-sm" 
                                                                            data-toggle="modal" data-target="#order_model" 
                                                                            name="{{ $order->order_ID }}" 
                                                                            value="Allocate" 
                                                                            onclick="fillModal(this)">
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th>Check</th>
                                                            <th>Order ID</th>
                                                            <th>Order GUID</th>
                                                            <th>Vendor</th>
                                                            <th>Vendor Purchase ID</th>
                                                            <th>Customer</th>
                                                            <th>Style</th>
                                                            <th>Color</th>
                                                            <th>Size</th>
                                                            <th>sub Products</th>
                                                            <th>Quantity</th>
                                                            <th>Inventory</th>
                                                            <th>OnWay</th>
                                                            <th>Total Cost</th>
                                                            <th>Total Price</th>
                                                            <th>Place Date</th>
                                                            <th>Status</th>
                                                            <th>Customer Purchase ID</th>
                                                            <th>Wear Date</th>
                                                            <th>User</th>
                                                            <th>Staging Date</th>
                                                            <th>Action</th>
                                                            <th>Allocate</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-block">
                                    <button type="submit" name="allocate" value="Bulk Allocate" class="btn btn-primary flaot-left" form="orderForm" formaction="{{ route('order-allocations.bulk-allocate') }}" onclick="xpandTablePrint()">Bulk Allocate</button>
                                    <button type="submit" name="stage" value="Bulk Stage" class="btn btn-info flaot-left" form="orderForm" formaction="{{ route('order-allocations.bulk-stage') }}" onclick="xpandTablePrint()">Bulk Stage</button>
                                    <button type="submit" name="unstage" value="Bulk Unstage" class="btn btn-warning flaot-left" form="orderForm" formaction="{{ route('order-allocations.bulk-unstage') }}" onclick="xpandTablePrint()">Bulk Unstage</button>
                                    <a href="{{ route('order-allocations.download') }}" class="btn btn-success float-right" style="color:#fff;">Download Order Allocation Data</a>
                                    @if(auth()->user()->admin_role == "superadmin")
                                        {{-- <a href="{{ route('order-allocations.clear') }}" class="btn btn-danger float-right" onclick="return confirm('Are you sure you want to delete all orders');">Clear All Orders</a> --}}
                                    @endif
                                    <button id="cancel-orders" class="btn btn-warning float-right" style="color:#fff; background:#f8631d">Cancel Orders</button>
                                </div>
                                <div class="card-block">
                                    <button type="submit" name="customer" value="Confirm Orders to Customers" class="btn btn-primary mb-2" form="orderForm" onclick="xpandTablePrint()">Confirm Orders to Customers</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Allocation Modal -->
<div class="modal fade" id="order_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Allocation for Order ID: <span id="_ordID"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="" id="allocationForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6">
                            <p><b>Vendor Name:</b> <span id="_venName"></span></p>
                        </div>
                        <div class="col-6">
                            <p><b>Vendor Purchase ID:</b> <span id="_venPur"></span></p>
                        </div>
                        <div class="col-6">
                            <p><b>Customer Name:</b> <span id="_CusName"></span></p>
                        </div>
                        <div class="col-6">
                            <p><b>Style:</b> <span id="_styleNumber"></span></p>
                        </div>
                        <div class="col-6">
                            <p><b>Color:</b> <span id="_prodColor"></span></p>
                        </div>
                        <div class="col-6">
                            <p><b>Size:</b> <span id="_prodSize"></span></p>
                        </div>
                        <div class="col-6">
                            <p><b>Order Quantity:</b> <span id="_ordQuant"></span></p>
                        </div>
                        <div class="col-6">
                            <p><b>From Inventory:</b> <span id="_fromIn"></span></p>
                        </div>
                        <div class="col-6">
                            <p><b>From OnWay:</b> <span id="_fromONW"></span></p>
                        </div>
                        <div class="col-12">
                            <div class="form-group mt-4">
                                <label>Allocate Units In Number</label>
                                <input class="form-control" type="number" id="placed_order" name="itemnumber" min="1">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Allocate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-js')
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script> --}}

    
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
<script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

<script type="text/javascript">
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

    var table;
    $(document).ready(function() {
        // table = $('#example').DataTable({
        //     aLengthMenu: [
        //         [25, 50, 100, 200, -1],
        //         [25, 50, 100, 200, "All"]
        //     ],
        //     dom: 'lBfrtip',
        //     buttons: [{
        //         extend: 'print',
        //         autoPrint: true,
        //         text: 'Print',
        //         exportOptions: {
        //             // Your requested specific columns
        //             columns: [1, 4, 5, 6, 7, 8, 9, 10, 17]
        //         },
        //         customize: function(win) {
        //             var sheet = win.document.styleSheets[0];
                    
        //             // 1. Force Landscape and remove unnecessary browser margins
        //             $(win.document.body).css('font-size', '10pt').css('margin', '0').css('padding', '0');
                    
        //             // 2. Style the table for maximum space efficiency
        //             $(win.document.body).find('table')
        //                 .addClass('compact')
        //                 .css('width', '100%')
        //                 .css('table-layout', 'auto') // Allows browser to calculate best fit
        //                 .css('font-size', 'inherit');
            
        //             // 3. Define specific column behaviors via CSS injection
        //             var style = win.document.createElement('style');
        //             style.type = 'text/css';
        //             style.innerHTML = `
        //                 @page { size: landscape; margin: 0.5cm; }
        //                 table { border-collapse: collapse !important; }
        //                 th, td { 
        //                     padding: 4px !important; 
        //                     border: 1px solid #ddd !important;
        //                     word-wrap: break-word;
        //                     white-space: normal !important;
        //                 }
        //                 th:nth-child(1), td:nth-child(1) { width: 50px; }
        //                 th:nth-child(6), td:nth-child(6) { width: 40px; }
        //                 th:nth-child(8), td:nth-child(8) { width: 40px; }
        //                 th:nth-child(2), td:nth-child(2) { max-width: 150px; }
        //             `;
        //             win.document.head.appendChild(style);
        //         }
        //     }]
        // });
        
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
                    columns: [1, 4, 5, 6, 7, 8, 9, 10, 17],
                    // Fix: Only include rows where the checkbox is checked
                    rows: function (idx, data, node) {
                        // Check if any boxes are selected. If none, print all (or change to return false to print nothing)
                        var hasChecked = $('input[type="checkbox"][name="orders[]"]:checked').length > 0;
                        if (!hasChecked) return true; 
        
                        // Return true only for rows where the checkbox is checked
                        return $(node).find('input[type="checkbox"]').is(':checked');
                    }
                },
                customize: function(win) {
                    $(win.document.body).css('font-size', '8pt').css('margin', '0');
                    
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('width', '100%');
                
                    var style = win.document.createElement('style');
                    style.type = 'text/css';
                    style.innerHTML = `
                            @page { size: landscape; margin: 0.2cm; } /* Minimal margins */
                            
                            table { 
                                border-collapse: collapse !important; 
                                table-layout: auto !important; /* Allows cells to wrap to their natural width */
                                width: 100% !important; 
                                font-size: 10pt !important; /* Tiny font to squeeze data */
                            }
                            
                            th, td { 
                                padding: 1px 2px !important; 
                                border: 1px solid #ddd !important; 
                                white-space: nowrap !important; /* Forces everything on one line */
                                overflow: visible !important; /* Ensures no hidden content */
                            }
                        `;
                        win.document.head.appendChild(style);
                }
            }]
        });

        $('#example_filter input').addClass('myClass');

        // Handle paginate button events for unchecking the check-all check
        table.on('draw', function() {
            if ($('#check-all').is(":checked")) {
                $('#check-all').prop('checked', false);
            }
        });
    });

    // Listen for click on toggle checkbox
    $('#check-all').click(function(event) {
        if (this.checked) {
            // Iterate each checkbox
            $(':checkbox').each(function() {
                this.checked = true;
            });
        } else {
            $(':checkbox').each(function() {
                this.checked = false;
            });
        }
    });

    // function fillModal(e) {
    //     var order_ID_JS = e.name;
    //     $('#exampleModalLabel').html("Allocation for Order ID: " + order_ID_JS);
    //     document.getElementById('orderIDNow').value = order_ID_JS;
        
    //     // Get order data from the table row
    //     var row = $(e).closest('tr');
    //     $('#_venName').html(row.find('td:eq(3)').text());
    //     $('#_venPur').html(row.find('td:eq(4)').text());
    //     $('#_ordID').html(order_ID_JS);
    //     $('#_CusName').html(row.find('td:eq(5)').text());
    //     $('#_styleNumber').html(row.find('td:eq(6)').text());
    //     $('#_prodColor').html(row.find('td:eq(7)').text());
    //     $('#_prodSize').html(row.find('td:eq(8)').text());
    //     $('#_ordQuant').html(row.find('td:eq(9)').text());
    //     $('#_fromIn').html(row.find('td:eq(10)').text());
    //     $('#_fromONW').html(row.find('td:eq(11)').text());
    // }

        function fillModal(button) {
        const orderId = button.name;
        
        // Set form action
        $('#allocationForm').attr('action', `/order-allocations-allocate/${orderId}/allocate`);
        
        // Load order data via AJAX
            $.get(`/order-allocations-show/${orderId}/show`, function(data) {
                $('#_venName').text(data.venName);
                $('#_venPur').text(data.venPur);
                $('#_ordID').text(data.ordID);
                $('#_CusName').text(data.CusName);
                $('#_styleNumber').text(data.styleNumber);
                $('#_prodColor').text(data.prodColor);
                $('#_prodSize').text(data.prodSize);
                $('#_ordQuant').text(data.ordQuant);
                $('#_fromIn').text(data.fromIn);
                $('#_fromONW').text(data.fromONW);
                
                // Set max value for allocation input
                $('#placed_order').attr('max', data.ordQuant);
            }).fail(function() {
                alert('Failed to load order data');
            });
        }
 
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

        if (!confirm("Are you sure you want to cancel the selected orders?")) {
            return;
        }

        const token = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            type: "POST",
            url: "{{ route('order-allocations.cancel') }}",
            headers: {
                'X-CSRF-TOKEN': token
            },
            data: {
                orderIDs: orderIDs
            },
            dataType: "json",
            success: function(response) {
                toastr.success(response.message);
                location.reload();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed to cancel orders');
            }
        });
    }

    document.getElementById("cancel-orders").addEventListener("click", cancelOrders);

    // Show toast messages if they exist
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif
</script>
@endsection