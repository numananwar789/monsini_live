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
                                    <h5 class="mb-0 text-uppercase">History</h5>

                                    @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    @if (session('error'))
                                        <div class="alert alert-danger">
                                            {{ session('error') }}
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

                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
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
                                                    <input type="checkbox" id="check-all" class="ml-2">
                                                </div>

                                                <table cellspacing="5" cellpadding="5">
                                                    <tbody>
                                                        <tr>
                                                            <td>Minimum date:</td>
                                                            <td><input type="text" id="min" name="min"
                                                                    class="form-control"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Maximum date:</td>
                                                            <td><input type="text" id="max" name="max"
                                                                    class="form-control"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <table id="example"
                                                    class="table table-striped table-bordered display nowrap"
                                                    style="width:100%">
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
                                                            <th>Sub Product</th>
                                                            <th>Quantity</th>
                                                            <th>Purchase ID</th>
                                                            <th>Place Date</th>
                                                            <th>User</th>
                                                            <th>Allocation Date</th>
                                                            <th>Wear Date</th>
                                                            <th>Delete</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($historyList as $value)
                                                            @php
                                                                $row_clr = '';
                                                                if (
                                                                    str_contains(
                                                                        strtoupper($value->order_customer_name),
                                                                        strtoupper($ownerComp),
                                                                    )
                                                                ) {
                                                                    $row_clr =
                                                                        'background-color: #3f4d67; color:white;';
                                                                }
                                                            @endphp
                                                            <tr style="{{ $row_clr }}">
                                                                <td style="text-align: center;vertical-align: middle;">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        value="{{ $value->history_ID }}"
                                                                        id="{{ $value->history_ID }}" name="history[]">
                                                                    <label class="form-check-label"
                                                                        for="{{ $value->history_ID }}"></label>
                                                                </td>
                                                                <td>{{ $value->order_ID }}</td>
                                                                <td>{{ $value->order_GUID }}</td>
                                                                <td>{{ $value->vendor_purchase_ID }}</td>
                                                                <td>{{ $value->order_customer_name }}</td>
                                                                <td>{{ $value->order_product_style }}</td>
                                                                <td>{{ $value->order_product_color }}</td>
                                                                <td>{{ $value->order_product_size }}</td>
                                                                <td>{{ implode(', ', $value->sub_products ?? []) }}
                                                                </td>
                                                                <td>{{ $value->order_quantity }}</td>
                                                                <td>{{ $value->purchase_id }}</td>
                                                               <td>
    {{ \Carbon\Carbon::parse($value->created_at_final ?? $value->created_at)->format('Y-m-d') }}
</td>

                                                                <td>{{ $value->user_flag }}</td>
                                                                <td>20{{ date('y-m-d', strtotime($value->history_date)) }}
                                                                </td>
                                                                <td>{{ $value->order_wear_date }}</td>
                                                                <td>
                                                                    <a href="{{ route('order-histories.edit', $value->order_ID) }}"
                                                                        class="btn btn-success mb-0 mr-0 btn-sm">Edit</a>
                                                                    <span class="btn btn-danger mb-0 mr-0 btn-sm delete"
                                                                        data-id='{{ $value->order_ID }}'>Delete</span>
                                                                    <input type="hidden" name="orderID"
                                                                        value="{{ $value->order_ID }}">
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>

                                                <!-- Archive Modal -->
                                                <div class="modal fade" id="archiveModal" tabindex="-1"
                                                    aria-labelledby="archiveModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="archiveModalLabel">Archive
                                                                    Orders</h5>
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <form id="archiveForm"
                                                                action="{{ route('order-histories.archive') }}"
                                                                method="POST">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label for="archive-name"
                                                                            class="col-form-label">Archive Name:</label>
                                                                        <input type="text" class="form-control"
                                                                            id="archive-name" name="archiveName" required>
                                                                        <input type="hidden" name="selectedItems"
                                                                            id="selectedItemsInput">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="submit"
                                                                        class="btn btn-primary">Save</button>
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-dismiss="modal">Close</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <a class="btn btn-primary text-white" data-toggle="modal"
                                            data-target="#importModal">Import Orders</a>
                                        <button type="button" class="btn btn-warning float-right" data-toggle="modal"
                                            data-target="#archiveModal">Archive Data</button>
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
                                                <form method="POST" action="{{ route('order-histories.import') }}"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="historyFile">Upload File</label>
                                                            <input name="file" type="file"
                                                                class="form-control-file" id="historyFile"
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
    <!-- [ Main Content ] end -->
@endsection

@section('page-js')
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script> --}}
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>

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
                        columns: function(idx, data, node) {
                            if (node.innerHTML == "Delete")
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

            // Set up date filtering
            var minDate = new DateTime($('#min'), {
                format: 'MMMM Do YYYY'
            });
            var maxDate = new DateTime($('#max'), {
                format: 'MMMM Do YYYY'
            });

            $('#min, #max').on('change', function() {
                table.draw();
            });

            // Custom filtering function
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var min = minDate.val();
                    var max = maxDate.val();
                    var date = new Date(data[12]); // Allocation date column

                    if (
                        (min === null && max === null) ||
                        (min === null && date <= max) ||
                        (min <= date && max === null) ||
                        (min <= date && date <= max)
                    ) {
                        return true;
                    }
                    return false;
                }
            );
        });

        // Check all functionality
        $('#check-all').click(function(event) {
            $(':checkbox').prop('checked', this.checked);
        });

        // Set selected items before archive form submission
        $('#archiveModal').on('show.bs.modal', function() {
            var selectedItems = [];
            $('input[name="history[]"]:checked').each(function() {
                selectedItems.push($(this).val());
            });

            console.log(selectedItems);
            $('#selectedItemsInput').val(JSON.stringify(selectedItems));
        });

        // Delete functionality
        $('.delete').click(function() {
            if (!confirm('Are you sure you want to delete this item?')) return;

            var deleteid = $(this).data('id');
            var row = $(this).closest('tr');

            $.ajax({
                url: "order-histories/" + deleteid,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        row.css('background', 'tomato');
                        row.fadeOut(800, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Error: ' + response.error);
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseJSON.error);
                }
            });
        });
    </script>
@endsection
