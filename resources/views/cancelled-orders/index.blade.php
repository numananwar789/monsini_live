@extends('layouts.app')

@section('page-css')
<style>
    .badgeButton {
        color: #fff !important;
        background-color: #1de9b6 !important;
        border-color: #1de9b6 !important;
        margin-bottom: 6px;
        font-size: 14px;
    }
    .in-td {
        width: 200px;
        margin: 0 auto;
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
                                <h5 class="mb-0 text-uppercase">Archive Orders</h5>
                                <hr />
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <form id="archiveForm" method="POST" action="{{ route('cancelled-orders.restore') }}">
                                                @csrf
                                                <table id="example" class="table table-striped table-bordered display nowrap" style="width:100%">
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
                                                            <th>Size</th>
                                                            <th>Sub Products</th>
                                                            <th>Quantity</th>
                                                            <th>Wear Date</th>
                                                            <th>From Inventory</th>
                                                            <th>From Onway</th>
                                                            <th>Total Cost</th>
                                                            <th>Total Price</th>
                                                            <th>Status</th>
                                                            <th>User</th>
                                                            <th>Stage/Table</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($archiveListNew as $order)
                                                            @php
                                                                $row_clr = '';
                                                                if (strtoupper($order->order_customer_name) == strtoupper($ownerComp)) {
                                                                    $row_clr = "background-color: #3f4d67; color:white;";
                                                                }
                                                                if ($order->given_by_invntry > 0) {
                                                                    $row_clr = "background-color: rgb(0 100 12); color:white;";
                                                                }
                                                                if ($order->given_by_onway > 0) {
                                                                    $row_clr = "background-color: rgb(209 198 0); color:black;";
                                                                }
                                                            @endphp
                                                            <tr style="{{ $row_clr }}">
                                                                <td style="text-align: center;vertical-align: middle;">
                                                                    <input class="form-check-input" type="checkbox" value="{{ $order->order_ID }}" id="{{ $order->order_ID }}" name="history[]">
                                                                    <label class="form-check-label" for="{{ $order->order_ID }}"></label>
                                                                </td>
                                                                <td>{{ $order->order_ID }}</td>
                                                                <td>{{ $order->order_GUID }}</td>
                                                                <td>{{ $order->purchase_id }}</td>
                                                                <td>{{ explode(' ', $order->created_at)[0] }}</td>
                                                                <td>{{ strtoupper($order->order_customer_name) }}</td>
                                                                <td>{{ strtoupper($order->order_vendor_name) }}</td>
                                                                <td>{{ strtoupper($order->order_product_style) }}</td>
                                                                <td>{{ strtoupper($order->order_product_color) }}</td>
                                                                <td>{{ $order->order_product_size }}</td>
                                                                   <td>{{ implode(', ', $order->sub_products ?? []) }}</td>
                                                                <td>{{ $order->order_quantity }}</td>
                                                                <td>{{ $order->order_wear_date }}</td>
                                                                <td>{{ $order->given_by_invntry }}</td>
                                                                <td>{{ $order->given_by_onway }}</td>
                                                                <td>{{ $order->order_cost }}</td>
                                                                <td>{{ $order->order_purchase_price }}</td>
                                                                <td>{{ $order->order_status }}</td>
                                                                <td>{{ $order->user_flag }}</td>
                                                                <td>Order</td>
                                                            </tr>
                                                        @endforeach

                                                        @foreach($archiveListNew_final as $order)
                                                            @php
                                                                $row_clr = '';
                                                                if (strtoupper($order->order_customer_name) == strtoupper($ownerComp)) {
                                                                    $row_clr = "background-color: #3f4d67; color:white;";
                                                                }
                                                                if ($order->given_by_invntry > 0) {
                                                                    $row_clr = "background-color: rgb(0 100 12); color:white;";
                                                                }
                                                                if ($order->given_by_onway > 0) {
                                                                    $row_clr = "background-color: rgb(209 198 0); color:black;";
                                                                }
                                                            @endphp
                                                            <tr style="{{ $row_clr }}">
                                                                <td style="text-align: center;vertical-align: middle;">
                                                                    <input class="form-check-input" type="checkbox" value="{{ $order->order_ID }}" id="{{ $order->order_ID }}" name="history[]">
                                                                    <label class="form-check-label" for="{{ $order->order_ID }}"></label>
                                                                </td>
                                                                <td>{{ $order->order_ID }}</td>
                                                                <td>{{ $order->order_GUID }}</td>
                                                                <td>{{ $order->purchase_id }}</td>
                                                                <td>{{ explode(' ', $order->created_at)[0] }}</td>
                                                                <td>{{ strtoupper($order->order_customer_name) }}</td>
                                                                <td>{{ strtoupper($order->order_vendor_name) }}</td>
                                                                <td>{{ strtoupper($order->order_product_style) }}</td>
                                                                <td>{{ strtoupper($order->order_product_color) }}</td>
                                                                <td>{{ $order->order_product_size }}</td>
                                                                   <td>{{ implode(', ', $order->sub_products ?? []) }}</td>
                                                                <td>{{ $order->order_quantity }}</td>
                                                                <td>{{ $order->order_wear_date }}</td>
                                                                <td>{{ $order->given_by_invntry }}</td>
                                                                <td>{{ $order->given_by_onway }}</td>
                                                                <td>{{ $order->order_cost }}</td>
                                                                <td>{{ $order->order_purchase_price }}</td>
                                                                <td>{{ $order->order_status }}</td>
                                                                <td>{{ $order->user_flag }}</td>
                                                                <td>Final</td>
                                                            </tr>
                                                        @endforeach

                                                        @foreach($archiveListNew_allocation as $order)
                                                            @php
                                                                $row_clr = '';
                                                                if (strtoupper($order->order_customer_name) == strtoupper($ownerComp)) {
                                                                    $row_clr = "background-color: #3f4d67; color:white;";
                                                                }
                                                                if ($order->given_by_invntry > 0) {
                                                                    $row_clr = "background-color: rgb(0 100 12); color:white;";
                                                                }
                                                                if ($order->given_by_onway > 0) {
                                                                    $row_clr = "background-color: rgb(209 198 0); color:black;";
                                                                }
                                                            @endphp
                                                            <tr style="{{ $row_clr }}">
                                                                <td style="text-align: center;vertical-align: middle;">
                                                                    <input class="form-check-input" type="checkbox" value="{{ $order->order_ID }}" id="{{ $order->order_ID }}" name="history[]">
                                                                    <label class="form-check-label" for="{{ $order->order_ID }}"></label>
                                                                </td>
                                                                <td>{{ $order->order_ID }}</td>
                                                                <td>{{ $order->order_GUID }}</td>
                                                                <td>{{ $order->purchase_id }}</td>
                                                                <td>{{ explode(' ', $order->created_at)[0] }}</td>
                                                                <td>{{ strtoupper($order->order_customer_name) }}</td>
                                                                <td>{{ strtoupper($order->order_vendor_name) }}</td>
                                                                <td>{{ strtoupper($order->order_product_style) }}</td>
                                                                <td>{{ strtoupper($order->order_product_color) }}</td>
                                                                <td>{{ $order->order_product_size }}</td>
                                                                   <td>{{ implode(', ', $order->sub_products ?? []) }}</td>
                                                                <td>{{ $order->order_quantity }}</td>
                                                                <td>{{ $order->order_wear_date }}</td>
                                                                <td>{{ $order->given_by_invntry }}</td>
                                                                <td>{{ $order->given_by_onway }}</td>
                                                                <td>{{ $order->order_cost }}</td>
                                                                <td>{{ $order->order_purchase_price }}</td>
                                                                <td>{{ $order->order_status }}</td>
                                                                <td>{{ $order->user_flag }}</td>
                                                                <td>Allocation</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                @if(auth()->user()->admin_role == 'superadmin' || auth()->user()->user_name == 'admin1')
                                    <div class="card-block">
                                        <button type="submit" onclick="xpandTablePrint()" name="restore" form="archiveForm" class="btn btn-success float-right">Restore Data</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
<script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
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

    $(document).ready(function() {
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
    });
</script>
@endsection