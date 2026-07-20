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
        .custom-select-box {
            border: 1px solid #aaa;
            border-radius: 3px;
            padding: 5px;
            background-color: transparent;
            padding: 4px;
            font-size: 17px;
            margin-right: 10px;
        }
        @media screen and (min-width: 1200px) {
            .custom-select-box-main {
                gap: 10px;
                margin-bottom: -41px !important;
                justify-content: end;
            }
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
                            <div class="col-12">
                                <div class="text-right mb-3 d-flex align-items-center flex-wrap custom-select-box-main">
                                    <form method="GET" action="{{ route('reports.index') }}">
                                        @csrf
                                        <select name="selected_archive" class="form-select form-select-sm custom-select-box" title="select-archive">
                                            <option disabled selected>Select an Archive</option>
                                            @foreach($archiveList as $archive)
                                                <option value="{{ $archive }}" {{ request('selected_archive') == $archive ? 'selected' : '' }}>
                                                    {{ $archive }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" name="action" class="btn btn-success btn-sm">Filter</button>
                                        <a href="{{ route('reports.index') }}" class="btn btn-danger btn-sm">Clear</a>
                                    </form>
                                </div>

                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="report_1_tab" data-toggle="tab" href="#report_1" role="tab" aria-controls="report_1" aria-selected="true">Total Orders</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="report_2_tab" data-toggle="tab" href="#report_2" role="tab" aria-controls="report_2" aria-selected="false">Top Sold Styles</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="report_3_tab" data-toggle="tab" href="#report_3" role="tab" aria-controls="report_3" aria-selected="false">Customer Total History</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="report_4_tab" data-toggle="tab" href="#report_4" role="tab" aria-controls="report_4" aria-selected="false">Top Customers</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="report_5_tab" data-toggle="tab" href="#report_5" role="tab" aria-controls="report_5" aria-selected="false">Late Vendors</a>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="myTabContent">
                                    <!-- Total Orders Tab -->
                                    <div class="tab-pane fade show active" id="report_1" role="tabpanel" aria-labelledby="home-report_1_tab">
                                        <h5 class="mb-0 text-uppercase">Total Orders</h5>
                                        <hr />
                                        <div class="table-responsive">
                                            <table id="example" class="table table-striped table-bordered display nowrap" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Vendor Name</th>
                                                        <th>Customer Name</th>
                                                        <th>Product Style</th>
                                                        <th>Product Color</th>
                                                        <th>Product Size</th>
                                                        <th>Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($totOrders as $order)
                                                    <tr>
                                                        <td>{{ strtoupper($order->order_vendor_name) }}</td>
                                                        <td>{{ ucwords($order->order_customer_name) }}</td>
                                                        <td>{{ ucwords($order->order_product_style) }}</td>
                                                        <td>{{ strtoupper($order->order_product_color) }}</td>
                                                        <td>{{ ucwords($order->order_product_size) }}</td>
                                                        <td>{{ ucwords($order->order_quantity) }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Top Sold Styles Tab -->
                                    <div class="tab-pane fade" id="report_2" role="tabpanel" aria-labelledby="home-report_2_tab">
                                        <h5 class="mb-0 text-uppercase">Top Sold Styles</h5>
                                        <hr />
                                        <div class="table-responsive">
                                            <table id="example2" class="table table-striped table-bordered display nowrap" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Product Style</th>
                                                        <th># Of Pieces</th>
                                                        <th>Product Colors</th>
                                                        <th>Vendors</th>
                                                        <th>Orders</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($topStyles as $style)
                                                    <tr>
                                                        <td>{{ strtoupper($style['order_product_style']) }}</td>
                                                        <td>{{ $style['total_count'] }}</td>
                                                        <td>{{ strtoupper($style['colors']) }}</td>
                                                        <td>{{ strtoupper($style['vendors']) }}</td>
                                                        <td>{{ strtoupper($style['OrdersTables']) }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Customer Total History Tab -->
                                    <div class="tab-pane fade" id="report_3" role="tabpanel" aria-labelledby="home-report_3_tab">
                                        <h5 class="mb-0 text-uppercase">Customer Total History</h5>
                                        <hr />
                                        <div class="table-responsive">
                                            <table id="example3" class="table table-striped table-bordered display nowrap" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Customer Name</th>
                                                        <th>Product Style</th>
                                                        <th>Product Color</th>
                                                        <th>Product Size</th>
                                                        <th>Quantity</th>
                                                        <th>Purchase ID</th>
                                                        <th>Order Status</th>
                                                        <th>Date Placed</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($custHistory as $history)
                                                    <tr>
                                                        <td>{{ ucwords($history->order_customer_name) }}</td>
                                                        <td>{{ ucwords($history->order_product_style) }}</td>
                                                        <td>{{ strtoupper($history->order_product_color) }}</td>
                                                        <td>{{ $history->order_product_size }}</td>
                                                        <td>{{ $history->order_quantity }}</td>
                                                        <td>{{ $history->purchase_id }}</td>
                                                        <td>{{ isset($history->order_status) ? ucwords($history->order_status) : "NA" }}</td>
                                                        <td>{{ $history->created_at }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Top Customers Tab -->
                                    <div class="tab-pane fade" id="report_4" role="tabpanel" aria-labelledby="home-report_4_tab">
                                        <h5 class="mb-0 text-uppercase">Top Customers</h5>
                                        <hr />
                                        <div class="table-responsive">
                                            <table id="example4" class="table table-striped table-bordered display nowrap" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Customer Name</th>
                                                        <th>Pieces Ordered</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($topCust as $customer)
                                                    <tr>
                                                        <td>{{ ucwords($customer['order_customer_name']) }}</td>
                                                        <td>{{ $customer['total_count'] }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Late Vendors Tab -->
                                    <div class="tab-pane fade" id="report_5" role="tabpanel" aria-labelledby="home-report_5_tab">
                                        <h5 class="mb-0 text-uppercase">Late Vendors</h5>
                                        <hr />
                                        <div class="table-responsive">
                                            <table id="example5" class="table table-striped table-bordered display nowrap" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Vendor Name</th>
                                                        <th>Customer Name</th>
                                                        <th>Customer PO</th>
                                                        <th>Product Style</th>
                                                        <th>Product Color</th>
                                                        <th>Product Size</th>
                                                        <th>Order Quantity</th>
                                                        <th>Purchase ID</th>
                                                        <th>ETA</th>
                                                        <th>Delayed Days</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($lateVendors as $vendor)
                                                    @php
                                                        $etaExplode = explode(' ', $vendor->ETA);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ strtoupper($vendor->order_vendor_name) }}</td>
                                                        <td>{{ strtoupper($vendor->order_customer_name) }}</td>
                                                        <td>{{ strtoupper($vendor->purchase_id) }}</td>
                                                        <td>{{ strtoupper($vendor->order_product_style) }}</td>
                                                        <td>{{ strtoupper($vendor->order_product_color) }}</td>
                                                        <td>{{ $vendor->order_product_size }}</td>
                                                        <td>{{ $vendor->order_quantity }}</td>
                                                        <td>{{ $vendor->vendor_purchase_ID }}</td>
                                                        <td>{{ $etaExplode[0] ?? $vendor->ETA }}</td>
                                                        <td>{{ $vendor->delay_days }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
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
</div>
@endsection

@section('page-js')


    {{-- <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
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
    <!-- <script src="assets/js/pages/dashboard-custom.js"></script> -->
    <!-- <script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script> -->
    <!-- <script src="assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script> -->

    <!-- datatable date range links -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>

    <!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>



    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script> --}}
    
    <script>
        $(document).ready(function() {
            // Initialize all tables with common settings
            function initDataTable(tableId, title, orderColumn = null) {
                return $(tableId).DataTable({
                    aLengthMenu: [
                        [25, 50, 100, 200, -1],
                        [25, 50, 100, 200, "All"]
                    ],
                    dom: 'lBfrtip',
                    buttons: [
                        'copyHtml5',
                        {
                            extend: 'print',
                            title: `Report | ${title}`,
                            customize: function(win) {
                                $(win.document.body).find('tr:nth-child(odd) td').each(function(index) {
                                    $(this).css('background-color', '#f4f4f4');
                                });
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            title: `Report | ${title}`
                        },
                        {
                            extend: 'csvHtml5',
                            title: `Report | ${title}`
                        },
                        {
                            extend: 'pdfHtml5',
                            title: `Report | ${title}`
                        }
                    ],
                    order: orderColumn ? [[orderColumn, 'desc']] : []
                });
            }

            // Initialize each table
            initDataTable('#example', 'Total Orders');
            initDataTable('#example2', 'Top Sold Styles', 1);
            initDataTable('#example3', 'Customer Total History');
            initDataTable('#example4', 'Top Customers', 1);
            initDataTable('#example5', 'Late Vendors');
        });
    </script>
@endsection