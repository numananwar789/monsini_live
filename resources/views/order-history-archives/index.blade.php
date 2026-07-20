@extends('layouts.app')

@section('page-css')
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
    
    <style>
        .badgeButton {
            color: #fff !important;
            background-color: #1de9b6 !important;
            border-color: #1de9b6 !important;
            margin-bottom: 6px;
            font-size: 14px;
        }
        .bg-monsini {
            background-color: #3f4d67 !important;
            color: white !important;
        }
        .dt-buttons {
            margin-left: 20px !important;
        }
        .dataTables_wrapper {
            margin-top: 5px;
        }

        td{
                color: black;
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
                                <div class="text-right mb-3 d-flex align-items-center">
                                    @if($archiveList->count() > 0)
                                        @foreach($archiveList as $archive)
                                            <form method="GET" action="{{ route('order-history-archives.index') }}">
                                                @csrf
                                                <input type="submit" name="action" class="btn btn-success mb-0 btn-sm" value="{{ $archive }}" />
                                            </form>
                                        @endforeach
                                    @else
                                        <input type="button" name="action" class="btn btn-danger mb-0 btn-sm" value="No Archives" />
                                    @endif
                                </div>
                                
                                @if($orders->count() > 0)
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <form id="archiveForm" method="POST" action="{{ route('order-history-archives.restore') }}">
                                                @csrf
                                                <input type="hidden" name="archive_name" value="{{ $orders->first()->archive_name }}">
                                                
                                                <table id="example" class="table table-striped table-bordered display nowrap" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Check</th>
                                                            <th>Order ID</th>
                                                            <th>Order GUID</th>
                                                            <th>Vendor Purchase ID</th>
                                                            <th>Customer</th>
                                                            <th>Style</th>
                                                            <th>Color</th>
                                                            <th>Size</th>
                                                            <th>Sub Products</th>
                                                            <th>Quantity</th>
                                                            <th>Purchase ID</th>
                                                            <th>Place Date</th>
                                                            <th>User</th>
                                                            <th>Allocation Date</th>
                                                            <th>Wear Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($orders as $order)
                                                        @php
                                                            $rowClass = str_contains(strtoupper($order->order_customer_name), strtoupper($ownerComp)) 
                                                                ? 'bg-monsini' 
                                                                : '';
                                                        @endphp
                                                        <tr class="{{ $rowClass }}">
                                                            <td style="text-align: center;vertical-align: middle;">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       value="{{ $order->history_ID }}" 
                                                                       id="{{ $order->history_ID }}" 
                                                                       name="history[]">
                                                                <label class="form-check-label" for="{{ $order->history_ID }}"></label>
                                                            </td>
                                                            <td>{{ $order->order_ID }}</td>
                                                            <td>{{ $order->order_GUID }}</td>
                                                            <td>{{ $order->vendor_purchase_ID }}</td>
                                                            <td>{{ $order->order_customer_name }}</td>
                                                            <td>{{ $order->order_product_style }}</td>
                                                            <td>{{ $order->order_product_color }}</td>
                                                            <td>{{ $order->order_product_size }}</td>
                                                               <td>{{ implode(', ', $order->sub_products ?? []) }}</td>
                                                            <td>{{ $order->order_quantity }}</td>
                                                            <td>{{ $order->purchase_id }}</td>
                                                            <td>{{ explode(' ', $order->created_at)[0] }}</td>
                                                            <td>{{ $order->user_flag }}</td>
                                                            <td>{{ date("y-m-d", strtotime($order->history_date)) }}</td>
                                                            <td>{{ $order->order_wear_date }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                @if(auth()->user()->admin_role == "superadmin" || auth()->user()->user_name == "admin1")
                                <div class="card-block">
                                    <button type="submit" form="archiveForm" class="btn btn-success float-right">
                                        Restore Data
                                    </button>
                                </div>
                                @endif
                                @else
                                <div class="alert alert-info">
                                    No orders found in archive. Please select an archive to view orders.
                                </div>
                                @endif
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
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
       <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#example').DataTable({
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
                        columns: ':not(:first-child)'
                    },
                    customize: function(win) {
                        $(win.document.body)
                            .css('font-size', '11pt')
                            .find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');

                        var css = '@page { size: landscape; }';
                        var style = document.createElement('style');
                        style.type = 'text/css';
                        style.media = 'print';
                        style.appendChild(document.createTextNode(css));
                        win.document.head.appendChild(style);
                    }
                }]
            });

            // Show toast messages if they exist
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif
            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif
        });
    </script>
@endsection